<form wire:submit.prevent="login">
    @if($error)
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="errorAlert">
            {{ $error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="form-group mb-3">
        <label class="form-label">{{ __('auth.email_or_phone') }}</label>
        <input
                class="form-control @error('identifier') is-invalid @enderror"
                type="text"
                wire:model="identifier"
                required
            >
        @error('identifier')
            <div class="invalid-feedback">{{ __($message) }}</div>
        @enderror
    </div>

    <div class="form-group mb-3 password-field">
        <label for="password" class="form-label">Mot de passe</label>
        <div class="position-relative">
            <input
                class="form-control @error('password') is-invalid @enderror"
                id="password"
                type="password"
                wire:model="password"
                required
            >
            <span class="password-toggle">
                <i class="iconoir-eye" id="toggleIcon"></i>
            </span>
        </div>
        @error('password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="remember-forgot-row">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="remember" wire:model="remember">
            <label class="form-check-label" for="remember">{{ __('auth.remember_me') }}</label>
        </div>
        <a href="#" class="epena-red text-decoration-none" wire:click.prevent="forgotPassword">
            {{ __('auth.forgot_password') }}
        </a>
    </div>

    <button
        type="submit"
        class="btn btn-epena btn-block w-100"
        wire:loading.attr="disabled"
        wire:target="login"
    >
        <span wire:loading.class="d-none" wire:target="login">Se connecter</span>
        <span wire:loading wire:target="login">
            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
            Connexion...
        </span>

    </button>
</form>
