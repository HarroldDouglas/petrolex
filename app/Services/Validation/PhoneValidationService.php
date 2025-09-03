<?php

namespace App\Services\Validation;

class PhoneValidationService
{
    protected array $validationRules;

    public function __construct()
    {
        $this->validationRules = config('phone-validation.rules', []);
    }

    /**
     * Validate a phone number for a specific country
     */
    public function validatePhoneForCountry(string $phoneNumber, string $countryCode): bool
    {
        $rules = $this->getValidationRules($countryCode);

        // Check length
        $length = strlen($phoneNumber);
        if ($length < $rules['min_length'] || $length > $rules['max_length']) {
            return false;
        }

        // Check format with regex
        if (! empty($rules['regex']) && ! preg_match($rules['regex'], $phoneNumber)) {
            return false;
        }

        return true;
    }

    /**
     * Get validation rules for a country
     */
    public function getValidationRules(string $countryCode): array
    {
        $countryCode = strtoupper($countryCode);

        return $this->validationRules[$countryCode] ?? $this->validationRules['default'];
    }

    /**
     * Get validation error message for a country
     */
    public function getValidationMessage(string $countryCode): string
    {
        $rules = $this->getValidationRules($countryCode);
        $messages = config('phone-validation.messages');

        return str_replace(
            [':country', ':min', ':max'],
            [$countryCode, $rules['min_length'], $rules['max_length']],
            $rules['description'] ?? $messages['invalid_format']
        );
    }

    /**
     * Get supported country codes
     */
    public function getSupportedCountryCodes(): array
    {
        return array_keys($this->validationRules);
    }

    /**
     * Check if a country code is explicitly supported
     */
    public function isCountrySupported(string $countryCode): bool
    {
        $countryCode = strtoupper($countryCode);

        return isset($this->validationRules[$countryCode]);
    }
}
