<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetRoleListController extends Controller
{
    /**
     * Display a listing of the roles.
     *
     * Route: GET /roles
     * Name: roles.list
     */
    public function __invoke(Request $request)
    {
        return view('roles.index');
    }
}