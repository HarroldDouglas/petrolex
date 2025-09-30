#!/bin/bash

# 🚀 Script de Déploiement Staging - Petrolex Tracking System
# Usage: ./deploy-staging.sh [--dry-run]

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Lire la configuration depuis votre .env actuel
if [[ -f ".env" ]]; then
    DOMAIN=$(grep "^APP_URL=" .env | cut -d'=' -f2 | sed 's|https://||' | sed 's|http://||')
    GOOGLE_MAPS_KEY=$(grep "^GOOGLE_MAPS_KEY=" .env | cut -d'=' -f2)
    APP_NAME=$(grep "^APP_NAME=" .env | cut -d'=' -f2)
else
    print_error "Fichier .env introuvable"
    exit 1
fi

# Parse arguments
DRY_RUN=false
for arg in "$@"; do
    if [[ "$arg" == "--dry-run" ]]; then
        DRY_RUN=true
    fi
done

echo "🚀 Déploiement Staging - Petrolex Tracking"
echo "=========================================="
echo "📍 Domaine: $DOMAIN"
echo "🗺️ Google Maps Key: ${GOOGLE_MAPS_KEY:0:20}..."
echo "🔍 Mode: $([ "$DRY_RUN" = true ] && echo "DRY RUN" || echo "EXECUTION")"
echo ""

# Vérification des prérequis
print_status "Vérification des prérequis..."

# Vérifier que nous sommes dans le bon répertoire
if [[ ! -f "artisan" ]] || [[ ! -f "composer.json" ]]; then
    print_error "Ce script doit être exécuté depuis la racine du projet Laravel"
    exit 1
fi

print_success "Prérequis validés"

# Backup des fichiers de configuration existants
print_status "Sauvegarde des configurations existantes..."

BACKUP_DIR="backup-$(date +%Y%m%d_%H%M%S)"
if [[ "$DRY_RUN" = false ]]; then
    mkdir -p "$BACKUP_DIR"
    
    # Backup des fichiers de config
    cp .env "$BACKUP_DIR/.env.backup" 2>/dev/null || true
    cp public/test/delivery-tracking/shared-config.js "$BACKUP_DIR/shared-config.js.backup" 2>/dev/null || true
    
    print_success "Sauvegarde créée dans $BACKUP_DIR/"
fi

# 1. Mise à jour automatique du .env avec variables WebSocket
print_status "Mise à jour du .env avec variables WebSocket..."

# Variables WebSocket à ajouter/mettre à jour
ENV_WEBSOCKET_VARS=(
    "BROADCAST_CONNECTION=reverb"
    "REVERB_SERVER=reverb"
    "REVERB_SERVER_HOST=0.0.0.0"
    "REVERB_SERVER_PORT=8080"
    "REVERB_HOST=$DOMAIN"
    "REVERB_PORT=8080"
    "REVERB_SCHEME=https"
    "REVERB_APP_ID=petrolex-app"
    "REVERB_APP_KEY=petro-key-12345"
    "REVERB_APP_SECRET=petro-secret-67890"
    "REVERB_SCALING_ENABLED=true"
    "REVERB_SCALING_CHANNEL=reverb"
)

if [[ "$DRY_RUN" = false ]]; then
    for update in "${ENV_WEBSOCKET_VARS[@]}"; do
        key=$(echo $update | cut -d'=' -f1)
        value=$(echo $update | cut -d'=' -f2-)
        
        if grep -q "^$key=" .env; then
            # Remplacer la ligne existante
            sed -i "s|^$key=.*|$update|" .env
            print_status "Mis à jour: $key"
        else
            # Ajouter la nouvelle ligne
            echo "$update" >> .env
            print_status "Ajouté: $key"
        fi
    done
    print_success "Variables WebSocket ajoutées au .env"
else
    echo "DRY RUN - Variables WebSocket qui seraient ajoutées au .env:"
    for update in "${ENV_WEBSOCKET_VARS[@]}"; do
        echo "  - $update"
    done
fi

# 2. Mise à jour du shared-config.js
print_status "Mise à jour de la configuration JavaScript..."

SHARED_CONFIG_FILE="public/test/delivery-tracking/shared-config.js"

if [[ "$DRY_RUN" = false ]]; then
    if [[ -f "$SHARED_CONFIG_FILE" ]]; then
        # Créer une nouvelle version du shared-config.js
        cat > "$SHARED_CONFIG_FILE" << EOF
