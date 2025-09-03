<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniquePhoneByCountryRule implements ValidationRule
{
    protected string $countryCode;
    protected ?int $exceptUserId;

    public function __construct(string $countryCode, ?int $exceptUserId = null)
    {
        $this->countryCode = strtoupper($countryCode);
        $this->exceptUserId = $exceptUserId;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value) || empty($this->countryCode)) {
            return;
        }

        // Check if country exists in our delivery database
        $country = \App\Models\Geography\Country::where('code', $this->countryCode)->first();

        if ($country) {
            // Country exists in our DB - check by country_id
            $query = User::where('phone_number', $value)
                ->where('country_id', $country->id);
        } else {
            // Country not in our DB - for now, we'll be permissive and allow it
            // TODO: Add country_code column to users table for proper worldwide uniqueness
            return;
        }

        // Exclude current user if updating
        if ($this->exceptUserId) {
            $query->where('id', '!=', $this->exceptUserId);
        }

        if ($query->exists()) {
            $fail('Ce numéro de téléphone est déjà utilisé dans ce pays.');
        }
    }
}
