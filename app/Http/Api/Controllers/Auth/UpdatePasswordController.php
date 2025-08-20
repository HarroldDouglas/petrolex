<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Auth;

use App\DTOs\User\UpdatePasswordDTO;
use App\Http\Api\Responses\Auth\UpdatePasswordResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Models\User;
use App\Services\User\UserService;

final class UpdatePasswordController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Update authenticated user's password.
     *
     * Route: PATCH /password
     * Name: api.password.update
     */
    public function __invoke(UpdatePasswordRequest $request): UpdatePasswordResponse
    {
        /** @var User $user */
        $user = $request->user();

        $dto = new UpdatePasswordDTO(
            old_password: $request->input('old_password'),
            new_password: $request->input('new_password'),
        );

        try {
            $this->userService->updatePassword(
                $user,
                $dto
            );

            return UpdatePasswordResponse::success();
        } catch (\Exception $e) {
            return UpdatePasswordResponse::error($e->getMessage());
        }
    }
}
