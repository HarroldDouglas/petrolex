<?php

namespace App\Http\Requests\Bottletype;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBottleTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255',
                Rule::unique('bottle_types', 'name'),
            ],
            'capacity' => ['required', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'radius' => ['nullable', 'numeric', 'min:0'],
            'content_price' => ['required', 'numeric', 'min:0'],
            'bottle_with_content_price' => ['required', 'numeric', 'min:0', 'gt:content_price'],
            'is_active' => ['required', 'boolean'],
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
            'name.required' => 'Le nom du type de bouteille est requis.',
            'name.string' => 'Le nom du type de bouteille doit être une chaîne de caractères.',
            'name.unique' => 'Ce nom de type de bouteille existe déjà.',
            'name.max' => 'Ce nom de type de bouteille ne doit pas dépasser 255 caractères.',

            'capacity.required' => 'La capacité est requise.',
            'capacity.numeric' => 'La capacité doit être un nombre.',
            'capacity.min' => 'La capacité doit être au moins 0.',

            'height.numeric' => 'La hauteur doit être un nombre.',
            'height.min' => 'La hauteur doit être au moins 0.',

            'width.numeric' => 'La largeur doit être un nombre.',
            'width.min' => 'La largeur doit être au moins 0.',

            'radius.numeric' => 'Le rayon doit être un nombre.',
            'radius.min' => 'Le rayon doit être au moins 0.',

            'content_price.numeric' => 'Le prix du contenu doit être un nombre.',
            'content_price.min' => 'Le prix du contenu doit être au moins 0.',

            'bottle_with_content_price.numeric' => 'Le prix de la bouteille avec contenu doit être un nombre.',
            'bottle_with_content_price.min' => 'Le prix de la bouteille avec contenu doit être au moins 0.',
            'bottle_with_content_price.gt' => 'Le prix de la bouteille avec contenu doit être supérieur au prix du contenu.',
            'is_active.boolean' => 'Le statut doit être vrai ou faux.',
        ];
    }
}
