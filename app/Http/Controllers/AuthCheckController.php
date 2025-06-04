<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthCheckController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return RedirectResponse
     */
    public function __invoke(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('login');
    }
}
