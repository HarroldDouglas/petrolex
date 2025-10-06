<?php

namespace App\Http\Requests\PaymentTest;

use App\Services\PaymentTest\PaymentTestConstants;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CallbackTestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow all callback requests for testing
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Common fields
            'status' => 'required|string',
            
            // MTN fields
            'referenceId' => 'sometimes|string|nullable',
            'reference_id' => 'sometimes|string|nullable',
            'financialTransactionId' => 'sometimes|string|nullable',
            'financial_transaction_id' => 'sometimes|string|nullable',
            'reason' => 'sometimes|string|nullable',
            'externalId' => 'sometimes|string|nullable',
            'external_id' => 'sometimes|string|nullable',
            
            // Orange fields
            'payToken' => 'sometimes|string|nullable',
            'pay_token' => 'sometimes|string|nullable',
            'txnid' => 'sometimes|string|nullable',
            'transaction_id' => 'sometimes|string|nullable',
            'msisdn' => 'sometimes|string|nullable',
            'reference' => 'sometimes|string|nullable',
            
            // Common optional fields
            'message' => 'sometimes|string|nullable',
            'amount' => 'sometimes|numeric|nullable',
            'currency' => 'sometimes|string|nullable'
        ];
    }

    /**
     * Get MTN callback validation rules
     */
    private function getMTNCallbackRules(): array
    {
        return [
            'referenceId' => 'sometimes|string',
            'reference_id' => 'sometimes|string', // Alternative field name
            'status' => 'required|string',
            'financialTransactionId' => 'sometimes|string|nullable',
            'financial_transaction_id' => 'sometimes|string|nullable', // Alternative field name
            'reason' => 'sometimes|string|nullable',
            'message' => 'sometimes|string|nullable',
            'amount' => 'sometimes|numeric|nullable',
            'currency' => 'sometimes|string|nullable',
            'externalId' => 'sometimes|string|nullable',
            'external_id' => 'sometimes|string|nullable' // Alternative field name
        ];
    }

    /**
     * Get Orange callback validation rules
     */
    private function getOrangeCallbackRules(): array
    {
        return [
            'payToken' => 'sometimes|string',
            'pay_token' => 'sometimes|string', // Alternative field name
            'status' => 'required|string',
            'txnid' => 'sometimes|string|nullable',
            'transaction_id' => 'sometimes|string|nullable', // Alternative field name
            'message' => 'sometimes|string|nullable',
            'amount' => 'sometimes|numeric|nullable',
            'currency' => 'sometimes|string|nullable',
            'msisdn' => 'sometimes|string|nullable',
            'reference' => 'sometimes|string|nullable'
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'status.required' => '📊 Callback status is required',
            'status.string' => '📊 Callback status must be a string',
            'referenceId.string' => '🔗 Reference ID must be a string',
            'payToken.string' => '🎫 Pay token must be a string',
            'amount.numeric' => '💰 Amount must be numeric'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'referenceId' => 'reference ID',
            'financialTransactionId' => 'financial transaction ID',
            'payToken' => 'pay token',
            'txnid' => 'transaction ID'
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
                'message' => 'Callback validation failed',
                'errors' => $validator->errors()->toArray(),
                'logs' => $this->generateValidationLogs($validator->errors()->toArray()),
                'timestamp' => now()->toISOString()
            ], 422)
        );
    }

    /**
     * Get validated callback data
     */
    public function getCallbackData(): array
    {
        return $this->validated();
    }

    /**
     * Get normalized callback data (handles different field name formats)
     */
    public function getNormalizedCallbackData(string $provider): array
    {
        $data = $this->validated();
        
        if ($provider === PaymentTestConstants::PROVIDER_MTN) {
            return $this->normalizeMTNData($data);
        } elseif ($provider === PaymentTestConstants::PROVIDER_ORANGE) {
            return $this->normalizeOrangeData($data);
        }
        
        return $data;
    }

    /**
     * Normalize MTN callback data
     */
    private function normalizeMTNData(array $data): array
    {
        return [
            'reference_id' => $data['referenceId'] ?? $data['reference_id'] ?? null,
            'status' => $data['status'],
            'financial_transaction_id' => $data['financialTransactionId'] ?? $data['financial_transaction_id'] ?? null,
            'reason' => $data['reason'] ?? $data['message'] ?? null,
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? null,
            'external_id' => $data['externalId'] ?? $data['external_id'] ?? null
        ];
    }

    /**
     * Normalize Orange callback data
     */
    private function normalizeOrangeData(array $data): array
    {
        return [
            'pay_token' => $data['payToken'] ?? $data['pay_token'] ?? null,
            'status' => $data['status'],
            'transaction_id' => $data['txnid'] ?? $data['transaction_id'] ?? null,
            'message' => $data['message'] ?? null,
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? null,
            'msisdn' => $data['msisdn'] ?? null,
            'reference' => $data['reference'] ?? null
        ];
    }

    /**
     * Check if the status indicates success
     */
    public function isSuccessfulStatus(): bool
    {
        $status = strtoupper($this->validated('status') ?? '');
        return in_array($status, PaymentTestConstants::SUCCESS_STATUSES);
    }

    /**
     * Check if the status indicates failure
     */
    public function isFailedStatus(): bool
    {
        $status = strtoupper($this->validated('status') ?? '');
        return in_array($status, PaymentTestConstants::FAILED_STATUSES);
    }

    /**
     * Check if the status indicates pending
     */
    public function isPendingStatus(): bool
    {
        $status = strtoupper($this->validated('status') ?? '');
        return in_array($status, PaymentTestConstants::PENDING_STATUSES);
    }

    /**
     * Get the callback status category
     */
    public function getStatusCategory(): string
    {
        if ($this->isSuccessfulStatus()) {
            return PaymentTestConstants::STATUS_SUCCESSFUL;
        } elseif ($this->isFailedStatus()) {
            return PaymentTestConstants::STATUS_FAILED;
        } elseif ($this->isPendingStatus()) {
            return PaymentTestConstants::STATUS_PENDING;
        }
        
        return 'unknown';
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
            'message' => '❌ Callback validation failed',
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
}