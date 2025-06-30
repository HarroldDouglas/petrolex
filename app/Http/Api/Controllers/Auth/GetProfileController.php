<?php

namespace App\Http\Api\Controllers\Auth;

use App\Http\Api\Responses\Auth\ProfileResponse;
use App\Http\Controllers\Controller;
use App\Services\Auth\Contracts\AuthenticationServiceInterface;

class GetProfileController extends Controller
{
    public function __construct(protected AuthenticationServiceInterface $authService) {}

    /**
     * Get current user profile.
     *
     * Route: GET /user
     * Name: api.user
     */
    public function __invoke(): ProfileResponse
    {
        $user = $this->authService->getAuthenticatedUser();

        return ProfileResponse::withUser($user);
    }
}
