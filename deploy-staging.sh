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

# Variables WebSocket à ajouter
ENV_WEBSOCKET_VARS=(
    "BROADCAST_CONNECTION=reverb"
    "REVERB_SERVER=laravel"
    "REVERB_HOST=0.0.0.0"
    "REVERB_PORT=8080"
    "REVERB_HOSTNAME=$DOMAIN"
    "REVERB_SCHEME=https"
    "REVERB_APP_ID=petrolex-app"
    "REVERB_APP_KEY=petro-key-12345"
    "REVERB_APP_SECRET=petro-secret-67890"
    "REVERB_APP_HOST=$DOMAIN"
    "REVERB_APP_PORT=8080"
    "REVERB_APP_SCHEME=https"
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

# 2. Mise à jour du shared-config.js (préservation de la détection auto des ports)
print_status "Mise à jour de la configuration JavaScript (API et Google Maps seulement)..."

SHARED_CONFIG_FILE="public/test/delivery-tracking/shared-config.js"

if [[ "$DRY_RUN" = false ]]; then
    if [[ -f "$SHARED_CONFIG_FILE" ]]; then
        # Mise à jour sélective sans écraser la détection auto des ports
        sed -i "s|API_BASE_URL: .*|API_BASE_URL: 'https://$DOMAIN/api',|" "$SHARED_CONFIG_FILE"
        sed -i "s|API_KEY: '.*'|API_KEY: '$GOOGLE_MAPS_KEY'|" "$SHARED_CONFIG_FILE"
        print_success "Configuration JavaScript mise à jour (ports WebSocket préservés)"
    else
        print_warning "Fichier $SHARED_CONFIG_FILE non trouvé"
    fi
else
    echo "DRY RUN - Modifications qui seraient appliquées à shared-config.js:"
    echo "  - API_BASE_URL: https://$DOMAIN/api"
    echo "  - GOOGLE_MAPS_API_KEY: ${GOOGLE_MAPS_KEY:0:20}..."
    echo "  - PORTS WEBSOCKET: Préservés (détection automatique maintenue)"
fi

# 3. Mise à jour des autres fichiers de configuration (API seulement)
print_status "Mise à jour des configurations API (WebSocket préservé)..."

CONFIG_FILES=(
    "public/test/delivery-tracking/delivery/js/config.js"
    "public/test/delivery-tracking/client/js/components/login-form.js"
)

for file in "${CONFIG_FILES[@]}"; do
    if [[ -f "$file" ]]; then
        if [[ "$DRY_RUN" = false ]]; then
            # Remplacer seulement les URLs API, pas les WebSocket
            sed -i "s|127\.0\.0\.1:8001|$DOMAIN|g" "$file"
            sed -i "s|localhost:8001|$DOMAIN|g" "$file"
            # NE PAS remplacer les ports WebSocket - détection automatique maintenue
            print_status "Mis à jour API: $file"
        else
            echo "DRY RUN - Fichier API qui serait modifié: $file"
        fi
    fi
done

print_warning "⚠️  Ports WebSocket préservés - détection automatique maintenue"
print_warning "⚠️  Ne pas modifier reverb-client.js - contient la détection auto HTTP/HTTPS"

# 4. Vérification des fichiers de configuration Laravel
print_status "Vérification des fichiers de configuration Laravel..."

if [[ "$DRY_RUN" = false ]]; then
    # Vérifier si config/reverb.php existe
    if [[ ! -f "config/reverb.php" ]]; then
        print_status "Création de config/reverb.php..."
        cat > config/reverb.php << 'EOF'
<?php

return [
    'default' => env('REVERB_SERVER', 'laravel'),

    'servers' => [
        'laravel' => [
            'host' => env('REVERB_HOST', '0.0.0.0'),
            'port' => env('REVERB_PORT', 8080),
            'hostname' => env('REVERB_HOSTNAME'),
            'options' => [
                'tls' => [],
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
            'app_id' => env('REVERB_APP_ID'),
            'app_key' => env('REVERB_APP_KEY'),
            'app_secret' => env('REVERB_APP_SECRET'),
            'options' => [
                'host' => env('REVERB_APP_HOST'),
                'port' => env('REVERB_APP_PORT', 8080),
                'scheme' => env('REVERB_APP_SCHEME', 'https'),
            ],
            'allowed_origins' => ['*'],
            'ping_interval' => env('REVERB_APP_PING_INTERVAL', 60),
            'max_message_size' => env('REVERB_APP_MAX_MESSAGE_SIZE', 10000),
        ],
    ],
];
EOF
        print_success "Créé: config/reverb.php"
    else
        print_status "config/reverb.php existe déjà"
    fi

    # Mettre à jour config/broadcasting.php pour ajouter reverb
    if [[ -f "config/broadcasting.php" ]]; then
        # Vérifier si reverb existe déjà
        if ! grep -q "'reverb'" config/broadcasting.php; then
            # Backup
            cp config/broadcasting.php "$BACKUP_DIR/broadcasting.php.backup"
            
            # Ajouter la config reverb avant la dernière accolade
            sed -i '/^];$/i\
\
    '"'"'reverb'"'"' => [\
        '"'"'driver'"'"' => '"'"'reverb'"'"',\
        '"'"'key'"'"' => env('"'"'REVERB_APP_KEY'"'"'),\
        '"'"'secret'"'"' => env('"'"'REVERB_APP_SECRET'"'"'),\
        '"'"'app_id'"'"' => env('"'"'REVERB_APP_ID'"'"'),\
        '"'"'options'"'"' => [\
            '"'"'host'"'"' => env('"'"'REVERB_HOST'"'"', '"'"'127.0.0.1'"'"'),\
            '"'"'port'"'"' => env('"'"'REVERB_PORT'"'"', 8080),\
            '"'"'scheme'"'"' => env('"'"'REVERB_SCHEME'"'"', '"'"'http'"'"'),\
        ],\
    ],' config/broadcasting.php
            
            print_success "Mis à jour: config/broadcasting.php"
        else
            print_status "config/broadcasting.php déjà configuré pour reverb"
        fi
    fi
else
    echo "DRY RUN - Vérifications qui seraient faites:"
    echo "  - config/reverb.php (créé seulement s'il n'existe pas)"
    echo "  - config/broadcasting.php (section reverb ajoutée seulement si absente)"
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
    # Exécuter les migrations
    php artisan migrate
    
    # Mettre en cache la configuration
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Générer la documentation Swagger
    php artisan l5-swagger:generate
    
    print_success "Configuration Laravel terminée"
else
    echo "DRY RUN - Commandes Laravel qui seraient exécutées:"
    echo "  - php artisan migrate"
    echo "  - php artisan config:cache"
    echo "  - php artisan route:cache"
    echo "  - php artisan view:cache"
    echo "  - php artisan l5-swagger:generate"
fi

# 6. Configuration Supervisor pour Reverb
print_status "Génération de la configuration Supervisor..."

SUPERVISOR_CONFIG="[program:reverb-isogaz]
command=php /var/www/html/isogaz/artisan reverb:start --host=0.0.0.0 --port=8080
directory=/var/www/html/isogaz
autostart=true
autorestart=true
user=www-data
stdout_logfile=/var/log/reverb-isogaz.log
stderr_logfile=/var/log/reverb-isogaz.log"

if [[ "$DRY_RUN" = false ]]; then
    echo "$SUPERVISOR_CONFIG" > "supervisor-reverb.conf"
    print_success "Configuration Supervisor sauvegardée dans supervisor-reverb.conf"
else
    echo "DRY RUN - Configuration Supervisor qui serait générée dans supervisor-reverb.conf"
fi

# 7. Vérification des permissions
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
    echo "📋 Prochaines étapes manuelles:"
    echo "1. Configurer Supervisor (si pas encore fait)"
    echo "2. Configurer Apache pour proxy WebSocket (si pas encore fait)"
    echo "3. Ouvrir le port 8080 (si pas encore fait)"
    echo "4. Démarrer le serveur WebSocket"
    echo ""
    echo "👆 Dites-moi ce qui est déjà configuré pour que je vous donne les bonnes commandes !"
    
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