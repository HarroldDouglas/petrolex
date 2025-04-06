<?php

namespace App\Http\Api\Controllers\Auth;

use App\Contracts\Services\AuthenticationServiceInterface;
use App\Http\Api\Responses\Auth\ProfileResponse;
use App\Http\Controllers\Controller;

class GetProfileController extends Controller
{
    public function __construct(protected AuthenticationServiceInterface $authService) {}

    /**
     * Get current user profile
     */
    public function __invoke(): ProfileResponse
    {
        $user = $this->authService->getAuthenticatedUser();

        return ProfileResponse::withUser($user);
    }
}
