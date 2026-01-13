<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politique de Confidentialité - {{ config('privacy.company_name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            min-height: 100vh;
        }

        .header {
            text-align: center;
            padding: 40px 20px;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 40px;
        }

        .header h1 {
            color: #1a1a1a;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 1.1em;
        }

        .content {
            padding: 0 20px 40px;
        }

        .section {
            margin-bottom: 40px;
        }

        .section h2 {
            color: #1a1a1a;
            font-size: 1.8em;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .section h3 {
            color: #2c2c2c;
            font-size: 1.3em;
            margin-top: 25px;
            margin-bottom: 10px;
        }

        .section p {
            margin-bottom: 15px;
            text-align: justify;
        }

        .section ul {
            margin-left: 30px;
            margin-bottom: 15px;
        }

        .section li {
            margin-bottom: 10px;
        }

        .highlight {
            background-color: #fff3cd;
            padding: 15px;
            border-left: 4px solid #ffc107;
            margin-bottom: 20px;
        }

        .contact-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-top: 30px;
        }

        .footer {
            text-align: center;
            padding: 30px 20px;
            border-top: 2px solid #e0e0e0;
            margin-top: 40px;
            color: #666;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2em;
            }

            .section h2 {
                font-size: 1.5em;
            }

            .container {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Politique de Confidentialité</h1>
            <p>{{ config('privacy.company_name') }} - {{ config('privacy.app_description') }}</p>
            <p><small>Dernière mise à jour: {{ date('d/m/Y') }}</small></p>
        </div>

        <div class="content">
            <div class="section">
                <h2>1. Introduction</h2>
                <p>
                    Bienvenue chez {{ config('privacy.company_name') }}. Nous nous engageons à protéger et respecter votre vie privée. Cette politique de confidentialité explique comment nous collectons, utilisons, partageons et protégeons vos informations personnelles lorsque vous utilisez nos applications mobiles (client et livreur) et nos services.
                </p>
                <p>
                    En utilisant nos services, vous acceptez les pratiques décrites dans cette politique de confidentialité. Si vous n'acceptez pas ces termes, veuillez ne pas utiliser nos applications.
                </p>
            </div>

            <div class="section">
                <h2>2. Informations que nous collectons</h2>

                <h3>2.1 Informations que vous nous fournissez</h3>
                <ul>
                    <li><strong>Informations de compte:</strong> Nom, prénom, numéro de téléphone, adresse e-mail</li>
                    <li><strong>Informations de livraison:</strong> Adresse de livraison, instructions de livraison</li>
                    <li><strong>Informations de paiement:</strong> Informations liées aux transactions (via Mobile Money)</li>
                    <li><strong>Communications:</strong> Messages envoyés via l'application, commentaires et avis</li>
                </ul>

                <h3>2.2 Informations collectées automatiquement</h3>
                <ul>
                    <li><strong>Données de localisation:</strong> Localisation GPS pour faciliter les livraisons</li>
                    <li><strong>Données d'utilisation:</strong> Informations sur votre utilisation de l'application</li>
                    <li><strong>Données de l'appareil:</strong> Type d'appareil, système d'exploitation, identifiants uniques</li>
                    <li><strong>Données de connexion:</strong> Adresse IP, horodatages des connexions</li>
                </ul>
            </div>

            <div class="section">
                <h2>3. Utilisation de vos informations</h2>
                <p>Nous utilisons vos informations pour:</p>
                <ul>
                    <li>Traiter et livrer vos commandes de gaz</li>
                    <li>Gérer votre compte et vous fournir un support client</li>
                    <li>Améliorer nos services et développer de nouvelles fonctionnalités</li>
                    <li>Communiquer avec vous concernant vos commandes et nos services</li>
                    <li>Assurer la sécurité et prévenir la fraude</li>
                    <li>Respecter nos obligations légales et réglementaires</li>
                    <li>Gérer votre portefeuille électronique (wallet) et vos transactions</li>
                </ul>
            </div>

            <div class="section">
                <h2>4. Partage de vos informations</h2>
                <p>Nous ne vendons pas vos informations personnelles. Nous partageons vos informations uniquement dans les cas suivants:</p>
                <ul>
                    <li><strong>Avec les livreurs:</strong> Pour faciliter la livraison de vos commandes</li>
                    <li><strong>Prestataires de services:</strong> Partenaires de paiement (Mobile Money), services d'hébergement</li>
                    <li><strong>Obligations légales:</strong> Si requis par la loi ou pour protéger nos droits</li>
                    <li><strong>Avec votre consentement:</strong> Dans d'autres cas, avec votre autorisation explicite</li>
                </ul>
            </div>

            <div class="section">
                <h2>5. Sécurité des données</h2>
                <p>
                    Nous mettons en œuvre des mesures de sécurité techniques et organisationnelles appropriées pour protéger vos informations personnelles contre:
                </p>
                <ul>
                    <li>L'accès non autorisé</li>
                    <li>La divulgation, modification ou destruction non autorisée</li>
                    <li>La perte accidentelle</li>
                </ul>
                <p>
                    Cependant, aucune méthode de transmission sur Internet ou de stockage électronique n'est 100% sécurisée. Nous ne pouvons garantir une sécurité absolue.
                </p>
            </div>

            <div class="section">
                <h2>6. Conservation des données</h2>
                <p>
                    Nous conservons vos informations personnelles aussi longtemps que nécessaire pour:
                </p>
                <ul>
                    <li>Fournir nos services</li>
                    <li>Respecter nos obligations légales et réglementaires</li>
                    <li>Résoudre les litiges et appliquer nos accords</li>
                </ul>
                <p>
                    Lorsque vos informations ne sont plus nécessaires, nous les supprimons de manière sécurisée ou les anonymisons.
                </p>
            </div>

            <div class="section">
                <h2>7. Vos droits</h2>
                <p>Vous avez le droit de:</p>
                <ul>
                    <li><strong>Accès:</strong> Demander une copie de vos informations personnelles</li>
                    <li><strong>Rectification:</strong> Corriger vos informations inexactes ou incomplètes</li>
                    <li><strong>Suppression:</strong> Demander la suppression de vos données personnelles</li>
                    <li><strong>Opposition:</strong> Vous opposer au traitement de vos données</li>
                    <li><strong>Portabilité:</strong> Recevoir vos données dans un format structuré</li>
                    <li><strong>Retrait du consentement:</strong> Retirer votre consentement à tout moment</li>
                </ul>
                <p>
                    Pour exercer ces droits, veuillez nous contacter via les coordonnées ci-dessous.
                </p>
            </div>

            <div class="section">
                <h2>8. Données de localisation</h2>
                <p>
                    Notre application utilise les services de localisation pour:
                </p>
                <ul>
                    <li>Déterminer votre adresse de livraison</li>
                    <li>Suivre la livraison en temps réel</li>
                    <li>Optimiser les itinéraires de livraison</li>
                </ul>
                <p>
                    Vous pouvez désactiver les services de localisation dans les paramètres de votre appareil, mais cela peut affecter certaines fonctionnalités de l'application.
                </p>
            </div>

            <div class="section">
                <h2>9. Cookies et technologies similaires</h2>
                <p>
                    Nous utilisons des cookies et des technologies similaires pour améliorer votre expérience, analyser l'utilisation de nos services et personnaliser le contenu.
                </p>
            </div>

            <div class="section">
                <h2>10. Protection des mineurs</h2>
                <p>
                    Nos services sont destinés aux personnes âgées de 18 ans et plus. Nous ne collectons pas sciemment d'informations personnelles auprès de mineurs. Si vous pensez qu'un mineur nous a fourni des informations, veuillez nous contacter immédiatement.
                </p>
            </div>

            <div class="section">
                <h2>11. Modifications de cette politique</h2>
                <p>
                    Nous pouvons mettre à jour cette politique de confidentialité de temps en temps. Nous vous informerons de tout changement important en publiant la nouvelle politique sur cette page et en mettant à jour la date de "dernière mise à jour".
                </p>
                <p>
                    Nous vous encourageons à consulter régulièrement cette politique pour rester informé de la manière dont nous protégeons vos informations.
                </p>
            </div>

            <div class="section">
                <h2>12. Contact</h2>
                <div class="contact-info">
                    <p><strong>Pour toute question concernant cette politique de confidentialité, veuillez nous contacter:</strong></p>
                    <p><strong>{{ config('privacy.company_name') }}</strong></p>
                    <p>Email: {{ config('privacy.contact.email') }}</p>
                    <p>Téléphone: {{ config('privacy.contact.phone') }}</p>
                    <p>Adresse: {{ config('privacy.contact.address') }}</p>
                </div>
            </div>

            <div class="highlight">
                <p><strong>Note importante:</strong> En utilisant nos services, vous confirmez avoir lu, compris et accepté cette politique de confidentialité.</p>
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('privacy.company_name') }}. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
