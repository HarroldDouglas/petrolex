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

## Production — isogaz.net ✅ DÉPLOYÉ

- **Domaine** : isogaz.net (DNS configuré, SSL actif)
- **IP** : 158.220.121.100
- **Hébergeur** : Contabo VPS
- **OS** : Ubuntu 22.04.5 LTS
- **CPU** : 6 vCPU AMD EPYC | **RAM** : 11 Go | **Disque** : 194 Go SSD
- **User SSH** : isogaz-ptlx
- **Mot de passe SSH** : IsoPTX@527.
- **Port SSH** : 5522
- **Chemin projet** : /var/www/petrolex
- **URL** : https://isogaz.net
- **URL API** : https://isogaz.net/api
- **URL Health** : https://isogaz.net/api/health

### Connexion SSH (Production)
```bash
ssh -p 5522 isogaz-ptlx@158.220.121.100
# Mot de passe : IsoPTX@527.
# Sudo disponible avec le même mot de passe
```

### Stack installée
| Outil | Version |
|---|---|
| PHP | 8.2.30 + FPM |
| Composer | 2.9.5 |
| MySQL | 8.0.45 |
| Redis | 7.x |
| Nginx | 1.18.0 |
| Node.js | 20.20.0 |
| NPM | 10.8.2 |
| Supervisor | ✅ actif |
| Certbot | 1.21.0 (SSL prêt) |

### Base de données
- **Host** : 127.0.0.1:3306
- **DB** : petrolex
- **User** : petrolex_user
- **Password** : Petrolex@2026!

### SMTP
- **Host** : scie.o2switch.net
- **Port** : 465 (SSL)
- **User** : info.isogaz@isogaz.net
- **Password** : Infiso@2026

### Compte Admin Application
- **Email** : admin@isogaz.net
- **Mot de passe** : Admin@Isogaz2026!
- **Rôle** : super_admin (51 permissions)

### Supervisor (Services continus)
```bash
# Voir le statut
echo 'IsoPTX@527.' | sudo -S supervisorctl status

# Redémarrer tout
echo 'IsoPTX@527.' | sudo -S supervisorctl restart all
```
Processus gérés :
- `petrolex-worker:petrolex-worker_00` — Queue worker
- `petrolex-worker:petrolex-worker_01` — Queue worker
- `petrolex-reverb` — WebSocket Reverb (port 8080)

### Logs
```bash
# Logs Laravel
tail -f /var/www/petrolex/storage/logs/laravel-$(date +%Y-%m-%d).log

# Logs Queue worker
tail -f /var/www/petrolex/storage/logs/worker.log

# Logs Reverb WebSocket
tail -f /var/www/petrolex/storage/logs/reverb.log

# Logs Nginx
sudo tail -f /var/log/nginx/error.log
```

### Déploiement rapide (mises à jour)
```bash
ssh -p 5522 isogaz-ptlx@158.220.121.100
echo 'IsoPTX@527.' | sudo -S bash -c "
cd /var/www/petrolex
git pull origin main
composer install --no-dev --optimize-autoloader
php8.2 artisan migrate --force
php8.2 artisan config:cache && php8.2 artisan route:cache && php8.2 artisan view:cache
chown -R www-data:www-data storage bootstrap/cache
supervisorctl restart all
"
```

### SSL — Let's Encrypt ✅ ACTIF
- **Certificat** : `/etc/letsencrypt/live/isogaz.net/fullchain.pem`
- **Expiration** : 2026-06-07
- **Renouvellement** : Automatique via certbot.timer
- **Domaines** : isogaz.net + www.isogaz.net

### Proxy WebSocket Nginx ✅ CONFIGURÉ
- `location /app` et `/apps` → proxy vers `127.0.0.1:8080` (Reverb)

### ⚠️ Tâches restantes
1. **Centre de distribution** : Créer via admin → les produits peuvent ensuite être liés
2. **Clés paiement** : Renseigner MTN MoMo + Orange Money dans `.env` (`/var/www/petrolex/.env`)

