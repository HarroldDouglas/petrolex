<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user.
    |
    */

    'failed' => 'Ces identifiants ne correspondent pas à nos enregistrements.',
    'password' => 'Le mot de passe fourni est incorrect.',
    'throttle' => 'Tentatives de connexion trop nombreuses. Veuillez essayer de nouveau dans :seconds secondes.',

    // Custom authentication messages
    'login_success' => 'Connexion réussie!',
    'error_occurred' => 'Une erreur est survenue lors de la connexion. Veuillez réessayer.',
    'email_or_phone' => 'Email ou Téléphone',
    'remember_me' => 'Souvenez-vous de moi',
    'forgot_password' => 'Mot de passe oublié ?',
    'login' => 'Se connecter',
    'logging_in' => 'Connexion...',

    // Custom validation messages
    'identifier_required' => 'Veuillez saisir un email ou un numéro de téléphone.',
    'password_required' => 'Le mot de passe est requis.',

    // Forgot password translations
    'reset_password' => 'Réinitialisation du mot de passe',
    'email_or_phone' => 'Email ou Téléphone',
    'enter_email_or_phone' => 'Entrez votre email ou téléphone',
    'back_to_login' => 'Retour à la connexion',
    'send_reset_code' => 'Envoyer le code',
    'sending' => 'Envoi en cours',
    'verification_code' => 'Code de vérification',
    'verify_code' => 'Vérifier le code',
    'verifying' => 'Vérification',
    'otp_instructions' => 'Nous avons envoyé un code à 6 chiffres sur votre email ou téléphone. Veuillez le saisir ci-dessous.',
    'otp_sent' => 'Un code de vérification a été envoyé à votre adresse email ou numéro de téléphone.',
    'otp_required' => 'Le code de vérification est requis.',
    'otp_digits' => 'Le code de vérification doit contenir 6 chiffres.',
    'invalid_otp' => 'Code de vérification incorrect.',
    'otp_verified' => 'Code vérifié avec succès.',
    'new_password' => 'Nouveau mot de passe',
    'confirm_password' => 'Confirmez le mot de passe',
    'update_password' => 'Mettre à jour le mot de passe',
    'updating' => 'Mise à jour',
    'password_min' => 'Le mot de passe doit contenir au moins :min caractères.',
    'password_confirmed' => 'Les mots de passe ne correspondent pas.',
    'password_confirmation_required' => 'La confirmation du mot de passe est requise.',
    'password_reset_success' => 'Votre mot de passe a été réinitialisé avec succès.',
    'user_not_found' => 'Aucun compte trouvé avec ces informations.',
    'forgot_password' => 'Mot de passe oublié ?',

    // Additional translations for OTP
    'otp_delivery_failed' => 'Impossible d\'envoyer le code de vérification. Veuillez réessayer.',
    'resend_code' => 'Renvoyer le code',
    'otp_resent' => 'Un nouveau code de vérification a été envoyé.',
    'sent_to' => 'Envoyé à',
];
