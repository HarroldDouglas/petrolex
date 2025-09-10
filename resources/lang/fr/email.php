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
    'order_status_message' => 'Votre commande **#:order_number** a désormais le statut : **:status**',
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

    // Specific Order Status Email Translations

    // Order Created
    'order_created_subject' => 'Nouvelle commande #:order_number créée',
    'order_created_message' => 'Votre commande **#:order_number** a été créée avec succès !',
    'order_created_footer_message' => 'Nous traiterons votre commande dans les plus brefs délais.',
    'order_created_note' => 'Vous recevrez une notification dès que votre commande sera confirmée.',

    // Order Confirmed
    'order_confirmed_subject' => 'Commande #:order_number confirmée',
    'order_confirmed_message' => 'Excellente nouvelle ! Votre commande **#:order_number** a été confirmée.',
    'order_confirmed_footer_message' => 'Votre commande est maintenant en cours de préparation.',
    'order_confirmed_next_steps' => 'Votre commande va bientôt être assignée à un livreur.',
    'order_confirmed_at' => 'Confirmée le',

    // Order Processing
    'order_processing_subject' => 'Commande #:order_number en cours de livraison',
    'order_processing_message' => 'Votre commande **#:order_number** est maintenant en cours de livraison !',
    'order_processing_footer_message' => 'Votre livreur vous contactera bientôt.',
    'order_processing_delivery_info' => 'Livraison estimée sous 2-4 heures selon le type de livraison.',
    'order_processing_at' => 'Mise en livraison le',
    'delivery_person' => 'Livreur',
    'delivery_person_phone' => 'Téléphone du livreur',
    'estimated_delivery' => 'Livraison estimée',

    // Order Delivered
    'order_delivered_subject' => 'Commande #:order_number livrée !',
    'order_delivered_celebration' => 'Livraison réussie !',
    'order_delivered_message' => 'Nous sommes heureux de vous confirmer que votre commande **#:order_number** a été livrée avec succès !',
    'order_delivered_footer_message' => 'Merci d\'avoir choisi nos services !',
    'order_delivered_feedback_message' => 'Votre avis nous intéresse ! Notez votre expérience dans l\'application.',
    'delivery_details' => 'Détails de la livraison',
    'delivered_by' => 'Livré par',
    'feedback_request' => '⭐ Donnez votre avis',
    'thank_you_for_business' => 'Merci de votre confiance !',

    // Order Cancelled
    'order_cancelled_subject' => 'Commande #:order_number annulée',
    'order_cancelled_message' => 'Nous vous informons que votre commande **#:order_number** a été annulée.',
    'order_cancelled_footer_message' => 'Nous nous excusons pour tout inconvénient causé.',
    'order_cancelled_refund_message' => 'Si un paiement a été effectué, le remboursement sera traité sous 3-5 jours ouvrables.',
    'cancellation_details' => 'Détails de l\'annulation',
    'order_cancelled_at' => 'Annulée le',
    'cancellation_reason' => 'Raison de l\'annulation',
    'refund_info' => 'Informations de remboursement',
    'contact_support_message' => 'Notre équipe support est disponible pour vous aider.',

    // Order Pending Payment
    'order_pending_subject' => 'Paiement en attente - Commande #:order_number',
    'order_pending_message' => 'Votre commande **#:order_number** est en attente de paiement.',
    'order_pending_footer_message' => 'Complétez votre paiement pour que nous puissions traiter votre commande.',
    'order_pending_payment_warning' => 'Votre commande sera annulée automatiquement si le paiement n\'est pas effectué dans les 24 heures.',
    'order_pending_timeout_warning' => 'Cette commande expirera automatiquement dans 24 heures sans paiement.',
    'payment_details' => 'Détails du paiement',
    'complete_payment_now' => 'Compléter le paiement maintenant',
    'urgent' => 'Urgent',

    // Order Paid
    'order_paid_subject' => 'Paiement confirmé - Commande #:order_number',
    'payment_confirmed' => 'Paiement confirmé',
    'order_paid_message' => 'Parfait ! Le paiement de votre commande **#:order_number** a été confirmé.',
    'order_paid_footer_message' => 'Votre commande va maintenant être traitée rapidement.',
    'order_paid_next_steps' => 'Votre commande va être confirmée et assignée à un livreur sous peu.',
    'payment_confirmation_details' => 'Détails de confirmation de paiement',
    'amount_paid' => 'Montant payé',
    'payment_date' => 'Date de paiement',
    'payment_receipt' => '🧾 Reçu de paiement',
    'keep_receipt_message' => 'Conservez cet email comme reçu de votre paiement.',

    // Order Payment Failed
    'order_payment_failed_subject' => 'Échec du paiement - Commande #:order_number',
    'order_payment_failed_message' => 'Le paiement de votre commande **#:order_number** a échoué.',
    'order_payment_failed_footer_message' => 'Veuillez réessayer le paiement pour continuer avec votre commande.',
    'payment_failure_details' => 'Détails de l\'échec du paiement',
    'failure_date' => 'Date de l\'échec',
    'possible_reasons' => 'Raisons possibles',
    'insufficient_funds' => 'Fonds insuffisants',
    'expired_card' => 'Carte expirée',
    'network_issue' => 'Problème de réseau',
    'bank_decline' => 'Refus de la banque',
    'retry_payment_now' => 'Réessayer le paiement maintenant',
    'payment_support_message' => 'Notre équipe peut vous aider avec les problèmes de paiement.',

    // Common elements
    'order_date' => 'Date de commande',
    'next_steps' => 'Prochaines étapes',
    'need_help' => 'Besoin d\'aide ?',
    'note' => 'Note',

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
