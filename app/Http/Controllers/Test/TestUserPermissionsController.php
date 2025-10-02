<?php

namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\User\UserPermissionService;
use Illuminate\Http\Request;

class TestUserPermissionsController extends Controller
{
    public function __construct(
        protected UserPermissionService $userPermissionService
    ) {}

    public function testUserPermissions(Request $request, User $user)
    {
        if (! $user->canHavePermissionsManaged()) {
            return response()->json([
                'error' => 'User cannot have permissions managed',
                'user_type' => [
                    'is_customer' => $user->isCustomer(),
                    'is_delivery_person' => $user->isDeliveryPerson(),
                ],
            ], 403);
        }

        $permissionStates = $this->userPermissionService->getUserPermissionStates($user);
        $effectivePermissions = $this->userPermissionService->getEffectiveUserPermissions($user);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->full_name,
                'role' => $user->roles->first()?->name ?? 'No role',
            ],
            'permission_states' => $permissionStates,
            'effective_permissions' => $effectivePermissions->toArray(),
            'permission_sources' => $user->getPermissionsBySource(),
        ]);
    }

    public function togglePermission(Request $request, User $user, string $permission)
    {
        try {
            $this->userPermissionService->toggleUserPermission($user, $permission);

            return response()->json([
                'success' => true,
                'message' => "Permission '{$permission}' toggled successfully",
                'new_state' => $user->fresh()->getPermissionState($permission),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
