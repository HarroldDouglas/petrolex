<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

final class ScanEmptyBottleRequest extends FormRequest
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
            'barcode' => ['required', 'string'],
            'order_item_id' => ['required', 'integer', 'exists:order_items,id'],
        ];
    }

    public function messages()
    {
        return [
            'barcode.required' => 'Le code-barres de la bouteille est requis.',
            'barcode.string' => 'Le code-barres doit être une chaîne de caractères.',
            'order_item_id.required' => 'L\'ID de l\'article de commande est requis.',
            'order_item_id.integer' => 'L\'ID de l\'article de commande doit être un entier.',
            'order_item_id.exists' => 'L\'article de commande spécifié n\'existe pas.',
        ];
    }
}
