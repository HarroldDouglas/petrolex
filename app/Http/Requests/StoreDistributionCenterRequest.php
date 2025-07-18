<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDistributionCenterRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:distribution_centers,name',
            'countryId' => 'required|integer|exists:countries,id',
            'cityId' => 'required|integer|exists:cities,id',
            'neighborhoodId' => 'required|integer|exists:neighborhoods,id',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'storage_capacity' => 'nullable|numeric',
            'is_active' => 'required|boolean',
            'description' => 'nullable|string',
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
            'name.required' => 'Le nom du centre de distribution est requis',
            'name.unique' => 'Ce nom de centre de distribution existe déjà',
            'countryId.required' => 'Le pays est requis',
            'countryId.integer' => 'L\'ID du pays doit être un entier',
            'countryId.exists' => 'Le pays sélectionné n\'existe pas',
            'cityId.required' => 'La ville est requise',
            'cityId.integer' => 'L\'ID de la ville doit être un entier',
            'cityId.exists' => 'La ville sélectionnée n\'existe pas',
            'neighborhoodId.required' => 'Le quartier est requis',
            'neighborhoodId.integer' => 'L\'ID du quartier doit être un entier',
            'neighborhoodId.exists' => 'Le quartier sélectionné n\'existe pas',
            'address.required' => 'L\'adresse est requise',
            'phone.required' => 'Le numéro de téléphone est requis',
            'email.required' => 'L\'email est requis',
            'email.email' => 'Veuillez entrer une adresse email valide',
            'storage_capacity.numeric' => 'La capacité de stockage doit être un nombre',
            'latitude.numeric' => 'La latitude doit être un nombre',
            'longitude.numeric' => 'La longitude doit être un nombre',
        ];
    }
}
