<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeleteUserController extends Controller
{
    /**
     * Remove the specified user from storage.
     *
     * Route: DELETE /users/{user_id}/delete
     * Name: users.delete
     */
    public function __invoke(Request $request)
    {
        //
    }
}
