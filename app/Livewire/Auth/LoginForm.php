<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoginForm extends Component
{
    /**
     * Component properties
     */
    public $identifier = '';
    public $password = '';
    public $remember = false;
    public $error = null;
    public $loading = false;

    /**
     * Validation rules
     */
    protected $rules = [
        'identifier' => 'required',
        'password' => 'required',
    ];

    /**
     * Custom validation messages
     */
    protected $messages = [
        'identifier.required' => 'auth.identifier_required',
        'password.required' => 'auth.password_required',
    ];

    protected $listeners = ['showLoginForm' => 'resetForm'];

    /**
     * Attempt user authentication
     *
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function login()
    {
        $this->validate();
        $this->startLoading();

        try {
            $credentials = $this->getCredentials();

            if (Auth::attempt($credentials, $this->remember)) {
                request()->session()->regenerate();

                return $this->handleSuccessfulLogin();
            }

            $this->handleFailedLogin();
        } catch (\Exception $e) {
            $this->handleAuthenticationException($e);
        } finally {
            $this->stopLoading();
        }
    }

    /**
     * Render the component
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.auth.login-form');
    }

    /**
     * Start loading state
     */
    private function startLoading(): void
    {
        $this->loading = true;
        $this->error = null;
    }

    /**
     * Stop loading state
     */
    private function stopLoading(): void
    {
        $this->loading = false;
    }

    /**
     * Get the credentials for authentication
     */
    private function getCredentials(): array
    {
        $loginType = $this->determineLoginType();

        return [
            $loginType => $this->identifier,
            'password' => $this->password,
        ];
    }

    /**
     * Determine whether input is email or phone number
     */
    private function determineLoginType(): string
    {
        if (preg_match('/^[\w.%+-]+@[\w.-]+\.[a-zA-Z]{2,}$/', $this->identifier)) {
            return 'email';
        }

        $cleanPhone = preg_replace('/[^0-9+]/', '', $this->identifier);

        return 'phone_number';
    }

    /**
     * Handle successful login
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function handleSuccessfulLogin()
    {
        session()->flash('success', __('auth.login_success'));

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Handle failed login attempt
     */
    private function handleFailedLogin(): void
    {
        $this->error = __('auth.failed');
        $this->password = '';
    }

    /**
     * Handle authentication exception
     */
    private function handleAuthenticationException(\Exception $e): void
    {
        $this->error = __('auth.error_occurred');
        $this->password = '';
    }

    /**
     * Reset form state
     */
    public function resetForm()
    {
        $this->reset(['identifier', 'password', 'error']);
    }

    /**
     * Show forgot password form
     */
    public function forgotPassword()
    {
        $this->dispatch('showForgotPasswordForm');
    }
}
