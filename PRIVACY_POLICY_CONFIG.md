# Configuration de la Politique de Confidentialité

## Vue d'ensemble

La page de politique de confidentialité est entièrement configurable via le fichier `.env`. Cela permet une approche professionnelle et maintenable.

## Variables de configuration

Ajoutez ou modifiez ces variables dans votre fichier `.env`:

```env
# Privacy Policy Configuration
PRIVACY_COMPANY_NAME="Votre Entreprise"
PRIVACY_APP_DESCRIPTION="Application de livraison"
PRIVACY_CONTACT_EMAIL="contact@votreentreprise.com"
PRIVACY_CONTACT_PHONE="+237 XXX XXX XXX"
PRIVACY_CONTACT_ADDRESS="Votre adresse complète"
PRIVACY_POLICY_URL="${APP_URL}/privacy-policy"
```

## Utilisation

### URL d'accès
La politique de confidentialité est accessible publiquement à:
- **Local**: http://127.0.0.1:8002/privacy-policy
- **Production**: https://votredomaine.com/privacy-policy

### Pour Play Store / App Store
Utilisez l'URL de production dans vos soumissions d'applications:
```
https://votredomaine.com/privacy-policy
```

## Personnalisation

### 1. Nom de l'entreprise
Par défaut, utilise `APP_NAME`. Pour un nom différent:
```env
PRIVACY_COMPANY_NAME="Mon Entreprise Personnalisée"
```

### 2. Description de l'application
```env
PRIVACY_APP_DESCRIPTION="Application de livraison de gaz domestique"
```

### 3. Informations de contact
```env
PRIVACY_CONTACT_EMAIL="support@monentreprise.com"
PRIVACY_CONTACT_PHONE="+237 6XX XXX XXX"
PRIVACY_CONTACT_ADDRESS="123 Rue Example, Douala, Cameroun"
```

## Fichiers impliqués

- **Configuration**: `config/privacy.php`
- **Vue**: `resources/views/privacy-policy.blade.php`
- **Route**: `routes/web.php` (ligne 8)
- **Contrôleur**: `app/Http/Controllers/PrivacyPolicyController.php`

## Après modification

Après avoir modifié les variables d'environnement:

```bash
php artisan config:clear
php artisan view:clear
```

## Avantages de cette approche

✅ **Professionnel**: Séparation des données de configuration du code  
✅ **Maintenable**: Modification facile sans toucher au code  
✅ **Réutilisable**: Même base de code pour plusieurs clients  
✅ **Sécurisé**: Informations sensibles dans .env (non versionné)  
✅ **Évolutif**: Facile d'ajouter de nouvelles variables
