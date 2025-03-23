<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            $sweetAlert = [
                'title' => 'Connexion réussie !',
                'text' => 'Bienvenue sur votre tableau de bord.',
                'icon' => 'success',
                'timer' => 3000,
                'showConfirmButton' => false,
                'customClass' => [
                    'popup' => 'card',
                    'title' => 'card-header h5',
                ],
            ];

            return redirect()->intended(route('dashboard'))->with('sweet_alert', $sweetAlert);
        }

        throw ValidationException::withMessages([
            'email' => [__('Les informations d\'identification fournies ne correspondent pas à nos enregistrements.')],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('sweet_alert', [
            'title' => 'Déconnexion réussie !',
            'text' => 'À bientôt !',
            'icon' => 'success',
            'timer' => 2000,
            'showConfirmButton' => false,
        ]);
    }
}
