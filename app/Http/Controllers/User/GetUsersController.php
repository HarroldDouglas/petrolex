<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetUsersController extends Controller
{
    /**
     * Display a listing of the users.
     *
     * Route: GET /users
     * Name: users.list
     */
    public function __invoke(Request $request)
    {
        return view('users.index');
    }
}
