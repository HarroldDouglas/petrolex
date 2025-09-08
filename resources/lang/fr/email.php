<?php

return [
    // OTP Email
    'otp_subject' => 'Votre code de vérification',
    'otp_greeting' => 'Bonjour !',
    'otp_message' => 'Votre code de vérification pour :app est disponible sur',
    'use_code_message' => 'Utilisez le code suivant pour vérifier votre compte',
    'otp_expire' => 'Ce code expire dans 10 minutes.',
    'otp_security' => 'Si vous n\'avez pas demandé ce code, veuillez ignorer cet e-mail.',
    'otp_footer' => 'Merci de faire confiance à <strong>:app</strong>.',

    // Welcome messages
    'welcome_subject' => 'Bienvenue sur :app',
    'welcome_message' => 'Nous sommes ravis de vous accueillir !',

    // Common email elements
    'regards' => 'Cordialement',
    'team_signature' => 'L\'équipe :app',

    // Order notification emails - Simple unified approach
    'order_notification_subject' => 'Commande #:order_number',
    'order_greeting' => 'Bonjour :user_name,',
    'order_status_message' => 'Votre commande **#:order_number** a le statut : **:status**',
    'default_user_name' => 'Utilisateur',
    'unknown_customer' => 'Client inconnu',
    
    'order_details' => 'Détails de la commande',
    'order_number' => 'Numéro de commande',
    'order_status' => 'Statut',
    'order_total' => 'Montant total',
    'order_customer' => 'Client',
    'order_delivery_address' => 'Adresse de livraison',
    'order_delivered_at' => 'Livré le',
    'view_order_button' => 'Voir la commande',
    'order_footer_message' => 'Merci d\'avoir choisi nos services.',

    // Order Email Translations
    'order' => [
        'created' => [
            'customer' => [
                'subject' => 'Confirmation de votre commande #:order_number',
                'greeting' => 'Bonjour !',
                'message' => 'Votre commande a été créée avec succès ! Nous vous remercions de votre confiance.',
                'tracking_info' => 'Vous recevrez une notification dès que votre commande sera traitée.',
                'button_text' => 'Suivre ma commande',
            ],
            'manager' => [
                'subject' => 'Nouvelle commande #:order_number reçue',
                'greeting' => 'Bonjour !',
                'message' => 'Une nouvelle commande a été reçue dans votre centre de distribution.',
                'button_text' => 'Traiter la commande',
            ],
        ],
        'delivered' => [
            'customer' => [
                'subject' => 'Votre commande #:order_number a été livrée !',
                'greeting' => 'Bonjour !',
                'message' => 'Nous sommes heureux de vous confirmer que votre commande a été livrée avec succès !',
                'feedback_message' => 'Merci de votre confiance ! N\'hésitez pas à nous laisser un avis.',
                'button_text' => 'Voir ma commande',
            ],
            'manager' => [
                'subject' => 'Commande #:order_number livrée',
                'greeting' => 'Bonjour !',
                'message' => 'La commande a été marquée comme livrée avec succès.',
                'thank_you' => 'Merci pour votre excellent service !',
                'button_text' => 'Voir la commande',
            ],
        ],
        'cancelled' => [
            'customer' => [
                'subject' => 'Annulation de votre commande #:order_number',
                'greeting' => 'Bonjour !',
                'message' => 'Nous vous informons que votre commande a été annulée.',
                'refund_info' => 'Si un paiement a été effectué, le remboursement sera traité sous 3-5 jours ouvrables.',
                'support_message' => 'Pour toute question, n\'hésitez pas à nous contacter.',
                'button_text' => 'Voir mes commandes',
            ],
            'manager' => [
                'subject' => 'Commande #:order_number annulée',
                'greeting' => 'Bonjour !',
                'message' => 'La commande a été annulée dans votre centre de distribution.',
                'button_text' => 'Voir la commande',
            ],
        ],
        'details' => [
            'order_number' => 'Numéro de commande',
            'customer' => 'Client',
            'customer_phone' => 'Téléphone client',
            'total_amount' => 'Montant total',
            'delivery_type' => 'Type de livraison',
            'delivery_address' => 'Adresse de livraison',
            'order_date' => 'Date de commande',
            'delivery_date' => 'Date de livraison',
            'cancellation_date' => 'Date d\'annulation',
            'cancellation_reason' => 'Raison de l\'annulation',
        ],
    ],
];
