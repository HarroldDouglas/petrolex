<?php

namespace App\Http\Controllers\User;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreateUserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $this->authorize('users.create');

        // Get authenticated user role
        $authUser = Auth::user();
        $allowedRoles = $this->getAllowedRolesToCreate();

        return view('users.create-user', [
            'allowedRoles' => $allowedRoles,
        ]);
    }

    /**
     * Get the roles that the authenticated user is allowed to create
     *
     * @return array
     */
    private function getAllowedRolesToCreate()
    {
        $authUser = Auth::user();

        // If the authenticated user is a center manager, they can only create delivery persons
        if ($authUser->hasRole(UserRole::CENTER_MANAGER()->value)) {
            return [
                UserRole::DELIVERY_PERSON(),
            ];
        }

        // If the user is super admin or admin, they can create all roles except super admin and customer
        if ($authUser->hasRole(UserRole::SUPER_ADMIN()->value) ||
            $authUser->hasRole(UserRole::ADMIN()->value)) {
            return [
                UserRole::ADMIN(),
                UserRole::MANAGER(),
                UserRole::ACCOUNTANT(),
                UserRole::GAS_MANAGER(),
                UserRole::CENTER_MANAGER(),
                UserRole::DELIVERY_PERSON(),
            ];
        }

        // Default: empty array if no roles are allowed
        return [];
    }
}
