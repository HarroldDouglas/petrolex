# Informations Serveurs Petrolex

## Staging
- **Domaine**: isogaz.afrik-solutions.com
- **IP**: 157.173.104.21
- **User SSH**: root
- **Chemin projet**: /var/www/isogaz
- **Branche**: dev
- **URL API**: https://isogaz.afrik-solutions.com/api
- **URL Privacy Policy**: https://isogaz.afrik-solutions.com/privacy-policy

### Connexion SSH
```bash
ssh root@157.173.104.21
# ou
ssh root@isogaz.afrik-solutions.com
```

### Déploiement manuel
```bash
# Se connecter au serveur
ssh root@157.173.104.21

# Aller dans le dossier du projet
cd /var/www/isogaz

# Pull les changements
git pull origin dev

# Installer les dépendances si nécessaire
composer install --no-dev --optimize-autoloader

# Exécuter les migrations
php artisan migrate --force

# Clear les caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Optimiser
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Production
- **À définir**
