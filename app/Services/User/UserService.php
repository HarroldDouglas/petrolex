<?php

namespace App\Services\User;

use App\Events\UserCreatedEvent;
use App\Events\UserDeletedEvent;
use App\Events\UserUpdatedEvent;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\BaseServiceWithMedia;
use App\Services\Shared\Media\MediaServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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
        DB::beginTransaction();
        try {
            if ($attributes['image'] instanceof \Illuminate\Http\UploadedFile) {
                ('Creating user with media', [
                    'attributes' => $attributes,
                ]);
                /** @var User $user */
                $user = parent::createWithMedia($attributes);
            } else {
                /** @var User $user */
                $user = parent::create($attributes);
            }

            UserCreatedEvent::dispatch(
                $user,
                $attributes['role'],
                $attributes['distribution_center_ids']
            );

            DB::commit();

            return $user;
        } catch (\Exception $e) {
            Log::error('User creation failed', [
                'message' => $e->getMessage(),
                'attributes' => $attributes,
                'trace' => $e->getTraceAsString(),
            ]);
            DB::rollBack();
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
        if (! $user instanceof User) {
            throw new \InvalidArgumentException('Expected User model');
        }
        if (empty($attributes)) {
            return $user;
        }

        $originalValues = $user->only(array_keys($attributes));

        DB::beginTransaction();

        try {
            /** @var User */
            $result = $this->userRepository->update($user, $attributes);

            if ($result) {
                $user->refresh();
                $currentValues = $user->only(array_keys($attributes));
                $changes = array_diff_assoc($currentValues, $originalValues);

                if (! empty($changes)) {
                    UserUpdatedEvent::dispatch($user, $changes);
                }
            }

            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a user from the system
     *
     * @param  User  $user  The user to delete
     * @return bool Whether the deletion was successful
     */
    public function delete(Model $user): bool
    {
        DB::beginTransaction();

        try {
            $result = $this->userRepository->delete($user);

            if ($result) {
                UserDeletedEvent::dispatch($user);
            }

            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function getMediaFields(): array
    {
        return ['image'];
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

    protected function getModel(): string
    {
        return User::class;
    }
}
