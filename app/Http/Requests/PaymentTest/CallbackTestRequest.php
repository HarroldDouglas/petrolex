<?php

namespace App\Http\Requests\PaymentTest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CallbackTestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
            'currency' => 'sometimes|string|nullable',
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
            'amount.numeric' => '💰 Amount must be numeric',
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
            'txnid' => 'transaction ID',
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
                'timestamp' => now()->toISOString(),
            ], 422)
        );
    }

    /**
     * Check if the status indicates success
     */
    public function isSuccessfulStatus(): bool
    {
        $status = strtoupper($this->validated('status') ?? '');

        return in_array($status, config('payment.status_mappings.success_statuses', []));
    }

    /**
     * Check if the status indicates failure
     */
    public function isFailedStatus(): bool
    {
        $status = strtoupper($this->validated('status') ?? '');

        return in_array($status, config('payment.status_mappings.failed_statuses', []));
    }

    /**
     * Check if the status indicates pending
     */
    public function isPendingStatus(): bool
    {
        $status = strtoupper($this->validated('status') ?? '');

        return in_array($status, config('payment.status_mappings.pending_statuses', []));
    }

    /**
     * Get the callback status category
     */
    public function getStatusCategory(): string
    {
        if ($this->isSuccessfulStatus()) {
            return 'success';
        } elseif ($this->isFailedStatus()) {
            return 'failed';
        } elseif ($this->isPendingStatus()) {
            return 'pending';
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
            'timestamp' => $timestamp,
        ];

        foreach ($errors as $field => $messages) {
            foreach ($messages as $message) {
                $logs[] = [
                    'level' => 'error',
                    'message' => "❌ {$field}: {$message}",
                    'timestamp' => $timestamp,
                ];
            }
        }

        return $logs;
    }
}
