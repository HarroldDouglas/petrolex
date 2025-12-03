<?php

return [
    // Authentication messages
    'login_success' => 'Connexion réussie',
    'login_failed' => 'Identifiants invalides',
    'logout_success' => 'Déconnexion réussie',
    'unauthorized' => 'Non autorisé',
    'token_expired' => 'Jeton expiré',
    'token_invalid' => 'Jeton invalide',

    // Customer messages
    'customer_created_success' => 'Client créé avec succès. Un OTP a été envoyé pour vérification.',
    'customer_updated_success' => 'Profil client mis à jour avec succès',
    'customer_not_found' => 'Client non trouvé',

    // Profile messages
    'profile_updated_success' => 'Profil mis à jour avec succès',
    'profile_not_found' => 'Profil non trouvé',

    // OTP messages
    'otp_sent_success' => 'Code OTP envoyé avec succès',
    'otp_verified_success' => 'Code OTP vérifié avec succès',
    'otp_invalid' => 'Code OTP invalide',
    'otp_expired' => 'Code OTP expiré',

    // Validation messages
    'validation_failed' => 'Erreur de validation',
    'field_required' => 'Ce champ est requis',
    'field_invalid' => 'Ce champ n\'est pas valide',
    'email_invalid' => 'L\'adresse e-mail n\'est pas valide',
    'phone_invalid' => 'Le numéro de téléphone n\'est pas valide',
    'password_min_length' => 'Le mot de passe doit contenir au moins :min caractères',

    // Address messages
    'address_created_success' => 'Adresse de livraison créée avec succès',
    'address_updated_success' => 'Adresse de livraison mise à jour avec succès',
    'address_deleted_success' => 'Adresse de livraison supprimée avec succès',
    'address_not_found' => 'Adresse non trouvée',

    // Order messages
    'order_created_success' => 'Commande créée avec succès. Procédez au paiement.',
    'order_details_retrieved' => 'Détails de la commande récupérés avec succès',
    'order_cancelled_success' => 'Commande annulée avec succès',
    'order_delivered_success' => 'Commande marquée comme livrée avec succès',
    'order_comment_added_success' => 'Commentaire ajouté à la commande avec succès',
    'order_not_belongs_to_you' => 'Cette commande ne vous appartient pas',
    'order_cannot_be_cancelled' => 'Cette commande ne peut plus être annulée. Seules les commandes en attente ou payées peuvent être annulées.',
    'order_cannot_be_delivered' => 'Cette commande ne peut pas être marquée comme livrée',
    'order_cannot_accept_payment' => 'Cette commande ne peut pas accepter de paiement. Les commandes avec des paiements en cours ou terminés ne peuvent pas être payées à nouveau.',
    'order_cannot_receive_feedback' => 'Vous ne pouvez laisser un avis que sur des commandes livrées ou annulées',
    'order_invoice_not_belongs_to_you' => 'Cette facture ne vous appartient pas',
    'order_not_authorized_to_deliver' => 'Vous n\'êtes pas autorisé à marquer cette commande comme livrée',
    'order_not_authorized_to_scan_bottles' => 'Vous n\'êtes pas autorisé à scanner des bouteilles pour cette commande',
    'order_not_authorized_role_required' => 'Vous devez être un client ou un livreur pour accéder à cette ressource',
    'customer_orders_retrieved_success' => 'Commandes client récupérées avec succès',
    'delivery_person_orders_retrieved_success' => 'Commandes livreur récupérées avec succès',

    // Payment messages
    'payment_initiated_success' => 'Paiement initié avec succès',
    'payment_wallet_success' => 'Paiement effectué avec succès via le portefeuille',
    'payment_not_belongs_to_you' => 'Ce paiement ne vous appartient pas',

    // Bottle scanning messages
    'empty_bottle_scanned_success' => 'Bouteille vide scannée avec succès',
    'empty_bottle_scan_failed' => 'Échec du scan de la bouteille vide',

    // Generic messages
    'operation_success' => 'Opération réussie',
    'operation_failed' => 'Échec de l\'opération',
    'internal_server_error' => 'Erreur interne du serveur',
    'resource_not_found' => 'Ressource non trouvée',
    'access_denied' => 'Accès refusé',
];
