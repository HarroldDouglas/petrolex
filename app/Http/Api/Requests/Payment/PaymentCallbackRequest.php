<?php

declare(strict_types=1);

namespace App\Http\Api\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class PaymentCallbackRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'application' => ['required', 'string'],
            'app_transaction_ref' => ['required', 'string', 'exists:orders,id'],
            'operator_transaction_ref' => ['required', 'string'],
            'transaction_ref' => ['required', 'string'],
            'transaction_type' => ['required', 'string', Rule::in(['PAYIN', 'PAYOUT'])],
            'transaction_amount' => ['required', 'numeric', 'min:0'],
            'transaction_fees' => ['required', 'numeric', 'min:0'],
            'transaction_currency' => ['required', 'string', Rule::in(['XAF', 'EUR'])],
            'transaction_operator' => ['required', 'string', Rule::in([
                'MCP',
                'CM_MOMO',
                'CM_OM',
                'CARD',
            ])],
            'transaction_status' => ['required', 'string', Rule::in([
                'SUCCESS',
                'CANCELED',
                'CANCELLED',
                'FAILED',
            ])],
            'transaction_reason' => ['required', 'string'],
            'transaction_message' => ['required', 'string'],
            'customer_phone_number' => ['required', 'string'],
            'signature' => ['required', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true; // Authorization handled in controller (signature verification)
    }

    public function messages(): array
    {
        $locale = app()->getLocale();

        if ($locale === 'fr') {
            return [
                'application.required' => 'Le nom de l\'application est obligatoire',
                'app_transaction_ref.required' => 'La référence de transaction de l\'application est obligatoire',
                'app_transaction_ref.exists' => 'La commande référencée n\'existe pas',
                'operator_transaction_ref.required' => 'La référence de transaction de l\'opérateur est obligatoire',
                'transaction_ref.required' => 'La référence de transaction est obligatoire',
                'transaction_type.required' => 'Le type de transaction est obligatoire',
                'transaction_type.in' => 'Le type de transaction doit être PAYIN ou PAYOUT',
                'transaction_amount.required' => 'Le montant de la transaction est obligatoire',
                'transaction_amount.numeric' => 'Le montant doit être un nombre',
                'transaction_amount.min' => 'Le montant doit être positif ou nul',
                'transaction_fees.required' => 'Les frais de transaction sont obligatoires',
                'transaction_fees.numeric' => 'Les frais doivent être un nombre',
                'transaction_fees.min' => 'Les frais doivent être positifs ou nuls',
                'transaction_currency.required' => 'La devise est obligatoire',
                'transaction_currency.in' => 'La devise doit être XAF ou EUR',
                'transaction_operator.required' => 'L\'opérateur est obligatoire',
                'transaction_operator.in' => 'L\'opérateur doit être MCP, CM_MOMO, CM_OM ou CARD',
                'transaction_status.required' => 'Le statut de la transaction est obligatoire',
                'transaction_status.in' => 'Le statut doit être SUCCESS, CANCELED, CANCELLED ou FAILED',
                'transaction_reason.required' => 'La raison de la transaction est obligatoire',
                'transaction_message.required' => 'Le message de la transaction est obligatoire',
                'customer_phone_number.required' => 'Le numéro de téléphone du client est obligatoire',
                'signature.required' => 'La signature est obligatoire',
            ];
        }

        // English messages
        return [
            'application.required' => 'The application name is required',
            'app_transaction_ref.required' => 'The application transaction reference is required',
            'app_transaction_ref.exists' => 'The referenced order does not exist',
            'operator_transaction_ref.required' => 'The operator transaction reference is required',
            'transaction_ref.required' => 'The transaction reference is required',
            'transaction_type.required' => 'The transaction type is required',
            'transaction_type.in' => 'The transaction type must be PAYIN or PAYOUT',
            'transaction_amount.required' => 'The transaction amount is required',
            'transaction_amount.numeric' => 'The amount must be a number',
            'transaction_amount.min' => 'The amount must be positive or zero',
            'transaction_fees.required' => 'The transaction fees are required',
            'transaction_fees.numeric' => 'The fees must be a number',
            'transaction_fees.min' => 'The fees must be positive or zero',
            'transaction_currency.required' => 'The currency is required',
            'transaction_currency.in' => 'The currency must be XAF or EUR',
            'transaction_operator.required' => 'The operator is required',
            'transaction_operator.in' => 'The operator must be MCP, CM_MOMO, CM_OM or CARD',
            'transaction_status.required' => 'The transaction status is required',
            'transaction_status.in' => 'The status must be SUCCESS, CANCELED, CANCELLED or FAILED',
            'transaction_reason.required' => 'The transaction reason is required',
            'transaction_message.required' => 'The transaction message is required',
            'customer_phone_number.required' => 'The customer phone number is required',
            'signature.required' => 'The signature is required',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        $locale = app()->getLocale();

        if ($locale === 'fr') {
            return [
                'application' => 'application',
                'app_transaction_ref' => 'référence de transaction de l\'application',
                'operator_transaction_ref' => 'référence de transaction de l\'opérateur',
                'transaction_ref' => 'référence de transaction',
                'transaction_type' => 'type de transaction',
                'transaction_amount' => 'montant de la transaction',
                'transaction_fees' => 'frais de transaction',
                'transaction_currency' => 'devise',
                'transaction_operator' => 'opérateur',
                'transaction_status' => 'statut de la transaction',
                'transaction_reason' => 'raison de la transaction',
                'transaction_message' => 'message de la transaction',
                'customer_phone_number' => 'numéro de téléphone du client',
                'signature' => 'signature',
            ];
        }

        return [
            'application' => 'application',
            'app_transaction_ref' => 'application transaction reference',
            'operator_transaction_ref' => 'operator transaction reference',
            'transaction_ref' => 'transaction reference',
            'transaction_type' => 'transaction type',
            'transaction_amount' => 'transaction amount',
            'transaction_fees' => 'transaction fees',
            'transaction_currency' => 'currency',
            'transaction_operator' => 'operator',
            'transaction_status' => 'transaction status',
            'transaction_reason' => 'transaction reason',
            'transaction_message' => 'transaction message',
            'customer_phone_number' => 'customer phone number',
            'signature' => 'signature',
        ];
    }
}
