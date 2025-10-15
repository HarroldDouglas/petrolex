<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class EditRoleController extends Controller
{
    /**
     * Show the form for editing the specified role.
     *
     * Route: GET /roles/{role}/edit
     * Name: roles.edit
     */
    public function __invoke(Request $request, Role $role)
    {
        return view('roles.edit', compact('role'));
    }
}
