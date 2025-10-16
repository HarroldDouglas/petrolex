<?php

return [
    'messages' => [
        'delivery_address_id.required' => 'L\'adresse de livraison est obligatoire',
        'delivery_address_id.exists' => 'L\'adresse de livraison sélectionnée n\'existe pas',
        'distribution_center_id.required' => 'Le centre de distribution est obligatoire',
        'distribution_center_id.exists' => 'Le centre de distribution sélectionné n\'existe pas',
        'delivery_type.in' => 'Le type de livraison doit être l\'un des suivants : :values',
        'payment_method.in' => 'La méthode de paiement doit être l\'une des suivantes : :values',
        'items.required' => 'Au moins un article est requis',
        'items.min' => 'Au moins un article est requis',
        'items.max' => 'Vous ne pouvez pas commander plus de 50 articles différents',
        'items.*.product_category_id.required' => 'L\'ID de la catégorie de produit est obligatoire',
        'items.*.product_category_id.exists' => 'La catégorie de produit sélectionnée n\'existe pas',
        'items.*.quantity.required' => 'La quantité est obligatoire',
        'items.*.quantity.min' => 'La quantité doit être au minimum de 1',
        'items.*.quantity.max' => 'La quantité ne peut pas dépasser 100',
        'items.*.option.in' => 'L\'option doit être l\'une des valeurs suivantes : :values',
        'comments.max' => 'Les commentaires ne peuvent pas dépasser 500 caractères',

        // New validation messages
        'price_mismatch' => 'Prix incorrect: attendu :expected, fourni :provided',
        'insufficient_stock' => 'Stock insuffisant: demandé :requested, disponible :available',
        'delivery_fee_mismatch' => 'Frais de livraison incorrect: attendu :expected, fourni :provided',
        'total_amount_mismatch' => 'Montant total incorrect: attendu :expected, fourni :provided',
        'unit_price_required' => 'Le prix unitaire est requis',
        'unit_price_numeric' => 'Le prix unitaire doit être un nombre',
        'delivery_fee_required' => 'Les frais de livraison sont requis',
        'delivery_fee_numeric' => 'Les frais de livraison doivent être un nombre',
        'total_amount_required' => 'Le montant total est requis',
        'total_amount_numeric' => 'Le montant total doit être un nombre',
        'different_municipalities' => 'L\'adresse de livraison (municipalité :delivery_municipality) doit être dans la même municipalité que le centre de distribution (municipalité :center_municipality).',
    ],

    'attributes' => [
        'delivery_address_id' => 'adresse de livraison',
        'distribution_center_id' => 'centre de distribution',
        'delivery_type' => 'type de livraison',
        'payment_method' => 'méthode de paiement',
        'items' => 'articles',
        'items.*.product_category_id' => 'catégorie de produit',
        'items.*.quantity' => 'quantité',
        'items.*.option' => 'option',
        'comments' => 'commentaires',
    ],
];
