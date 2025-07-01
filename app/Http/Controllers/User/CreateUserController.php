<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreateUserController extends Controller
{
    /**
     * Show the form for creating a new user.
     *
     * Route: GET /users/create
     * Name: users.create
     */
    public function __invoke(Request $request)
    {
        $this->authorize('users.create');

        return view('users.create');
    }
}
