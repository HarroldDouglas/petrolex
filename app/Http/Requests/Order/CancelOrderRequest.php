<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class CancelOrderRequest extends FormRequest
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
            'cancelled_reason' => 'required|string|max:255',
            'cancelled_by' => 'required|integer|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'cancelled_reason.required' => 'La raison de l\'annulation est requise.',
            'cancelled_by.required' => 'L\'ID de l\'utilisateur ayant annulé la commande est requis.',
        ];
    }
}
