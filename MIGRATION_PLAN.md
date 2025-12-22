# Plan de Migration - Isogaz vers Nouveau Serveur

**Date:** 17-18 Décembre 2025
**Ancien Serveur:** 38.242.215.198 (Apache + SSL configuré)
**Nouveau Serveur:** 157.173.104.21 (Nginx installé)
**Projet Ancien:** /var/www/html/isogaz
**Projet Nouveau:** /var/www/petrolex (à renommer en /var/www/isogaz)
**Domaine:** isogaz.afrik-solutions.com

---

## État d'avancement - Mis à jour le 18 Décembre 2025

- [x] 1. Configuration SSH
- [x] 2. Installation des dépendances système (Nginx, PHP, MySQL, Redis, Supervisor, Certbot)
- [x] 3. Configuration Git
- [x] 4. Clonage du projet (dans /var/www/petrolex)
- [ ] 5. Renommer le dossier petrolex → isogaz
- [ ] 6. Migration du fichier .env
- [ ] 7. Migration de la base de données
- [ ] 8. Configuration Nginx pour isogaz.afrik-solutions.com
- [ ] 9. Configuration SSL/Certbot
- [ ] 10. Configuration Supervisor (queues Laravel)
- [ ] 11. Permissions Laravel
- [x] 12. Mise à jour DNS (déjà pointé vers 157.173.104.21)
- [ ] 13. Tests et vérification complète

---

## 🔍 DÉCOUVERTES IMPORTANTES

### État actuel détecté (18 Décembre 2025):
- ✅ **Nginx** : Installé et actif sur le nouveau serveur
- ✅ **Projet cloné** : Dans `/var/www/petrolex` (branche dev à jour)
- ✅ **DNS** : Déjà pointé vers 157.173.104.21
- ⚠️ **Différence serveurs** : Ancien (Apache) ≠ Nouveau (Nginx)
- ❌ **Fichier .env** : MANQUANT sur le nouveau serveur
- ❌ **Configuration Nginx** : Pas de config pour isogaz (seulement default)
- ❌ **SSL** : Pas encore configuré
- ❌ **Base de données** : Pas encore migrée

---

## ÉTAPE 1 : Configuration SSH ✅

**Statut:** ✅ **TERMINÉ**
**Résultat:** Connexion SSH fonctionnelle sur les deux serveurs avec les clés configurées.

---

## ÉTAPE 2 : Installation des dépendances système ✅

**Statut:** ✅ **TERMINÉ**
**Résultat:**
- Nginx installé et actif
- PHP-FPM, MySQL, Redis, Supervisor installés
- Certbot installé

---

## ÉTAPE 3 : Configuration Git ✅

**Statut:** ✅ **TERMINÉ**
**Résultat:** Clés SSH configurées sur Bitbucket, projet accessible

---

## ÉTAPE 4 : Clonage du projet ✅

**Statut:** ✅ **TERMINÉ**
**Résultat:** Projet cloné dans `/var/www/petrolex` (branche dev)

---

## ÉTAPE 5 : Renommer le dossier projet ⏳

**Sur le nouveau serveur (157.173.104.21):**
```bash
cd /var/www
mv petrolex isogaz
```

**Raison:** Cohérence avec l'ancien serveur et le domaine isogaz.afrik-solutions.com

**Statut:** ⏳ **EN ATTENTE**

---

### 6.1 Récupérer le .env de l'ancien serveur
**Depuis votre machine locale:**
```bash
scp root@38.242.215.198:/var/www/html/isogaz/.env /tmp/isogaz_env_backup
```

### 6.2 Copier le .env sur le nouveau serveur
```bash
scp /tmp/isogaz_env_backup root@157.173.104.21:/var/www/isogaz/.env
```

### 6.3 Adapter le .env si nécessaire
- Vérifier les credentials de base de données
- Vérifier les URLs et chemins
- Vérifier les clés API (Orange Money, MTN, etc.)

**Statut:** ⏳ **EN ATTENTE**

---

## ÉTAPE 7 : Migration de la base de données ⏳

### 7.1 Export depuis l'ancien serveur
**Sur l'ancien serveur (38.242.215.198):**
```bash
cd /var/www/html/isogaz
cat .env | grep DB_
mysqldump -u DB_USERNAME -p DB_DATABASE > /root/isogaz_db_backup.sql
```

### 7.2 Transférer la sauvegarde
**Depuis votre machine locale:**
```bash
scp root@38.242.215.198:/root/isogaz_db_backup.sql /tmp/
scp /tmp/isogaz_db_backup.sql root@157.173.104.21:/root/
```

