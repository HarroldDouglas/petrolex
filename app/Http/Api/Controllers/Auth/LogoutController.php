<?php

namespace App\Http\Api\Controllers\Auth;

use App\Contracts\Services\AuthenticationServiceInterface;
use App\Http\Api\Responses\Auth\LogoutResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __construct(protected AuthenticationServiceInterface $authService) {}

    /**
     * Logout user and revoke API token
     */
    public function __invoke(Request $request): LogoutResponse
    {
        $user = $this->authService->getAuthenticatedUser();

        $this->authService->revokeAllTokens($user);

        return LogoutResponse::make();
    }
}
