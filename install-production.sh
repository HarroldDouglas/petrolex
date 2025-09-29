#!/bin/bash

# 🚀 Script d'installation automatique - Production Petrolex Tracking
# Usage: ./install-production.sh mondomaine.com

set -e

DOMAIN=${1:-"mondomaine.com"}
PROJECT_PATH="/var/www/$DOMAIN"
CURRENT_DIR=$(pwd)

echo "🚀 Installation Production Petrolex Tracking System"
echo "📍 Domaine: $DOMAIN"
echo "📂 Chemin: $PROJECT_PATH"

# Vérification des permissions
if [[ $EUID -ne 0 ]]; then
   echo "❌ Ce script doit être exécuté en tant que root"
   exit 1
fi

echo "📦 1. Installation des dépendances système..."
apt update
apt install -y supervisor nginx redis-server certbot python3-certbot-nginx

echo "🔧 2. Configuration Supervisor pour Reverb..."
# Copier et adapter la configuration Supervisor
sed "s|/var/www/mondomaine.com|$PROJECT_PATH|g" "$CURRENT_DIR/supervisor-reverb.conf" > /etc/supervisor/conf.d/laravel-reverb.conf

# Recharger Supervisor
supervisorctl reread
supervisorctl update

echo "🌍 3. Configuration Nginx..."
cat > /etc/nginx/sites-available/$DOMAIN << EOF
server {
    listen 80;
    server_name $DOMAIN;
    root $PROJECT_PATH/public;
    index index.php index.html;

    # Gestion des erreurs
    error_log /var/log/nginx/${DOMAIN}_error.log;
    access_log /var/log/nginx/${DOMAIN}_access.log;

    # PHP Laravel
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Proxy WebSocket Reverb
    location /app/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_cache_bypass \$http_upgrade;
        proxy_read_timeout 86400;
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
    }

    # Cache des fichiers statiques
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        add_header X-Content-Type-Options nosniff;
    }

    # Sécurité
    location ~ /\.ht {
        deny all;
    }
    
    location ~ /\.env {
        deny all;
    }
}
EOF

# Activer le site
ln -sf /etc/nginx/sites-available/$DOMAIN /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx

echo "🔒 4. Configuration SSL avec Certbot..."
certbot --nginx -d $DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN

echo "🔥 5. Configuration du Firewall..."
ufw allow 'Nginx Full'
ufw allow 8080/tcp
ufw --force enable

echo "📝 6. Mise à jour de la configuration shared-config.js..."
if [ -f "$PROJECT_PATH/public/test/delivery-tracking/shared-config.js" ]; then
    # Backup de l'ancien fichier
    cp "$PROJECT_PATH/public/test/delivery-tracking/shared-config.js" "$PROJECT_PATH/public/test/delivery-tracking/shared-config.js.backup"
    
    # Remplacer les configurations de développement
    sed -i "s/127\.0\.0\.1/$DOMAIN/g" "$PROJECT_PATH/public/test/delivery-tracking/shared-config.js"
    sed -i "s/localhost/$DOMAIN/g" "$PROJECT_PATH/public/test/delivery-tracking/shared-config.js"
    sed -i "s/http:\/\//https:\/\//g" "$PROJECT_PATH/public/test/delivery-tracking/shared-config.js"
fi

echo "🚀 7. Démarrage des services..."
supervisorctl start laravel-reverb:*

echo "🧪 8. Tests de connectivité..."
sleep 5

# Test du port WebSocket
if timeout 5 bash -c "</dev/tcp/127.0.0.1/8080"; then
    echo "✅ Port WebSocket 8080 accessible"
else
    echo "❌ Port WebSocket 8080 non accessible"
    supervisorctl status laravel-reverb:*
fi

# Test de la configuration Nginx
if nginx -t 2>/dev/null; then
    echo "✅ Configuration Nginx valide"
else
    echo "❌ Erreur dans la configuration Nginx"
fi

echo "📊 9. Création du script de monitoring..."
cat > /usr/local/bin/petrolex-monitor.sh << 'EOF'
#!/bin/bash
# Script de monitoring Petrolex

# Vérifier Reverb
if ! supervisorctl status laravel-reverb:00 | grep -q RUNNING; then
    echo "$(date): ❌ Laravel Reverb redémarré" >> /var/log/petrolex-monitor.log
    supervisorctl start laravel-reverb:*
fi

# Vérifier la connectivité WebSocket
if ! timeout 3 bash -c "</dev/tcp/127.0.0.1/8080" 2>/dev/null; then
    echo "$(date): ❌ Port 8080 inaccessible, redémarrage Reverb" >> /var/log/petrolex-monitor.log
    supervisorctl restart laravel-reverb:*
fi

# Vérifier l'espace disque des logs
LOG_SIZE=$(du -sm /var/log/supervisor/laravel-reverb.log 2>/dev/null | cut -f1)
if [ "$LOG_SIZE" -gt 100 ]; then
    echo "$(date): 🧹 Rotation des logs Reverb ($LOG_SIZE MB)" >> /var/log/petrolex-monitor.log
    supervisorctl restart laravel-reverb:*
fi
EOF

chmod +x /usr/local/bin/petrolex-monitor.sh

# Ajouter au cron toutes les 5 minutes
(crontab -l 2>/dev/null; echo "*/5 * * * * /usr/local/bin/petrolex-monitor.sh") | crontab -

echo "📋 10. Résumé de l'installation:"
echo "================================================"
echo "🌍 Domaine: https://$DOMAIN"
echo "🔧 Configuration Supervisor: /etc/supervisor/conf.d/laravel-reverb.conf"
echo "🌐 Configuration Nginx: /etc/nginx/sites-available/$DOMAIN"
echo "📊 Logs Reverb: /var/log/supervisor/laravel-reverb.log"
echo "🔍 Monitoring: /usr/local/bin/petrolex-monitor.sh (cron 5min)"
echo "================================================"

echo "✅ Installation terminée avec succès!"
echo ""
echo "🧪 Tests recommandés:"
echo "1. Accéder à https://$DOMAIN"
echo "2. Tester WebSocket: https://$DOMAIN/test-websocket-sender.html"
echo "3. Vérifier les logs: sudo tail -f /var/log/supervisor/laravel-reverb.log"
echo ""
echo "🔧 Commandes utiles:"
echo "- Status Reverb: sudo supervisorctl status laravel-reverb:*"
echo "- Redémarrer Reverb: sudo supervisorctl restart laravel-reverb:*"
echo "- Logs monitoring: sudo tail -f /var/log/petrolex-monitor.log"