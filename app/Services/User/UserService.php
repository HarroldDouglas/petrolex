<?php

namespace App\Services\User;

use App\DTOs\User\UpdatePasswordDTO;
use App\Enums\UserRole;
use App\Events\UserCreatedEvent;
use App\Events\UserDeletedEvent;
use App\Events\UserUpdatedEvent;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\BaseServiceWithMedia;
use App\Services\Shared\Media\MediaServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService extends BaseServiceWithMedia
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        protected MediaServiceInterface $mediaService,
    ) {
        parent::__construct($userRepository, $mediaService);
    }

    /**
     * Create a new user with associated media
     *
     * @param  array  $attributes  Data Transfer Object containing user data
     * @return User The created user model
     *
     * @throws \Exception If the creation fails
     */
    public function createWithMedia(array $attributes): User
    {
        try {
            /** @var User $user */
            $user = parent::createWithMedia($attributes);

            UserCreatedEvent::dispatch(
                $user,
                $attributes['role'],
                $attributes['distribution_center_ids'] ?? []
            );

            return $user;
        } catch (\Exception $e) {
            Log::error('User creation failed', [
                'message' => $e->getMessage(),
                'attributes' => $attributes,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Update a user's information
     *
     * @param  User  $user  The user to update
     * @param  array  $attributes  The attributes to update
     * @return User The updated user model
     */
    public function update(Model $user, array $attributes): Model
    {
        /** @var User $user */
        if (! $user instanceof User) {
            throw new \InvalidArgumentException('Expected User model');
        }
        if (empty($attributes)) {
            return $user;
        }

        $originalValues = $user->only(array_keys($attributes));

        if (empty($attributes['password'])) {
            unset($attributes['password']);
        }

        if (! isset($attributes['is_active'])) {
            unset($attributes['is_active']);
        }

        try {
            /** @var User $user */
            $user = parent::updateWithMedia($user, $attributes);

            if ($user) {
                $user->refresh();
                $currentValues = $user->only(array_keys($attributes));
                $changes = array_diff_assoc($currentValues, $originalValues);

                UserUpdatedEvent::dispatch(
                    $user,
                    $attributes['role'] ?? null,
                    $attributes['distribution_center_ids'] ?? [],
                    $changes
                );
            }

            return $user;
        } catch (\Exception $e) {
            Log::error('User update failed', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
                'attributes' => $attributes,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Update a user's password.
     *
     * @param  User  $user  The user to update.
     * @param  UpdatePasswordDTO  $dto  The DTO containing old and new passwords.
     * @return bool True if the password was updated, false otherwise.
     */
    public function updatePassword(User $user, UpdatePasswordDTO $dto): bool
    {
        if (! Hash::check($dto->old_password, $user->password)) {
            throw new \Exception('L\'ancien mot de passe est incorrect.');
        }

        return (bool) $this->userRepository->update($user, [
            'password' => Hash::make($dto->new_password),
        ]);
    }

    /**
     * Delete a user from the system
     *
     * @param  User  $user  The user to delete
     * @return bool Whether the deletion was successful
     */
    public function delete(Model $user): bool
    {
        try {
            $result = $this->userRepository->delete($user);

            if ($result) {
                UserDeletedEvent::dispatch($user);
            }

            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function getMediaFields(): array
    {
        return ['image'];
    }

    protected function getMediaStrategy(): string
    {
        return 'conditional_single';
    }

    protected function processMediaWithStrategy($model, array $data): void
    {
        // Only process media if there's actually an image in the data
        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            $this->mediaService->handleSingleImageStrategy($model, $data['image']);
        }
    }

    /**
     * Find a user by their ID
     *
     * @param  int  $id  The user ID to find
     * @return User|null The user if found, null otherwise
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the user is not found
     */
    public function find(int $id): ?User
    {
        /** @var User|null */
        return $this->userRepository->find($id);
    }

    /**
     * Get all users with the "customer" role.
     *
     * @return \Illuminate\Database\Eloquent\Collection|User[]
     */
    public function getAllCustomers()
    {
        return $this->userRepository->findByRole(UserRole::CUSTOMER());
    }

    public function findUserByIdentifier(string $identifier): ?User
    {
        return $this->userRepository->findByEmailOrPhone($identifier);
    }

    public function markEmailAsVerified(User $user): Model
    {
        return $this->userRepository->update($user, ['email_verified_at' => now()]);
    }

    public function markPhoneAsVerified(User $user): Model
    {
        return $this->userRepository->update($user, ['phone_verified_at' => now()]);
    }

    protected function getModel(): string
    {
        return User::class;
    }
}
