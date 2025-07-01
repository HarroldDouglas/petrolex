<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetUserDetailsController extends Controller
{
    /**
     * Display the specified user details.
     *
     * Route: GET /users/{user_id}/details
     * Name: users.details
     */
    public function __invoke(Request $request)
    {
        return view('users.user-details');
    }
}
