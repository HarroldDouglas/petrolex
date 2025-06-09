<?php

namespace App\Services\User;

use App\DTOs\User\UpdateUserDTO;
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

    /**
     * Update a user's information
     *
     * @param  User  $user  The user to update
     * @param  UpdateUserDTO  $dto  Data Transfer Object containing the updated user data
     * @return bool Whether the update was successful
     */
    public function update(User $user, UpdateUserDTO $dto): bool
    {
        $attributes = $dto->toArrayFiltered();

        if (empty($attributes)) {
            return true;
        }

        $originalValues = $user->only(array_keys($attributes));

        DB::beginTransaction();

        try {
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
