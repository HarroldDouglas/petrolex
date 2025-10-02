<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Services\Role\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class DeleteRoleController extends Controller
{
    public function __construct(
        private readonly RoleService $roleService
    ) {}

    /**
     * Remove the specified role from storage.
     *
     * Route: DELETE /roles/{role}/delete
     * Name: roles.delete
     */
    public function __invoke(Request $request, Role $role): JsonResponse
    {
        try {
            // Check if user has permission to delete roles
            if (!$request->user()->can('roles.delete')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas l\'autorisation de supprimer des rôles.'
                ], 403);
            }

            // Prevent deletion of super admin role
            if ($role->name === 'Super Admin' || $role->name === 'super-admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Le rôle Super Admin ne peut pas être supprimé.'
                ], 422);
            }

            // Check if role has users assigned
            if ($role->users()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce rôle ne peut pas être supprimé car il est assigné à des utilisateurs.'
                ], 422);
            }

            $deleted = $this->roleService->delete($role);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Rôle supprimé avec succès.'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du rôle.'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur inattendue s\'est produite.'
            ], 500);
        }
    }
}