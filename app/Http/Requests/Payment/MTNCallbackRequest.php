<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class MTNCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string',
            'amount' => 'sometimes|string|nullable',
            'currency' => 'sometimes|string|nullable',
            'financialTransactionId' => 'sometimes|string|nullable',
            'externalId' => 'sometimes|string|nullable',
            'payer' => 'sometimes|array|nullable',
            'payer.partyIdType' => 'sometimes|string|nullable',
            'payer.partyId' => 'sometimes|string|nullable',
            'reason' => 'sometimes|string|nullable',
            'payerMessage' => 'sometimes|string|nullable',
            'payeeNote' => 'sometimes|string|nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => '📊 MTN callback status is required',
            'status.string' => '📊 MTN callback status must be a string',
            'financialTransactionId.string' => '🏦 Financial Transaction ID must be a string',
            'externalId.string' => '🔗 External ID must be a string',
            'amount.string' => '💰 Amount must be a string',
        ];
    }
}
