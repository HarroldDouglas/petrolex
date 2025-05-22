<div>
    <div class="mb-4">
        <h5 class="text-center">{{ __('auth.reset_password') }}</h5>
    </div>

    @if($error)
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="errorAlert">
            {{ $error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($success)
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="successAlert">
            {{ $success }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Step 1: Identifier Input -->
    @if($this->isIdentifierStep())
        <form wire:submit.prevent="submitIdentifier">
            <div class="form-group mb-3">
                <label class="form-label">{{ __('auth.email_or_phone') }}</label>
                <input
                    type="text"
                    class="form-control @error('identifier') is-invalid @enderror"
                    wire:model="identifier"
                    placeholder="{{ __('auth.enter_email_or_phone') }}"
                    required
                >
                @error('identifier')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <button
                    type="button"
                    class="btn btn-link text-muted ps-0"
                    wire:click="backToLogin"
                >
                    <i class="iconoir-arrow-left me-1"></i> {{ __('auth.back_to_login') }}
                </button>

                <button
                    type="submit"
                    class="btn btn-epena"
                    wire:loading.attr="disabled"
                    wire:target="submitIdentifier"
                >
                    <span wire:loading.class="d-none" wire:target="submitIdentifier">
                        {{ __('auth.send_reset_code') }}
                    </span>
                    <span wire:loading wire:target="submitIdentifier">
                        <i class="fas fa-spinner fa-spin"></i> {{ __('auth.sending') }}...
                    </span>
                </button>
            </div>
        </form>
    @endif

    <!-- Step 2: OTP Verification -->
    @if($this->isOtpStep())
        <form wire:submit.prevent="verifyOtp">
            <div class="text-center mb-4">
                <p>{{ __('auth.otp_instructions') }}</p>
                @if($maskedIdentifier)
                    <p class="text-muted">{{ __('auth.sent_to') }}: {{ $maskedIdentifier }}</p>
                @endif
            </div>

            <div class="form-group mb-3">
                <label class="form-label">{{ __('auth.verification_code') }}</label>
                <input
                    type="text"
                    class="form-control @error('otp') is-invalid @enderror"
                    wire:model="otp"
                    placeholder="Ex: 123456"
                    maxlength="6"
                    required
                >
                @error('otp')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="text-center mb-3">
                <button type="button" class="btn btn-link" wire:click="resendOtp">
                    {{ __('auth.resend_code') }}
                </button>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <button
                    type="button"
                    class="btn btn-link text-muted ps-0"
                    wire:click="backToLogin"
                >
                    <i class="iconoir-arrow-left me-1"></i> {{ __('auth.back_to_login') }}
                </button>

                <button
                    type="submit"
                    class="btn btn-epena"
                    wire:loading.attr="disabled"
                    wire:target="verifyOtp"
                >
                    <span wire:loading.class="d-none" wire:target="verifyOtp">
                        {{ __('auth.verify_code') }}
                    </span>
                    <span wire:loading wire:target="verifyOtp">
                        <i class="fas fa-spinner fa-spin"></i> {{ __('auth.verifying') }}...
                    </span>
                </button>
            </div>
        </form>
    @endif

    <!-- Step 3: New Password -->
    @if($this->isResetStep())
        <form wire:submit.prevent="resetPassword">
            <div class="form-group mb-3">
                <label for="password" class="form-label">{{ __('auth.new_password') }}</label>
                <div class="position-relative">
                    <input
                        type="password"
                        class="form-control @error('password') is-invalid @enderror"
                        id="password_original"
                        wire:model.live="password"
                        required
                    >
                    <span class="password-toggle" onclick="togglePasswordVisibility('password_original', this)">
                        <i class="iconoir-eye"></i>
                    </span>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="password_confirmation" class="form-label">{{ __('auth.confirm_password') }}</label>
                <div class="position-relative">
                    <input
                        type="password"
                        class="form-control @error('password_confirmation') is-invalid @enderror"
                        id="password_confirmation"
                        wire:model.live="password_confirmation"
                        required
                    >
                    <span class="password-toggle" onclick="togglePasswordVisibility('password_confirmation', this)">
                        <i class="iconoir-eye"></i>
                    </span>
                    @error('password_confirmation')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button
                    type="submit"
                    class="btn btn-epena"
                    wire:loading.attr="disabled"
                    wire:target="resetPassword"
                >
                    <span wire:loading.class="d-none" wire:target="resetPassword">
                        {{ __('auth.update_password') }}
                    </span>
                    <span wire:loading wire:target="resetPassword">
                        <i class="fas fa-spinner fa-spin"></i> {{ __('auth.updating') }}...
                    </span>
                </button>
            </div>
        </form>
    @endif
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        // Redirect after password reset
        Livewire.on('passwordResetCompleted', () => {
            setTimeout(() => {
                Livewire.dispatch('showLoginForm');
            }, 2000);
        });
    });

    // Function to toggle password visibility
    function togglePasswordVisibility(inputId, element) {
        const input = document.getElementById(inputId);
        const icon = element.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('iconoir-eye', 'iconoir-eye-closed');
        } else {
            input.type = 'password';
            icon.classList.replace('iconoir-eye-closed', 'iconoir-eye');
        }
    }
</script>
