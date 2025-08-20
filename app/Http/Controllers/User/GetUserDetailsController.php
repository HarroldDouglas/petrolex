<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetUserDetailsController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Display the specified user details.
     *
     * Route: GET /users/{user_id}/details
     * Name: users.details
     */
    public function __invoke(Request $request, int $userId)
    {
        try {
            $user = $this->userService->find($userId);

            if (! $user) {
                return redirect()->route('users.list')->with('error', 'Utilisateur non trouvé.');
            }

            $user->loadMissing(['distributionCenters', 'roles', 'media']);

            return view('users.user-details', [
                'user' => $user,
            ]);

        } catch (\Exception $e) {
            Log::error('Error in GetUserDetailsController for user ID '.$userId.': '.$e->getMessage(), ['exception' => $e]);

            return redirect()->route('users.list')->with('error', 'Une erreur inattendue s\'est produite lors de la récupération des détails de l\'utilisateur.');
        }
    }
}
