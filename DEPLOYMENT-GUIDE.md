# 🚀 Guide de Déploiement Production - Système de Suivi Livraison

## 📋 Configuration pour `mondomaine.com`

### 1. Configuration Laravel Reverb

#### `/config/reverb.php`
```php
<?php

return [
    'default' => env('REVERB_SERVER', 'laravel'),

    'servers' => [
        'laravel' => [
            'host' => env('REVERB_HOST', '0.0.0.0'),
            'port' => env('REVERB_PORT', 8080),
            'hostname' => env('REVERB_HOSTNAME', 'mondomaine.com'),
            'options' => [
                'tls' => [
                    'local_cert' => env('REVERB_SSL_CERT'),
                    'local_pk' => env('REVERB_SSL_KEY'),
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ],
            'max_request_size' => 10_000,
            'scaling' => [
                'enabled' => env('REVERB_SCALING_ENABLED', false),
                'channel' => env('REVERB_SCALING_CHANNEL', 'reverb'),
                'server' => [
                    'url' => env('REDIS_URL'),
                    'host' => env('REDIS_HOST', '127.0.0.1'),
                    'port' => env('REDIS_PORT', '6379'),
                    'username' => env('REDIS_USERNAME'),
                    'password' => env('REDIS_PASSWORD'),
                    'database' => env('REDIS_DB', '0'),
                ],
            ],
            'pulse_ingest_interval' => 15,
            'telescope_ingest_interval' => 15,
        ],
    ],

    'apps' => [
        [
            'app_id' => env('REVERB_APP_ID', 'app'),
            'app_key' => env('REVERB_APP_KEY', 'petro-key-12345'),
            'app_secret' => env('REVERB_APP_SECRET', 'petro-secret-67890'),
            'options' => [
                'host' => env('REVERB_APP_HOST'),
                'port' => env('REVERB_APP_PORT', 8080),
                'scheme' => env('REVERB_APP_SCHEME', 'http'),
            ],
            'allowed_origins' => [
                'https://mondomaine.com',
                'http://mondomaine.com',
                'https://*.mondomaine.com',
            ],
            'ping_interval' => env('REVERB_APP_PING_INTERVAL', 60),
            'max_message_size' => env('REVERB_APP_MAX_MESSAGE_SIZE', 10000),
        ],
    ],
];
```

#### `/config/broadcasting.php` - Section Reverb
```php
'reverb' => [
    'driver' => 'reverb',
    'key' => env('REVERB_APP_KEY'),
    'secret' => env('REVERB_APP_SECRET'),
    'app_id' => env('REVERB_APP_ID'),
    'options' => [
        'host' => env('REVERB_HOST', '127.0.0.1'),
        'port' => env('REVERB_PORT', 8080),
        'scheme' => env('REVERB_SCHEME', 'http'),
    ],
    'client_options' => [
        // Options Guzzle
    ],
],
```

### 2. Variables d'Environnement (`.env`)

```env
# === REVERB WEBSOCKET CONFIGURATION ===
BROADCAST_CONNECTION=reverb

# Reverb Server Configuration
REVERB_SERVER=laravel
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_HOSTNAME=mondomaine.com
REVERB_SCHEME=https

# Reverb App Configuration  
REVERB_APP_ID=petrolex-app
REVERB_APP_KEY=petro-key-12345
REVERB_APP_SECRET=petro-secret-67890
REVERB_APP_HOST=mondomaine.com
REVERB_APP_PORT=8080
REVERB_APP_SCHEME=https

# SSL Configuration (for production)
REVERB_SSL_CERT=/path/to/ssl/cert.pem
REVERB_SSL_KEY=/path/to/ssl/private.key

# Scaling with Redis (optional)
REVERB_SCALING_ENABLED=true
REVERB_SCALING_CHANNEL=reverb

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=your_redis_password
REDIS_DB=0
```

### 3. Configuration Shared Config JavaScript

#### Mettre à jour `public/test/delivery-tracking/shared-config.js`
```javascript
// 🌍 CONFIGURATION PARTAGÉE - PRODUCTION
const SHARED_CONFIG = {
    WEBSOCKET: {
        ENABLED: true,
        HOST: 'mondomaine.com',
        PORT: 8080,
        FORCE_TLS: true,
        APP_KEY: 'petro-key-12345',
        ENABLED_TRANSPORTS: ['websocket', 'polling']
    },
    
    API: {
        BASE_URL: 'https://mondomaine.com/api',
        TIMEOUT: 10000
    },

    DELIVERY_BASE_URL: 'https://mondomaine.com/api/delivery',
    CUSTOMER_BASE_URL: 'https://mondomaine.com/api/customer',
    
    // Configuration par environnement
    ENVIRONMENT: 'production'
};

// Auto-detect development vs production
if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    SHARED_CONFIG.WEBSOCKET.HOST = '127.0.0.1';
    SHARED_CONFIG.WEBSOCKET.FORCE_TLS = false;
    SHARED_CONFIG.API.BASE_URL = 'http://localhost:8000/api';
    SHARED_CONFIG.DELIVERY_BASE_URL = 'http://localhost:8000/api/delivery';
    SHARED_CONFIG.CUSTOMER_BASE_URL = 'http://localhost:8000/api/customer';
    SHARED_CONFIG.ENVIRONMENT = 'development';
}

// Export pour Customer et Delivery
const CUSTOMER_CONFIG = {
    ...SHARED_CONFIG,
    WEBSOCKET: {
        ...SHARED_CONFIG.WEBSOCKET,
        APP_KEY: SHARED_CONFIG.WEBSOCKET.APP_KEY
    }
};

const DELIVERY_CONFIG = {
    ...SHARED_CONFIG,
    WEBSOCKET: {
        ...SHARED_CONFIG.WEBSOCKET,
        APP_KEY: SHARED_CONFIG.WEBSOCKET.APP_KEY
    }
};
```