// 🌍 CONFIGURATION PARTAGÉE - STAGING
const SHARED_CONFIG = {
    WEBSOCKET: {
        ENABLED: true,
        HOST: '$DOMAIN',
        PORT: 8080,
        FORCE_TLS: true,
        APP_KEY: 'petro-key-12345',
        ENABLED_TRANSPORTS: ['websocket', 'polling']
    },
    
    API: {
        BASE_URL: 'https://$DOMAIN/api',
        TIMEOUT: 10000
    },

    GOOGLE_MAPS: {
        API_KEY: '$GOOGLE_MAPS_KEY',
        DEFAULT_CENTER: { lat: 3.848, lng: 11.502 },
        DEFAULT_ZOOM: 12
    },
    
    // Configuration par environnement
    ENVIRONMENT: 'staging'
};

// Auto-detect development vs production
if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    SHARED_CONFIG.WEBSOCKET.HOST = '127.0.0.1';
    SHARED_CONFIG.WEBSOCKET.FORCE_TLS = false;
    SHARED_CONFIG.API.BASE_URL = 'http://localhost:8000/api';
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
EOF
        print_success "Configuration JavaScript mise à jour"
    else
        print_warning "Fichier $SHARED_CONFIG_FILE non trouvé"
    fi
else
    echo "DRY RUN - Fichier shared-config.js qui serait créé avec:"
    echo "  - API_BASE_URL: https://$DOMAIN/api"
    echo "  - WS_HOST: $DOMAIN"
    echo "  - GOOGLE_MAPS_API_KEY: ${GOOGLE_MAPS_KEY:0:20}..."
fi

# 3. Mise à jour des autres fichiers de configuration
print_status "Recherche et mise à jour des autres fichiers de configuration..."

CONFIG_FILES=(
    "public/test/delivery-tracking/delivery/js/config.js"
    "public/test/delivery-tracking/client/js/components/login-form.js"
    "public/test/delivery-tracking/client/js/reverb-client.js"
)

for file in "${CONFIG_FILES[@]}"; do
    if [[ -f "$file" ]]; then
        if [[ "$DRY_RUN" = false ]]; then
            # Remplacer localhost/127.0.0.1 par le domaine de staging
            sed -i "s|127\.0\.0\.1:8001|$DOMAIN|g" "$file"
            sed -i "s|localhost:8001|$DOMAIN|g" "$file"
            sed -i "s|127\.0\.0\.1:8080|$DOMAIN:8080|g" "$file"
            sed -i "s|localhost:8080|$DOMAIN:8080|g" "$file"
            sed -i "s|ws://|wss://|g" "$file"
            print_status "Mis à jour: $file"
        else
            echo "DRY RUN - Fichier qui serait modifié: $file"
        fi
    fi
done

# 4. Vérification des fichiers de configuration Laravel
print_status "Vérification des fichiers de configuration Laravel..."

if [[ "$DRY_RUN" = false ]]; then
    print_status "config/reverb.php existe déjà - aucune modification nécessaire"
    print_status "config/broadcasting.php existe déjà avec section reverb - aucune modification nécessaire"
else
    echo "DRY RUN - Configurations Laravel existantes - aucune modification nécessaire"
fi

# 5. Installation/Mise à jour des dépendances
print_status "Installation des dépendances..."

if [[ "$DRY_RUN" = false ]]; then
    # Installer les dépendances PHP
    composer install --optimize-autoloader
    
    print_success "Dépendances installées"
else
    echo "DRY RUN - Commandes qui seraient exécutées:"
    echo "  - composer install --optimize-autoloader"
fi

# 5. Configuration Laravel
print_status "Configuration Laravel..."

if [[ "$DRY_RUN" = false ]]; then
    # Publier le service provider Reverb si nécessaire
    php artisan vendor:publish --provider="Laravel\\Reverb\\ReverbServiceProvider" --force
    
    # Nettoyer et recacher la configuration
    php artisan config:clear
    php artisan config:cache
    
    # Exécuter les migrations
    php artisan migrate
    
    # Autres caches
    php artisan route:cache
    php artisan view:cache
    
    # Générer la documentation Swagger
    php artisan l5-swagger:generate
    
    print_success "Configuration Laravel terminée"
else
    echo "DRY RUN - Commandes Laravel qui seraient exécutées:"
    echo "  - php artisan vendor:publish --provider=Laravel\\Reverb\\ReverbServiceProvider --force"
    echo "  - php artisan config:clear"
    echo "  - php artisan config:cache"
    echo "  - php artisan migrate"
    echo "  - php artisan route:cache"
    echo "  - php artisan view:cache"
    echo "  - php artisan l5-swagger:generate"
fi

# 6. Configuration Supervisor pour Reverb
print_status "Génération de la configuration Supervisor..."

