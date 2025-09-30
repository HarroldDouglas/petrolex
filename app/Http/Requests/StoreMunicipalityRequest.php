<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMunicipalityRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:municipalities,name',
            'cityId' => 'required|exists:cities,id',
            'selectedNeighborhoods' => 'array',
            'selectedNeighborhoods.*' => 'exists:neighborhoods,id',
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
            'name.required' => 'Le nom de la municipalité est requis.',
            'name.unique' => 'Ce nom de municipalité existe déjà.',
            'cityId.required' => 'La ville est requise.',
            'cityId.exists' => 'La ville sélectionnée est invalide.',
            'selectedNeighborhoods.*.exists' => 'Un ou plusieurs quartiers sélectionnés sont invalides.',
        ];
    }
}
