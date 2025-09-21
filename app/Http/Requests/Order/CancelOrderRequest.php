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
            'cancelled_reason' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'cancelled_reason.max' => 'La raison de l\'annulation ne peut pas dépasser 255 caractères.',
        ];
    }
}
