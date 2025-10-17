<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;

class ManageUserPermissionsController extends Controller
{
    public function __invoke(User $user)
    {
        return view('pages.users.manage-permissions', [
            'user' => $user,
        ]);
    }
}
