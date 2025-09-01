<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers;

use App\DTOs\User\UpdateUserDTO;
use App\Http\Api\Responses\Auth\ProfileResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Models\User;
use App\Services\User\UserService;

final class UpdateProfileController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Update user profile.
     *
     * Route: PATCH /api/profile
     * Name: api.profile.update
     */
    public function __invoke(UpdateProfileRequest $request): ProfileResponse
    {
        /** @var User $user */
        $user = $request->user();

        $dto = new UpdateUserDTO(
            first_name: $request->input('first_name'),
            last_name: $request->input('last_name'),
            email: $request->input('email'),
            phone_number: $request->input('phone_number'),
        );

        $updatedUser = $this->userService->update($user, $dto->toArray());

        return ProfileResponse::withUser($updatedUser);
    }
}
