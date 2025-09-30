<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreateRoleController extends Controller
{
    /**
     * Show the form for creating a new role.
     *
     * Route: GET /roles/create
     * Name: roles.create
     */
    public function __invoke(Request $request)
    {
        return view('roles.create');
    }
}