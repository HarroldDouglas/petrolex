<?php

namespace App\Http\Requests\Neighborhood;

use Illuminate\Foundation\Http\FormRequest;

class StoreNeighborhoodRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'municipalityId' => ['required', 'integer', 'exists:municipalities,id'],
            'is_active' => ['required', 'boolean'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du quartier est obligatoire.',
            'name.string' => 'Le nom du quartier doit être une chaîne de caractères.',
            'name.max' => 'Le nom du quartier ne peut pas dépasser 255 caractères.',
            'municipalityId.required' => 'La municipalité est obligatoire.',
            'municipalityId.integer' => 'La municipalité sélectionnée est invalide.',
            'municipalityId.exists' => 'La municipalité sélectionnée n\'existe pas.',
            'is_active.required' => 'Le statut est obligatoire.',
            'is_active.boolean' => 'Le statut doit être actif ou inactif.',
            'latitude.required' => 'La latitude est obligatoire (les apps mobiles plantent sans coordonnées).',
            'latitude.numeric' => 'La latitude doit être un nombre.',
            'latitude.between' => 'La latitude doit être comprise entre -90 et 90.',
            'longitude.required' => 'La longitude est obligatoire (les apps mobiles plantent sans coordonnées).',
            'longitude.numeric' => 'La longitude doit être un nombre.',
            'longitude.between' => 'La longitude doit être comprise entre -180 et 180.',
        ];
    }
}