### 7.3 Créer la base sur le nouveau serveur
**Sur le nouveau serveur (157.173.104.21):**
```bash
mysql -u root -p
```

**Dans MySQL:**
```sql
CREATE DATABASE isogaz_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'isogaz_user'@'localhost' IDENTIFIED BY 'MOT_DE_PASSE_FORT';
GRANT ALL PRIVILEGES ON isogaz_db.* TO 'isogaz_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 7.4 Importer la base
```bash
mysql -u root -p isogaz_db < /root/isogaz_db_backup.sql
```

**Statut:** ⏳ **EN ATTENTE**

---

## ÉTAPE 8 : Configuration Nginx ⏳

**IMPORTANT:** L'ancien serveur utilise Apache, le nouveau utilise Nginx. Conversion nécessaire.

### 8.1 Config Apache existante (ancien serveur)
La configuration Apache actuelle inclut:
- HTTP (port 80) avec redirection vers HTTPS
- HTTPS (port 443) avec SSL
- WebSocket proxy pour Reverb (port 8080)
- Certificats Let's Encrypt

### 8.2 Créer la config Nginx équivalente

**Sur le nouveau serveur (157.173.104.21):**
```bash
nano /etc/nginx/sites-available/isogaz
```

**Configuration Nginx complète (sans SSL d'abord):**
```nginx
# WebSocket upgrade map
map $http_upgrade $connection_upgrade {
    default upgrade;
    '' close;
}

server {
    listen 80;
    server_name isogaz.afrik-solutions.com;
    root /var/www/isogaz/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html;
    charset utf-8;

    # Laravel routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # WebSocket Reverb Proxy
    location /app/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection $connection_upgrade;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }

    # Reverb HTTP fallback
    location /reverb/ {
        proxy_pass http://127.0.0.1:8080/;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    access_log /var/log/nginx/isogaz-access.log;
    error_log /var/log/nginx/isogaz-error.log;
}
```

### 8.3 Activer la configuration
```bash
ln -s /etc/nginx/sites-available/isogaz /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

**Statut:** ⏳ **EN ATTENTE**

---

## ÉTAPE 9 : Configuration SSL avec Certbot ⏳

### 9.1 S'assurer que le DNS est bien propagé
```bash
dig isogaz.afrik-solutions.com +short
# Doit retourner: 157.173.104.21
```

### 9.2 Obtenir le certificat SSL
**Sur le nouveau serveur (157.173.104.21):**
```bash
certbot --nginx -d isogaz.afrik-solutions.com
```

Certbot va automatiquement:
- Obtenir le certificat Let's Encrypt
- Modifier la config Nginx pour ajouter HTTPS
- Configurer la redirection HTTP → HTTPS

### 9.3 Vérifier le renouvellement automatique
```bash
certbot renew --dry-run
```

**Statut:** ⏳ **EN ATTENTE**

---

## ÉTAPE 10 : Configuration Supervisor ⏳

### 10.1 Créer la config Supervisor pour les queues

**Sur le nouveau serveur (157.173.104.21):**
```bash
nano /etc/supervisor/conf.d/isogaz-worker.conf
```

**Configuration:**
```ini
[program:isogaz-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/isogaz/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/isogaz/storage/logs/worker.log
stopwaitsecs=3600
```

### 10.2 Créer la config pour Reverb (WebSocket)

```bash
nano /etc/supervisor/conf.d/isogaz-reverb.conf
```

**Configuration:**
```ini
[program:isogaz-reverb]
command=php /var/www/isogaz/artisan reverb:start
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/isogaz/storage/logs/reverb.log
```

### 10.3 Activer Supervisor
```bash
supervisorctl reread
supervisorctl update
supervisorctl start isogaz-worker:*
supervisorctl start isogaz-reverb:*
supervisorctl status
```

**Statut:** ⏳ **EN ATTENTE**

---

## ÉTAPE 11 : Permissions Laravel ⏳

**Sur le nouveau serveur (157.173.104.21):**
```bash
cd /var/www/isogaz

# Donner les bonnes permissions
chown -R www-data:www-data /var/www/isogaz
chmod -R 755 /var/www/isogaz
chmod -R 775 /var/www/isogaz/storage
chmod -R 775 /var/www/isogaz/bootstrap/cache

# Créer le lien symbolique storage
php artisan storage:link
```

**Statut:** ⏳ **EN ATTENTE**

---

## ÉTAPE 12 : Optimisation Laravel ⏳

