<?php

namespace App\Livewire\Auth;

use App\Exceptions\Auth\OtpDeliveryException;
use App\Exceptions\UserNotFoundException;
use App\Http\Requests\Auth\IdentifierFormRequest;
use App\Http\Requests\Auth\OtpVerificationRequest;
use App\Http\Requests\Auth\PasswordResetRequest;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Auth\Contracts\OtpServiceInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ForgotPasswordForm extends Component
{
    // Step tracking
    public const STEP_IDENTIFIER = 1;
    public const STEP_OTP = 2;
    public const STEP_RESET = 3;
    public $currentStep = self::STEP_IDENTIFIER;

    // Form fields
    public $identifier = '';
    public $otp = '';
    public $password = '';
    public $password_confirmation = '';

    // State management
    public $error = null;
    public $success = null;
    public $loading = false;
    public $maskedIdentifier = null;

    /**
     * Get validation rules per step
     *
     * @return array
     */
    protected function rules()
    {
        switch ($this->currentStep) {
            case self::STEP_IDENTIFIER:
                return (new IdentifierFormRequest)->rules();
            case self::STEP_OTP:
                return (new OtpVerificationRequest)->rules();
            case self::STEP_RESET:
                return (new PasswordResetRequest)->rules();
            default:
                return [];
        }
    }

    /**
     * Get validation messages per step
     *
     * @return array
     */
    protected function messages()
    {
        switch ($this->currentStep) {
            case self::STEP_IDENTIFIER:
                return (new IdentifierFormRequest)->messages();
            case self::STEP_OTP:
                return (new OtpVerificationRequest)->messages();
            case self::STEP_RESET:
                return (new PasswordResetRequest)->messages();
            default:
                return [];
        }
    }

    /**
     * Submit identifier (email or phone)
     */
    public function submitIdentifier()
    {
        $this->resetErrorSuccess();
        $this->validate($this->rules());
        $this->loading = true;

        try {
            /** @var OtpServiceInterface $otpService */
            $otpService = app(OtpServiceInterface::class);
            $otpService->sendOtp($this->identifier);
            $this->maskedIdentifier = $otpService->maskIdentifier($this->identifier);

            $this->success = __('auth.otp_sent');
            $this->currentStep = self::STEP_OTP;

        } catch (UserNotFoundException $e) {
            Log::warning('User not found during password reset attempt', [
                'identifier' => $this->identifier,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error = __('auth.user_not_found');
        } catch (OtpDeliveryException $e) {
            Log::error('OTP delivery failed', [
                'identifier' => $this->identifier,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error = __('auth.otp_delivery_failed');
        } catch (\Exception $e) {
            Log::error('Unexpected error during password reset (identifier step)', [
                'identifier' => $this->identifier,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error = __('auth.error_occurred');
        } finally {
            $this->loading = false;
        }
    }

    /**
     * Verify OTP code
     */
    public function verifyOtp()
    {
        $this->resetErrorSuccess();
        $this->validate($this->rules());
        $this->loading = true;

        try {
            /** @var OtpServiceInterface $otpService */
            $otpService = app(OtpServiceInterface::class);

            if (! $otpService->verifyOtp($this->identifier, $this->otp)) {
                Log::warning('Invalid OTP provided', [
                    'identifier' => $this->identifier,
                ]);
                $this->error = __('auth.invalid_otp');

                return;
            }

            $this->currentStep = self::STEP_RESET;
            $this->success = __('auth.otp_verified');

        } catch (\Exception $e) {
            Log::error('Error during OTP verification', [
                'identifier' => $this->identifier,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error = __('auth.error_occurred');
        } finally {
            $this->loading = false;
        }
    }

    /**
     * Resend OTP code
     */
    public function resendOtp()
    {
        $this->resetErrorSuccess();
        $this->loading = true;

        try {
            /** @var OtpServiceInterface $otpService */
            $otpService = app(OtpServiceInterface::class);

            $otpService->resendOtp($this->identifier);
            $this->success = __('auth.otp_resent');

        } catch (\Exception $e) {
            Log::error('Error resending OTP', [
                'identifier' => $this->identifier,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error = __('auth.error_occurred');
        } finally {
            $this->loading = false;
        }
    }

    /**
     * Reset password
     */
    public function resetPassword()
    {
        $this->resetErrorSuccess();
        $this->validate($this->rules());
        $this->loading = true;

        try {
            $userRepository = app(UserRepositoryInterface::class);
            $user = $userRepository->findByEmailOrPhone($this->identifier);

            if (! $user) {
                Log::warning('User not found during password reset', [
                    'identifier' => $this->identifier,
                ]);
                $this->error = __('auth.user_not_found');

                return;
            }

            $userRepository->update($user, [
                'password' => Hash::make($this->password),
            ]);

            /** @var OtpServiceInterface $otpService */
            $otpService = app(OtpServiceInterface::class);
            $otpService->invalidateOtp($this->identifier);

            $this->success = __('auth.password_reset_success');

            // Redirect to login after a short delay
            $this->dispatch('passwordResetCompleted');

        } catch (\Exception $e) {
            Log::error('Error during password reset', [
                'identifier' => $this->identifier,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error = __('auth.error_occurred');
        } finally {
            $this->loading = false;
        }
    }

    /**
     * Go back to login form
     */
    public function backToLogin()
    {
        $this->dispatch('showLoginForm');
    }

    /**
     * Reset error and success messages
     */
    private function resetErrorSuccess()
    {
        $this->error = null;
        $this->success = null;
    }

    /**
     * Check if current step is identifier input
     */
    public function isIdentifierStep(): bool
    {
        return $this->currentStep === self::STEP_IDENTIFIER;
    }

    /**
     * Check if current step is OTP verification
     */
    public function isOtpStep(): bool
    {
        return $this->currentStep === self::STEP_OTP;
    }

    /**
     * Check if current step is password reset
     */
    public function isResetStep(): bool
    {
        return $this->currentStep === self::STEP_RESET;
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.auth.forgot-password-form');
    }
}
