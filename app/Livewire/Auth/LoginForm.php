<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class LoginForm extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;
    public $error = null;
    public $loading = false;

    protected $rules = [
        'email' => ['required', 'email'],
        'password' => ['required'],
    ];

    protected $messages = [
        'email.required' => 'L\'adresse e-mail est obligatoire.',
        'email.email' => 'Veuillez fournir une adresse e-mail valide.',
        'password.required' => 'Le mot de passe est obligatoire.',
    ];

    public function login()
    {
        $this->validate();
        $this->loading = true;
        $this->error = null;

        try {
            Log::info('Tentative de connexion', ['email' => $this->email]);

            if (Auth::attempt([
                'email' => $this->email,
                'password' => $this->password,
            ], $this->remember)) {
                session()->regenerate();

                Log::info('Connexion réussie', ['user_id' => Auth::id()]);

                $this->dispatch('sweet-alert', [
                    'title' => 'Connexion réussie !',
                    'text' => 'Bienvenue sur votre tableau de bord.',
                    'icon' => 'success',
                    'timer' => 3000,
                    'showConfirmButton' => false,
                    'customClass' => [
                        'popup' => 'card',
                        'title' => 'card-header h5',
                    ],
                ]);

                return $this->redirect(route('dashboard'));
            }

            Log::warning('Échec de connexion', ['email' => $this->email]);
            $this->error = 'Les informations d\'identification fournies ne correspondent pas à nos enregistrements.';
            $this->reset('password');

        } catch (\Exception $e) {
            Log::error('Exception lors de la connexion', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error = 'Une erreur est survenue lors de la connexion. Veuillez réessayer.';
        }

        $this->loading = false;
    }

    public function render()
    {
        return view('livewire.auth.login-form');
    }

    public function updated($propertyName)
    {
        $this->error = null;
    }
}