**Sur le nouveau serveur:**
```bash
cd /var/www/isogaz

# Installer les dépendances
composer install --no-dev --optimize-autoloader

# Optimiser Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

**Statut:** ⏳ **EN ATTENTE**

---

## ÉTAPE 13 : Tests et vérification ⏳

### 13.1 Vérifier les services
**Sur le nouveau serveur:**
```bash
systemctl status nginx
systemctl status php8.2-fpm
systemctl status mysql
systemctl status redis-server
systemctl status supervisor
supervisorctl status
```

### 13.2 Vérifier la propagation DNS
```bash
dig isogaz.afrik-solutions.com +short
# Doit retourner: 157.173.104.21
```

### 13.3 Tester l'application en HTTP d'abord
```bash
curl -I http://isogaz.afrik-solutions.com
# Vérifier les logs
tail -f /var/www/isogaz/storage/logs/laravel.log
```

### 13.4 Tester HTTPS après configuration SSL
```bash
curl -I https://isogaz.afrik-solutions.com
```

### 13.5 Tests fonctionnels depuis le navigateur
- ✅ Accès à https://isogaz.afrik-solutions.com
- ✅ Authentification utilisateurs
- ✅ Création de commandes
- ✅ Paiements (Orange Money, MTN)
- ✅ WebSocket/Reverb (notifications temps réel)
- ✅ Gestion des livreurs
- ✅ API endpoints

**Statut:** ⏳ **EN ATTENTE**

---

## 📝 Notes importantes

### Checklist finale:
- [ ] Sauvegarder les mots de passe de base de données
- [ ] Vérifier les cron jobs sur l'ancien serveur (`crontab -l`)
- [ ] Migrer les cron jobs si nécessaire
- [ ] Vérifier les variables d'environnement (.env)
- [ ] Tester tous les endpoints API
- [ ] Vérifier les webhooks (Orange Money, MTN)
- [ ] Tester les notifications push
- [ ] Vérifier les sauvegardes automatiques
- [ ] Garder l'ancien serveur actif pendant 7 jours comme backup
- [ ] Monitorer les logs pendant 48h

### Points critiques à vérifier:
- ✅ Base de données migrée complètement
- ✅ Tous les fichiers storage copiés
- ✅ Certificats SSL valides
- ✅ WebSocket fonctionnel (Reverb)
- ✅ Queues actives
- ✅ Paiements fonctionnels (CRITICAL)
- ✅ Aucune erreur 500 dans les logs

---

## 🚀 ORDRE D'EXÉCUTION RECOMMANDÉ

Pour une migration sans accroc, suivre cet ordre précis:

1. **ÉTAPE 5** - Renommer petrolex → isogaz
2. **ÉTAPE 6** - Copier le fichier .env
3. **ÉTAPE 7** - Migrer la base de données
4. **ÉTAPE 12** - Installer dépendances Composer et optimiser
5. **ÉTAPE 8** - Configurer Nginx
6. **ÉTAPE 11** - Ajuster les permissions
7. **ÉTAPE 9** - Configurer SSL avec Certbot
8. **ÉTAPE 10** - Configurer Supervisor
9. **ÉTAPE 13** - Tests complets

**IMPORTANT:** Ne pas activer le site Nginx avant d'avoir copié le .env et migré la base de données !

---

## 🔧 Commandes utiles pour le monitoring

### Vérifier les logs Nginx
```bash
tail -f /var/log/nginx/error.log
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/isogaz-error.log
tail -f /var/log/nginx/isogaz-access.log
```

### Vérifier les logs PHP
```bash
tail -f /var/log/php8.2-fpm.log
```

### Vérifier les logs Laravel
```bash
tail -f /var/www/isogaz/storage/logs/laravel.log
tail -f /var/www/isogaz/storage/logs/worker.log
tail -f /var/www/isogaz/storage/logs/reverb.log
```

### Redémarrer les services
```bash
systemctl restart nginx
systemctl restart php8.2-fpm
systemctl restart mysql
systemctl restart redis-server
supervisorctl restart all
```

### Vérifier l'état des services
```bash
systemctl status nginx php8.2-fpm mysql redis-server supervisor
supervisorctl status
```

### Commandes de dépannage
```bash
# Tester la config Nginx
nginx -t

# Vérifier les connexions MySQL
mysql -u isogaz_user -p -e "SHOW DATABASES;"

# Vérifier Redis
redis-cli ping

# Vérifier les queues
php artisan queue:work --once

# Nettoyer les caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## ✅ MIGRATION COMPLÉTÉE

Une fois toutes les étapes terminées et les tests validés:
- [ ] Marquer cette migration comme **TERMINÉE**
- [ ] Documenter les changements dans le CHANGELOG
- [ ] Notifier l'équipe
- [ ] Planifier la désactivation de l'ancien serveur (J+7)
