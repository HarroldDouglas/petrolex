<?php

namespace App\Http\Api\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CheckAuthController extends Controller
{
    /**
     * Check if the user is authenticated.
     *
     * Route: GET /api/auth/check
     * Name: api.auth.check
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            '_metadata' => [
                'success' => true,
                'message' => 'User is authenticated.',
            ],
            'data' => [
                'authenticated' => true,
                'user_id' => auth()->id(),
                'roles' => auth()->user()?->roles ?? [],
            ],
        ]);
    }
}
