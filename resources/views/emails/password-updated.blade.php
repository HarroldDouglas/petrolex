<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Mis à jour de votre mot de passe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
        }

        .otp-container {
            text-align: center;
            margin: 30px 0;
        }

        .otp-code {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 5px;
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
    </style>
</head>

<body>
    <h2>Mis à jour de votre mot de passe</h2>
    
    <p>Bonjour {{ $user->first_name }},</p>

    <p>Votre mot de passe a été mis à jour avec succès.</p>

    <p>Si vous n'êtes pas à l'origine de ce changement, veuillez nous contacter immédiatement.</p>

    <p>Cordialement,</p>
    <p>Votre équipe</p>
</body>

</html>
