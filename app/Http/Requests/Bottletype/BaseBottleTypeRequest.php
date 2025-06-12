<?php

namespace App\Http\Requests\Bottletype;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class BaseBottleTypeRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'name' => $this->nameRules(),
            'description' => ['nullable', 'string', 'min:3'],
            'weight' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'numeric', 'min:0'],
            'content_price' => ['required', 'numeric', 'min:0'],
            'bottle_with_content_price' => ['required', 'numeric', 'min:0', 'gt:content_price'],
            'is_active' => ['required', 'boolean'],

            'cityPrices' => ['nullable', 'array'],
            'cityPrices.*.city' => ['required_with:cityPrices', 'string', 'max:255'],
            'cityPrices.*.content_price' => ['required_with:cityPrices', 'numeric', 'min:0'],
            'cityPrices.*.content_with_bottle_price' => ['required_with:cityPrices', 'numeric',
                'min:0', 'gte:cityPrices.*.content_price'],
        ];
    }

    protected function nameRules(): array
    {
        return [
            'required',
            'string',
            'max:255',
            Rule::unique('bottle_types', 'name'),
        ];
    }

    public function messages(): array
    {
        return [
            'id.exists' => 'Ce type de bouteille n\'existe pas.',

            'name.required' => 'Le nom du type de bouteille est requis.',
            'name.string' => 'Le nom du type de bouteille doit être une chaîne de caractères.',
            'name.unique' => 'Ce nom de type de bouteille existe déjà.',
            'name.max' => 'Ce nom de type de bouteille ne doit pas dépasser 255 caractères.',

            'weight.required' => 'Le poids est requis.',
            'weight.numeric' => 'Le poids doit être un nombre.',
            'weight.min' => 'Le poids doit être au moins 0.',

            'capacity.required' => 'La capacité est requise.',
            'capacity.numeric' => 'La capacité doit être un nombre.',
            'capacity.min' => 'La capacité doit être au moins 0.',

            'content_price.required' => 'Le prix de la recharge est requis.',
            'content_price.numeric' => 'Le prix de la recharge doit être un nombre.',
            'content_price.min' => 'Le prix de la recharge doit être au moins 0.',

            'bottle_with_content_price.required' => 'Le prix de la consigne avec recharge est requis.',
            'bottle_with_content_price.numeric' => 'Le prix de la consigne avec recharge doit être un nombre.',
            'bottle_with_content_price.min' => 'Le prix de la consigne avec recharge doit être au moins 0.',
            'bottle_with_content_price.gt' => 'Le prix de la consigne avec recharge doit être supérieur au prix du contenu.',

            'is_active.boolean' => 'Le statut doit être vrai ou faux.',

            'cityPrices.*.city.required_with' => 'Le nom de la ville est requis.',
            'cityPrices.*.city.string' => 'Le nom de la ville doit être une chaîne de caractères.',
            'cityPrices.*.city.max' => 'Le nom de la ville ne doit pas dépasser 255 caractères.',

            'cityPrices.*.content_price.required_with' => 'Le prix de la recharge est requis pour chaque ville.',
            'cityPrices.*.content_price.numeric' => 'Le prix de la recharge doit être un nombre.',
            'cityPrices.*.content_price.min' => 'Le prix de la recharge doit être au moins 0.',

            'cityPrices.*.content_with_bottle_price.required_with' => 'Le prix avec consigne est requis pour chaque ville.',
            'cityPrices.*.content_with_bottle_price.numeric' => 'Le prix avec consigne doit être un nombre.',
            'cityPrices.*.content_with_bottle_price.min' => 'Le prix avec consigne doit être au moins 0.',
            'cityPrices.*.content_with_bottle_price.gte' => 'Le prix avec consigne doit être supérieur ou égal au prix de la recharge.',
        ];
    }
}
