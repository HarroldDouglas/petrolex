<?php

namespace App\Http\Requests\Supply;

use Illuminate\Foundation\Http\FormRequest;

class RegisterGasBottleRequest extends FormRequest
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
            'selectedBottleType' => 'required',
            'incomingQuantity' => 'required|numeric|min:1',
            'outgoingQuantity' => 'required|numeric|min:0|lte:incomingQuantity',
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
            'selectedBottleType.required' => 'Le type de bouteille est obligatoire.',
            'incomingQuantity.required' => 'La quantité est obligatoire.',
            'incomingQuantity.numeric' => 'La quantité doit être un nombre.',
            'incomingQuantity.min' => 'La quantité doit être au minimum 1.',
            'outgoingQuantity.required' => 'La quantité sortante est obligatoire.',
            'outgoingQuantity.numeric' => 'La quantité sortante doit être un nombre.',
            'outgoingQuantity.min' => 'La quantité sortante ne peut pas être négative.',
            'outgoingQuantity.lte' => 'La quantité sortante ne peut pas dépasser la quantité entrante.',
        ];
    }
}
