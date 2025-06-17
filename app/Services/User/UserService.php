<?php

namespace App\Services\User;

use App\DTOs\User\CreateUserDTO;
use App\DTOs\User\UpdateUserDTO;
use App\Events\UserCreatedEvent;
use App\Events\UserDeletedEvent;
use App\Events\UserUpdatedEvent;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function create(CreateUserDTO $dto): User
    {
        DB::beginTransaction();

        try {
            /** @var User $user */
            $user = $this->userRepository->create($dto->toUserArray());

            UserCreatedEvent::dispatch($user, $dto->role->value, $dto->distribution_center_ids);

            DB::commit();

            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update a user's information
     *
     * @param  User  $user  The user to update
     * @param  UpdateUserDTO  $dto  Data Transfer Object containing the updated user data
     * @return User The updated user model
     */
    public function update(User $user, UpdateUserDTO $dto): User
    {
        $attributes = $dto->toArrayFiltered();

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
    public function delete(User $user): bool
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
}
