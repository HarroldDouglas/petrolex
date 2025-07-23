<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Votre code d'authentification</title>
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
    <h2>Code d'authentification</h2>
    <p>Bonjour,</p>
    <p>Nous avons reçu une demande de vérification de votre identité pour {{ $maskedIdentifier }}. Veuillez utiliser le code suivant pour
        compléter le processus :</p>

    <div class="otp-container">
        <div class="otp-code">{{ $otp }}</div>
    </div>

    <p>Ce code expirera dans 10 minutes pour des raisons de sécurité.</p>
    <p>Si vous n'avez pas demandé ce code, veuillez ignorer cet e-mail ou contacter notre équipe de support si vous avez des préoccupations.</p>

    <div class="footer">
        <p>Ceci est un message automatisé, veuillez ne pas répondre à cet e-mail.</p>
    </div>
</body>

</html>
