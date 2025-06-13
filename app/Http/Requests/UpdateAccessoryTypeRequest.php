<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccessoryTypeRequest extends FormRequest
{
    /**
     * Accessory type ID
     */
    protected $accessoryTypeId;

    /**
     * Constructor
     */
    public function __construct($accessoryTypeId = null)
    {
        parent::__construct();
        $this->accessoryTypeId = $accessoryTypeId;
    }

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:255|unique:accessory_types,name,'.$this->accessoryTypeId,
            'price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'is_active' => 'boolean',
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048',
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
            'name.required' => 'Le nom du type d\'accessoire est requis',
            'name.min' => 'Le nom doit contenir au moins 3 caractères',
            'name.unique' => 'Ce nom de type d\'accessoire existe déjà',
            'price.required' => 'Le prix est requis',
            'price.numeric' => 'Le prix doit être un nombre',
            'price.min' => 'Le prix ne peut pas être négatif',
            'description.required' => 'La description est requise',
            'images.*.image' => 'Tous les fichiers doivent être des images',
            'images.*.max' => 'La taille des images ne doit pas dépasser 2MB',
        ];
    }
}