SUPERVISOR_CONFIG="[program:reverb-isogaz]
command=php /var/www/html/isogaz/artisan reverb:start --host=0.0.0.0 --port=8080 --hostname=$DOMAIN
directory=/var/www/html/isogaz
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/supervisor/reverb-isogaz.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
environment=LARAVEL_ENV=\"production\""

if [[ "$DRY_RUN" = false ]]; then
    echo "$SUPERVISOR_CONFIG" > "supervisor-reverb-isogaz.conf"
    print_success "Configuration Supervisor sauvegardée dans supervisor-reverb-isogaz.conf"
else
    echo "DRY RUN - Configuration Supervisor qui serait générée dans supervisor-reverb-isogaz.conf"
fi

# 7. Configuration Apache pour WebSocket
print_status "Génération de la configuration Apache pour WebSocket..."

APACHE_WEBSOCKET_CONFIG="# WebSocket Reverb Proxy - À ajouter dans le VirtualHost *:443
    # WebSocket Reverb Proxy
    ProxyPreserveHost On
    ProxyPass /app/ ws://127.0.0.1:8080/app/
    ProxyPassReverse /app/ ws://127.0.0.1:8080/app/
    
    # Alternative HTTP proxy pour les fallbacks
    ProxyPass /reverb/ http://127.0.0.1:8080/
    ProxyPassReverse /reverb/ http://127.0.0.1:8080/"

if [[ "$DRY_RUN" = false ]]; then
    echo "$APACHE_WEBSOCKET_CONFIG" > "apache-websocket-config.txt"
    print_success "Configuration Apache WebSocket sauvegardée dans apache-websocket-config.txt"
else
    echo "DRY RUN - Configuration Apache WebSocket qui serait générée dans apache-websocket-config.txt"
fi

# 8. Vérification des permissions
print_status "Vérification des permissions..."

if [[ "$DRY_RUN" = false ]]; then
    # Permissions Laravel
    chmod -R 775 storage bootstrap/cache
    chown -R www-data:www-data storage bootstrap/cache
    
    print_success "Permissions mises à jour"
else
    echo "DRY RUN - Permissions qui seraient appliquées:"
    echo "  - chmod -R 775 storage bootstrap/cache"
    echo "  - chown -R www-data:www-data storage bootstrap/cache"
fi


# Résumé final
echo ""
echo "🎉 Déploiement Staging terminé!"
echo "==============================="

if [[ "$DRY_RUN" = false ]]; then
    print_success "✅ Configuration .env mise à jour"
    print_success "✅ Fichiers JavaScript configurés"
    print_success "✅ Dépendances installées"
    print_success "✅ Laravel configuré"
    print_success "✅ Fichiers de configuration serveur générés"
    
    echo ""
    echo "📋 Prochaines étapes manuelles REQUISES:"
    echo "1. 🔧 Configurer Supervisor:"
    echo "   sudo cp supervisor-reverb-isogaz.conf /etc/supervisor/conf.d/"
    echo "   sudo supervisorctl reread && sudo supervisorctl update"
    echo "   sudo supervisorctl start reverb-isogaz:*"
    echo ""
    echo "2. 🌐 Configurer Apache WebSocket Proxy:"
    echo "   sudo a2enmod proxy proxy_http proxy_wstunnel"
    echo "   # Ajouter le contenu de apache-websocket-config.txt dans /etc/apache2/sites-available/isogaz.afrik-solutions.com.conf"
    echo "   # Dans la section <VirtualHost *:443>, avant </VirtualHost>"
    echo "   sudo systemctl reload apache2"
    echo ""
    echo "3. 🔥 Ouvrir le port 8080:"
    echo "   sudo ufw allow 8080/tcp comment 'Laravel Reverb WebSocket'"
    echo ""
    echo "4. ✅ Vérifier le fonctionnement:"
    echo "   sudo supervisorctl status reverb-isogaz:*"
    echo "   sudo netstat -tlnp | grep :8080"
    echo "   ./test-staging.sh"
    
    echo ""
    echo "🌐 URLs de test:"
    echo "👥 Interface Client: https://$DOMAIN/test/delivery-tracking/client/"
    echo "🚚 Interface Livreur: https://$DOMAIN/test/delivery-tracking/delivery/"
    echo "📚 API Documentation: https://$DOMAIN/api/documentation"
    
    echo ""
    echo "📁 Sauvegarde des anciens fichiers: $BACKUP_DIR/"
else
    echo "DRY RUN terminé - Aucune modification appliquée"
    echo "Exécutez sans --dry-run pour appliquer les changements"
fi

print_success "Script terminé avec succès!"