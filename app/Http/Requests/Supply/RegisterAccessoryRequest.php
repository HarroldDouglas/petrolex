<?php

namespace App\Http\Requests\Supply;

use Illuminate\Foundation\Http\FormRequest;

class RegisterAccessoryRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'selectedAccessoryType' => 'required',
            'accessoryQuantity' => 'required|numeric|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'selectedAccessoryType.required' => 'Le type d\'accessoire est obligatoire.',
            'accessoryQuantity.required' => 'La quantité est obligatoire.',
            'accessoryQuantity.numeric' => 'La quantité doit être un nombre.',
            'accessoryQuantity.min' => 'La quantité doit être au minimum 1.',
        ];
    }
}
