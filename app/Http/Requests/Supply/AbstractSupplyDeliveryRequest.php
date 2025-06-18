<?php

namespace App\Http\Requests\Supply;

use Illuminate\Foundation\Http\FormRequest;

abstract class AbstractSupplyDeliveryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'supply_date' => 'required|date',
            'description' => 'nullable|string',
            'distribution_center_id' => 'required|integer|exists:distribution_centers,id',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.required' => 'Le titre est obligatoire.',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'supply_date.required' => 'La date est obligatoire.',
            'supply_date.date' => 'La date doit être une date valide.',
            'distribution_center_id.required' => 'Le centre de distribution est obligatoire.',
            'distribution_center_id.exists' => 'Le centre de distribution sélectionné est invalide.',
        ];
    }
}
