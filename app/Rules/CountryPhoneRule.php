<?php

namespace App\Rules;

use App\Services\Validation\PhoneValidationService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CountryPhoneRule implements ValidationRule
{
    protected string $countryCode;
    protected PhoneValidationService $phoneValidationService;

    public function __construct(string $countryCode)
    {
        $this->countryCode = strtoupper($countryCode);
        $this->phoneValidationService = new PhoneValidationService;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value) || empty($this->countryCode)) {
            return;
        }

        // Validate phone format for the country
        if (! $this->phoneValidationService->validatePhoneForCountry($value, $this->countryCode)) {
            $message = $this->phoneValidationService->getValidationMessage($this->countryCode);
            $fail("Le numéro de téléphone n'est pas valide pour le pays {$this->countryCode}. {$message}");
        }
    }
}
