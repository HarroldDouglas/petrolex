<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\User\UserService;
use Illuminate\Http\Request;

class EditUserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Show the form for editing the specified user.
     *
     * Route: GET /users/{user_id}/edit
     * Name: users.edit
     */
    public function __invoke(Request $request, int $userId)
    {
        $user = $this->userService->find($userId);

        return view('users.edit', [
            'user' => $user,
        ]);
    }
}
