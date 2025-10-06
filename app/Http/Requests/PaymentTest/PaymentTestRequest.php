<?php

namespace App\Http\Requests\PaymentTest;

use App\Services\PaymentTest\PaymentTestConstants;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PaymentTestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow all requests for testing
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone_number' => [
                'required',
                'string',
                'min:9',
                'max:15'
            ],
            'amount' => [
                'required',
                'numeric',
                'min:10',
                'max:1000000'
            ],
            'test_mode' => [
                'sometimes',
                'string',
                'in:' . PaymentTestConstants::MODE_SANDBOX . ',' . PaymentTestConstants::MODE_LIVE
            ],
            'external_id' => [
                'sometimes',
                'nullable',
                'string',
                'max:50'
            ],
            'reference' => [
                'sometimes',
                'nullable',
                'string',
                'max:100'
            ]
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'phone_number.required' => '📱 Phone number is required',
            'phone_number.min' => '📱 Phone number must be at least 9 characters',
            'phone_number.max' => '📱 Phone number cannot exceed 15 characters',
            'amount.required' => '💰 Amount is required',
            'amount.numeric' => '💰 Amount must be a valid number',
            'amount.min' => '💰 Minimum amount is 10 FCFA',
            'amount.max' => '💰 Maximum amount is 1,000,000 FCFA',
            'test_mode.in' => '🔧 Test mode must be either "sandbox" or "live"',
            'external_id.max' => '🆔 External ID cannot exceed 50 characters',
            'reference.max' => '📝 Reference cannot exceed 100 characters'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'phone_number' => 'phone number',
            'external_id' => 'external ID',
            'test_mode' => 'test mode'
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Payment validation failed',
                'errors' => $validator->errors()->toArray(),
                'logs' => $this->generateValidationLogs($validator->errors()->toArray()),
                'timestamp' => now()->toISOString()
            ], 422)
        );
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // This method will be called before validation
        // We can add any pre-processing logic here
    }

    /**
     * Validate phone number format for specific provider
     */
    private function isValidPhoneForProvider(string $phone, ?string $provider): bool
    {
        // Remove any spaces, dashes, or plus signs
        $cleanPhone = preg_replace('/[^\d]/', '', $phone);
        
        return match($provider) {
            PaymentTestConstants::PROVIDER_MTN => $this->isValidMTNPhone($cleanPhone),
            PaymentTestConstants::PROVIDER_ORANGE => $this->isValidOrangePhone($cleanPhone),
            default => strlen($cleanPhone) >= 9 && strlen($cleanPhone) <= 15
        };
    }

    /**
     * Validate MTN phone number format
     */
    private function isValidMTNPhone(string $phone): bool
    {
        // MTN Cameroon patterns: 677, 678, 679, 680, 681, 682, 683
        $mtnPatterns = [
            '/^237677\d{6}$/',  // International format
            '/^677\d{6}$/',     // National format
            '/^237678\d{6}$/',
            '/^678\d{6}$/',
            '/^237679\d{6}$/',
            '/^679\d{6}$/',
            '/^237680\d{6}$/',
            '/^680\d{6}$/',
            '/^237681\d{6}$/',
            '/^681\d{6}$/',
            '/^237682\d{6}$/',
            '/^682\d{6}$/',
            '/^237683\d{6}$/',
            '/^683\d{6}$/'
        ];

        foreach ($mtnPatterns as $pattern) {
            if (preg_match($pattern, $phone)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate Orange phone number format
     */
    private function isValidOrangePhone(string $phone): bool
    {
        // Orange Cameroon patterns: 690, 691, 692, 693, 694, 695, 696, 697, 698, 699
        $orangePatterns = [
            '/^23769[0-9]\d{6}$/',  // International format
            '/^69[0-9]\d{6}$/'      // National format
        ];

        foreach ($orangePatterns as $pattern) {
            if (preg_match($pattern, $phone)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Clean phone number by removing unwanted characters
     */
    private function cleanPhoneNumber(string $phone): string
    {
        // Remove spaces, dashes, parentheses, plus signs
        $cleaned = preg_replace('/[\s\-\(\)\+]/', '', $phone);
        
        // If it starts with 00237, convert to +237 format then remove +
        if (str_starts_with($cleaned, '00237')) {
            $cleaned = substr($cleaned, 2);
        }
        
        // If it starts with +237, remove the +
        if (str_starts_with($cleaned, '+237')) {
            $cleaned = substr($cleaned, 1);
        }
        
        return $cleaned;
    }

    /**
     * Generate validation error logs for frontend display
     */
    private function generateValidationLogs(array $errors): array
    {
        $logs = [];
        $timestamp = now()->toISOString();
        
        $logs[] = [
            'level' => 'error',
            'message' => '❌ Payment validation failed',
            'timestamp' => $timestamp
        ];

        foreach ($errors as $field => $messages) {
            foreach ($messages as $message) {
                $logs[] = [
                    'level' => 'error',
                    'message' => "❌ {$field}: {$message}",
                    'timestamp' => $timestamp
                ];
            }
        }

        return $logs;
    }

    /**
     * Get validated and sanitized payment data
     */
    public function getPaymentData(): array
    {
        return [
            'phone_number' => $this->validated('phone_number'),
            'amount' => $this->validated('amount'),
            'external_id' => $this->validated('external_id'),
            'test_mode' => $this->validated('test_mode', PaymentTestConstants::MODE_SANDBOX),
            'reference' => $this->validated('reference') ?? 'Petrolex Test Payment - ' . now()->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Check if this is a test scenario phone number
     */
    public function isTestScenario(string $provider): bool
    {
        $phone = $this->validated('phone_number');
        
        if ($provider === PaymentTestConstants::PROVIDER_MTN) {
            return in_array($phone, ['677000001', '677000002', '677000003']);
        }
        
        if ($provider === PaymentTestConstants::PROVIDER_ORANGE) {
            return in_array($phone, ['690000001', '690000002', '690000003']);
        }
        
        return false;
    }
}