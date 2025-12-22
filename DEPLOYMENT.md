# Déploiement Isogaz - Guide Production

## 📋 Informations Serveur

**Serveur:** 157.173.104.21
**Domaine:** https://isogaz.afrik-solutions.com
**Chemin:** `/var/www/isogaz`
**Utilisateur:** `www-data`
**PHP:** 8.3 (CLI par défaut)
**Base de données:** MySQL (petrolex_prod)

## 🚀 Déploiement Rapide

```bash
# Se connecter au serveur
ssh root@157.173.104.21

# Aller dans le répertoire
cd /var/www/isogaz

# Mettre à jour le code
git pull origin main

# Installer les dépendances
composer install --no-dev --optimize-autoloader

# Migrations (si nécessaires)
php artisan migrate --force

# Optimiser Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Redémarrer les services
supervisorctl restart isogaz-worker:*
supervisorctl restart isogaz-reverb:*
systemctl restart php8.3-fpm
systemctl reload nginx
```

## ⚙️ Architecture

### Stack Technique
- **Web Server:** Nginx
- **SSL/TLS:** Let's Encrypt (Certbot)
- **PHP-FPM:** 8.3
- **WebSocket:** Laravel Reverb (port 8080)
- **Queue Workers:** 2 workers via Supervisor
- **Cache:** Redis

### Services Actifs
```bash
# Vérifier les services
systemctl status nginx
systemctl status php8.3-fpm
supervisorctl status
```

## 🔧 Configuration Importante

### 1. Nginx
**Fichier:** `/etc/nginx/sites-available/isogaz`
- Proxy WebSocket vers port 8080 (Reverb)
- SSL automatique via Certbot
- Timeout: 60s

### 2. Supervisor
**Workers:** `/etc/supervisor/conf.d/isogaz-worker.conf`
- 2 processus queue:work
- Auto-restart activé

**Reverb:** `/etc/supervisor/conf.d/isogaz-reverb.conf`
- WebSocket server Laravel Reverb
- Port 8080

### 3. PHP CLI
**Version par défaut:** PHP 8.3
```bash
# Si PHP 8.5+ est installé, forcer PHP 8.3 pour CLI
update-alternatives --set php /usr/bin/php8.3
```

### 4. Permissions
```bash
# Corriger les permissions si nécessaire
chown -R www-data:www-data /var/www/isogaz
chmod -R 755 /var/www/isogaz
chmod -R 775 /var/www/isogaz/storage
chmod -R 775 /var/www/isogaz/bootstrap/cache
```

## 🔍 Vérification Post-Déploiement

```bash
# 1. Vérifier l'API
curl https://isogaz.afrik-solutions.com/api/health

# 2. Vérifier SSL
curl -I https://isogaz.afrik-solutions.com

# 3. Vérifier les workers
supervisorctl status isogaz-worker:*

# 4. Vérifier Reverb (WebSocket)
supervisorctl status isogaz-reverb:*

# 5. Logs en temps réel
tail -f /var/www/isogaz/storage/logs/laravel-$(date +%Y-%m-%d).log
```

## 📱 Configuration Mobile

**URL de base API:** `https://isogaz.afrik-solutions.com/api`

**Endpoints principaux:**
- Health check: `GET /api/health`
- Auth: `POST /api/auth/login`
- Orders: `POST /api/orders`
- Payment: `POST /api/orders/{id}/payment`

**WebSocket:**
- URL: `wss://isogaz.afrik-solutions.com/app`
- Port: 443 (via proxy Nginx → 8080)

## 🔐 Paiements Mobile Money

### MTN Money
✅ **Fonctionnel**
Les paiements sont vérifiés automatiquement toutes les 10 secondes (max 3 minutes).
**Prérequis:** Queue workers actifs

### Orange Money
⚠️ **Timeout API**
Problème d'accès à l'API Orange (`api-s1.orange.cm`).
À vérifier: firewall, credentials API, environnement.

## 🐛 Dépannage

### Les paiements restent en "pending"
```bash
# Vérifier que les workers tournent
supervisorctl status isogaz-worker:*

# Si stopped, les démarrer
supervisorctl start isogaz-worker:*

# Vérifier PHP CLI a les bons drivers
php -m | grep -i mysql
```

### WebSocket ne fonctionne pas
```bash
# Vérifier Reverb
supervisorctl status isogaz-reverb:*
supervisorctl tail isogaz-reverb:*

# Redémarrer
supervisorctl restart isogaz-reverb:*
```

### Erreurs 500
```bash
# Logs Laravel
tail -100 /var/www/isogaz/storage/logs/laravel-$(date +%Y-%m-%d).log

# Logs Nginx
tail -100 /var/log/nginx/error.log

# Permissions storage
chmod -R 775 /var/www/isogaz/storage
```

## 📊 Monitoring

```bash
# Espace disque
df -h

# RAM
free -h

# Processus PHP
ps aux | grep php

# Connexions base de données
mysql -u petrolex_user -p -e "SHOW PROCESSLIST"
```

## 🔄 Rollback Rapide

```bash
cd /var/www/isogaz
git log --oneline -5  # Voir les derniers commits
git reset --hard <commit-id>
composer install --no-dev --optimize-autoloader
php artisan config:clear
supervisorctl restart all
systemctl restart php8.3-fpm
```

---

**Dernière mise à jour:** 18 décembre 2025
**Version serveur:** Production
**Contact:** devops@afrik-solutions.com
