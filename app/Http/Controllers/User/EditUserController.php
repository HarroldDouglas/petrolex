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
     * Handle the incoming request.
     */
    public function __invoke(Request $request, int $userId)
    {
        $this->authorize('users.edit');
        $user = $this->userService->find($userId);
        if(!$user) {
            abort(404, "Cet utilisateur n'existe pas.");
        }

        return view('users.edit', [
            'user' => $user,
        ]   );
    }
}