### 4. Configuration Supervisor

#### Créer `/etc/supervisor/conf.d/laravel-reverb.conf`
```ini
[program:laravel-reverb]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/petrolex/artisan reverb:start --host=0.0.0.0 --port=8080 --hostname=mondomaine.com
directory=/path/to/your/petrolex
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/supervisor/laravel-reverb.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
environment=LARAVEL_ENV="production"
```

#### Commandes Supervisor
```bash
# Recharger la configuration
sudo supervisorctl reread
sudo supervisorctl update

# Démarrer/Arrêter Reverb
sudo supervisorctl start laravel-reverb:*
sudo supervisorctl stop laravel-reverb:*
sudo supervisorctl restart laravel-reverb:*

# Vérifier le statut
sudo supervisorctl status laravel-reverb:*

# Voir les logs
sudo supervisorctl tail -f laravel-reverb:00
```

### 5. Configuration Nginx

#### Configuration proxy pour WebSocket
```nginx
server {
    listen 80;
    listen 443 ssl http2;
    server_name mondomaine.com;
    
    # SSL Configuration
    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/private.key;
    
    # Laravel App
    location / {
        try_files $uri $uri/ /index.php?$query_string;
        
        # PHP-FPM Configuration
        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
            include fastcgi_params;
        }
    }
    
    # WebSocket Reverb Proxy
    location /app/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
        proxy_read_timeout 86400;
    }
    
    # Static files
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### 6. Configuration Firewall

```bash
# Ouvrir les ports nécessaires
sudo ufw allow 8080/tcp comment "Laravel Reverb WebSocket"
sudo ufw allow 443/tcp comment "HTTPS"
sudo ufw allow 80/tcp comment "HTTP"

# Vérifier les règles
sudo ufw status
```

### 7. Configuration SSL/TLS (Recommandé)

#### Obtenir un certificat SSL avec Certbot
```bash
# Installer Certbot
sudo apt install certbot python3-certbot-nginx

# Obtenir le certificat
sudo certbot --nginx -d mondomaine.com

# Renouvellement automatique
sudo systemctl enable certbot.timer
```

### 8. Scripts de Déploiement

#### `/deploy.sh`
```bash
#!/bin/bash

echo "🚀 Déploiement Production Petrolex Tracking System"

# Mise à jour du code
git pull origin main

# Installation/mise à jour des dépendances
composer install --no-dev --optimize-autoloader
npm ci --production

# Mise à jour de la base de données
php artisan migrate --force

# Cache et optimisations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Redémarrage des services
sudo supervisorctl restart laravel-reverb:*
sudo systemctl reload nginx

echo "✅ Déploiement terminé avec succès"
```

#### `/monitoring.sh` - Script de monitoring
```bash
#!/bin/bash

# Vérifier si Reverb fonctionne
if ! supervisorctl status laravel-reverb:00 | grep -q RUNNING; then
    echo "❌ Laravel Reverb n'est pas en cours d'exécution"
    sudo supervisorctl start laravel-reverb:*
    
    # Envoyer une alerte (optionnel)
    # curl -X POST -H 'Content-type: application/json' \
    #   --data '{"text":"🚨 Laravel Reverb redémarré sur mondomaine.com"}' \
    #   YOUR_SLACK_WEBHOOK_URL
fi

# Vérifier la connectivité WebSocket
if ! timeout 5 bash -c "</dev/tcp/127.0.0.1/8080"; then
    echo "❌ Port WebSocket 8080 non accessible"
    sudo supervisorctl restart laravel-reverb:*
fi

echo "✅ Monitoring OK"
```

### 9. Tests de Validation Production

#### Test de connectivité WebSocket
```bash
# Tester la connexion WebSocket
curl -i -N -H "Connection: Upgrade" \
     -H "Upgrade: websocket" \
     -H "Sec-WebSocket-Key: x3JJHMbDL1EzLkh9GBhXDw==" \
     -H "Sec-WebSocket-Version: 13" \
     https://mondomaine.com:8080/app/petro-key-12345
```

#### Page de test accessible via
- `https://mondomaine.com/test-websocket-sender.html`
- `https://mondomaine.com/test-websocket-receiver.html`

### 10. Maintenance et Logs

#### Logs importants à surveiller
```bash
# Laravel Logs
tail -f /path/to/petrolex/storage/logs/laravel.log

# Reverb Logs via Supervisor
sudo tail -f /var/log/supervisor/laravel-reverb.log

# Nginx Logs
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/nginx/error.log
```

#### Commandes de maintenance
```bash
# Nettoyer les logs Laravel
php artisan log:clear

# Vérifier la santé du système
php artisan health:check

# Redémarrer tous les services
sudo supervisorctl restart all
sudo systemctl restart nginx
```

## ✅ Checklist de Déploiement

- [ ] Configuration `.env` mise à jour
- [ ] Certificats SSL installés
- [ ] Supervisor configuré et démarré
- [ ] Nginx configuré avec proxy WebSocket
- [ ] Firewall configuré
- [ ] Tests WebSocket fonctionnels
- [ ] Logs accessibles et configurés
- [ ] Scripts de monitoring en place
- [ ] Sauvegarde des données effectuée

**🎉 Votre système de tracking est maintenant prêt pour la production !**