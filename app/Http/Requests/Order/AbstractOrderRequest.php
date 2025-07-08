<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class AbstractOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'delivery_address_id' => ['nullable', 'exists:customer_delivery_addresses,id'],
            'delivery_person_id' => ['nullable', 'exists:delivery_people,id'],
            'distribution_center_id' => ['required', 'exists:distribution_centers,id'],
            'order_number' => ['required', 'string', 'max:255'],
            'delivery_type' => ['required'],
            'status' => ['required'],
            'subtotal' => ['required', 'numeric'],
            'delivery_fee' => ['required', 'numeric'],
            'total_amount' => ['required', 'numeric'],
            'order_date' => ['required', 'date'],
            'delivery_date' => ['nullable', 'date'],
            'comments' => ['nullable', 'string'],
            'center_comments' => ['nullable', 'string'],
            'rating' => ['nullable', 'integer'],
            'items' => ['required', 'array'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Le client est requis.',
            'customer_id.exists' => 'Le client sélectionné est invalide.',
            'delivery_address_id.exists' => "L'adresse de livraison sélectionnée est invalide.",
            'delivery_person_id.exists' => 'Le livreur sélectionné est invalide.',
            'distribution_center_id.required' => 'Le centre de distribution est requis.',
            'distribution_center_id.exists' => 'Le centre de distribution sélectionné est invalide.',
            'order_number.required' => 'Le numéro de commande est requis.',
            'order_number.string' => 'Le numéro de commande doit être une chaîne de caractères.',
            'order_number.max' => 'Le numéro de commande ne doit pas dépasser 255 caractères.',
            'delivery_type.required' => 'Le type de livraison est requis.',
            'status.required' => 'Le statut est requis.',
            'subtotal.required' => 'Le sous-total est requis.',
            'subtotal.numeric' => 'Le sous-total doit être un nombre.',
            'delivery_fee.required' => 'Les frais de livraison sont requis.',
            'delivery_fee.numeric' => 'Les frais de livraison doivent être un nombre.',
            'total_amount.required' => 'Le montant total est requis.',
            'total_amount.numeric' => 'Le montant total doit être un nombre.',
            'order_date.required' => 'La date de commande est requise.',
            'order_date.date' => 'La date de commande doit être une date valide.',
            'delivery_date.date' => 'La date de livraison doit être une date valide.',
            'comments.string' => 'Les commentaires doivent être une chaîne de caractères.',
            'center_comments.string' => 'Les commentaires du centre doivent être une chaîne de caractères.',
            'rating.integer' => 'La note doit être un nombre entier.',
            'items.required' => 'Les articles sont requis.',
            'items.array' => 'Les articles doivent être un tableau.',
            'items.*.product_id.required' => "L'identifiant du produit est requis pour chaque article.",
            'items.*.product_id.exists' => 'Le produit sélectionné pour un article est invalide.',
            'items.*.quantity.required' => 'La quantité est requise pour chaque article.',
            'items.*.quantity.integer' => 'La quantité doit être un nombre entier pour chaque article.',
            'items.*.quantity.min' => 'La quantité doit être au moins 1 pour chaque article.',
            'items.*.unit_price.required' => 'Le prix unitaire est requis pour chaque article.',
            'items.*.unit_price.numeric' => 'Le prix unitaire doit être un nombre pour chaque article.',
        ];
    }
}
