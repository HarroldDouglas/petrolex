<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class DeleteRoleController extends Controller
{
    /**
     * Remove the specified role from storage.
     *
     * Route: DELETE /roles/{role}/delete
     * Name: roles.delete
     */
    public function __invoke(Request $request, Role $role)
    {
        //
    }
}
