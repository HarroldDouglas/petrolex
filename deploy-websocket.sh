#!/bin/bash

# 🚀 Script de Déploiement Automatisé WebSocket - Petrolex
# Usage: ./deploy-websocket.sh [domain] [google_maps_key] [--dry-run]

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

# Configuration par défaut
DEFAULT_DOMAIN="petrolex.com"
DEFAULT_GOOGLE_MAPS_KEY="AIzaSyB0w8HLsobdoJgK7WUTQxLFUuZOirvmUCI"

# Parse arguments
DOMAIN=${1:-$DEFAULT_DOMAIN}
GOOGLE_MAPS_KEY=${2:-$DEFAULT_GOOGLE_MAPS_KEY}
DRY_RUN=false

for arg in "$@"; do
    if [[ "$arg" == "--dry-run" ]]; then
        DRY_RUN=true
    fi
done

echo "🚀 Déploiement WebSocket - Petrolex"
echo "=================================="
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

# Vérifier que git est propre (optionnel)
if [[ -n "$(git status --porcelain)" ]]; then
    print_warning "Il y a des changements non committés dans le répertoire de travail"
    read -p "Continuer quand même? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
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

# 1. Mise à jour du fichier .env
print_status "Configuration du fichier .env..."

ENV_UPDATES=(
    "APP_URL=https://$DOMAIN"
    "REVERB_HOST=$DOMAIN"
    "REVERB_PORT=8080"
    "REVERB_SCHEME=wss"
    "GOOGLE_MAPS_API_KEY=$GOOGLE_MAPS_KEY"
)

if [[ "$DRY_RUN" = false ]]; then
    for update in "${ENV_UPDATES[@]}"; do
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
else
    echo "DRY RUN - Modifications .env qui seraient appliquées:"
    for update in "${ENV_UPDATES[@]}"; do
        echo "  - $update"
    done
fi

# 2. Mise à jour du shared-config.js
print_status "Mise à jour de la configuration JavaScript..."

SHARED_CONFIG_FILE="public/test/delivery-tracking/shared-config.js"

if [[ "$DRY_RUN" = false ]]; then
    # Remplacer les URLs dans shared-config.js
    sed -i "s|API_BASE_URL: \`\${protocol}//\${hostname}:8001/api\`|API_BASE_URL: \`https://$DOMAIN/api\`|g" "$SHARED_CONFIG_FILE"
    sed -i "s|WS_HOST: hostname|WS_HOST: '$DOMAIN'|g" "$SHARED_CONFIG_FILE"
    sed -i "s|API_BASE_URL: 'http://127.0.0.1:8001/api'|API_BASE_URL: 'https://$DOMAIN/api'|g" "$SHARED_CONFIG_FILE"
    sed -i "s|WS_HOST: '127.0.0.1'|WS_HOST: '$DOMAIN'|g" "$SHARED_CONFIG_FILE"
    sed -i "s|API_KEY: '[^']*'|API_KEY: '$GOOGLE_MAPS_KEY'|g" "$SHARED_CONFIG_FILE"
    
    print_success "Configuration JavaScript mise à jour"
else
    echo "DRY RUN - Modifications shared-config.js qui seraient appliquées:"
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
            # Remplacer localhost/127.0.0.1 par le domaine de production
            sed -i "s|127\.0\.0\.1:8001|$DOMAIN|g" "$file"
            sed -i "s|localhost:8001|$DOMAIN|g" "$file"
            sed -i "s|127\.0\.0\.1:8080|$DOMAIN:8080|g" "$file"
            sed -i "s|localhost:8080|$DOMAIN:8080|g" "$file"
            print_status "Mis à jour: $file"
        else
            echo "DRY RUN - Fichier qui serait modifié: $file"
        fi
    fi
done

# 4. Installation/Mise à jour des dépendances
print_status "Installation des dépendances..."

if [[ "$DRY_RUN" = false ]]; then
    # Installer les dépendances PHP
    composer install --optimize-autoloader --no-dev
    
    # Installer les dépendances Node.js si nécessaire
    if [[ -f "package.json" ]]; then
        npm install --production
    fi
    
    print_success "Dépendances installées"
else
    echo "DRY RUN - Commandes qui seraient exécutées:"
    echo "  - composer install --optimize-autoloader --no-dev"
    echo "  - npm install --production (si package.json existe)"
fi

# 5. Configuration Laravel
print_status "Configuration Laravel..."

if [[ "$DRY_RUN" = false ]]; then
    # Générer la clé d'application si nécessaire
    if ! grep -q "APP_KEY=" .env || [[ -z "$(grep APP_KEY= .env | cut -d'=' -f2)" ]]; then
        php artisan key:generate --force
    fi
    
    # Exécuter les migrations
    php artisan migrate --force
    
    # Mettre en cache la configuration
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    print_success "Configuration Laravel terminée"
else
    echo "DRY RUN - Commandes Laravel qui seraient exécutées:"
    echo "  - php artisan key:generate --force (si nécessaire)"
    echo "  - php artisan migrate --force"
    echo "  - php artisan config:cache"
    echo "  - php artisan route:cache"
    echo "  - php artisan view:cache"
fi

# 6. Configuration du serveur Web
print_status "Génération de la configuration Nginx..."

NGINX_CONFIG="# Configuration Nginx pour $DOMAIN - WebSocket Support
server {
    listen 80;
    listen 443 ssl;
    server_name $DOMAIN;

    root /path/to/your/project/public;
    index index.php index.html;

    # SSL configuration (à adapter selon votre certificat)
    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/private.key;

    # Main application
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # PHP processing
    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    # WebSocket proxy pour Laravel Reverb
    location /app/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection \"Upgrade\";
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }

    # Static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)\$ {
        expires 1y;
        add_header Cache-Control \"public, immutable\";
    }
}"

if [[ "$DRY_RUN" = false ]]; then
    echo "$NGINX_CONFIG" > "nginx-$DOMAIN.conf"
    print_success "Configuration Nginx sauvegardée dans nginx-$DOMAIN.conf"
else
    echo "DRY RUN - Configuration Nginx qui serait générée dans nginx-$DOMAIN.conf"
fi

# 7. Configuration Supervisor pour Reverb
print_status "Génération de la configuration Supervisor..."

SUPERVISOR_CONFIG="[program:reverb-$DOMAIN]
command=php /path/to/your/project/artisan reverb:start --host=0.0.0.0 --port=8080
directory=/path/to/your/project
autostart=true
autorestart=true
user=www-data
stdout_logfile=/var/log/reverb-$DOMAIN.log
stderr_logfile=/var/log/reverb-$DOMAIN.log"

if [[ "$DRY_RUN" = false ]]; then
    echo "$SUPERVISOR_CONFIG" > "supervisor-reverb-$DOMAIN.conf"
    print_success "Configuration Supervisor sauvegardée dans supervisor-reverb-$DOMAIN.conf"
else
    echo "DRY RUN - Configuration Supervisor qui serait générée dans supervisor-reverb-$DOMAIN.conf"
fi

# 8. Script de test de déploiement
print_status "Génération du script de test..."

TEST_SCRIPT="#!/bin/bash
# Script de test pour vérifier le déploiement WebSocket

echo \"🧪 Test de déploiement WebSocket pour $DOMAIN\"
echo \"============================================\"

# Test 1: API Health Check
echo \"1️⃣ Test API Health Check...\"
curl -s \"https://$DOMAIN/api/health\" | jq '.' || echo \"❌ API non accessible\"

# Test 2: WebSocket Connection
echo \"2️⃣ Test WebSocket (port 8080)...\"
nc -zv $DOMAIN 8080 || echo \"❌ WebSocket non accessible\"

# Test 3: Frontend Access
echo \"3️⃣ Test Frontend...\"
curl -s -o /dev/null -w \"%{http_code}\" \"https://$DOMAIN/test/delivery-tracking/client/\" || echo \"❌ Frontend non accessible\"

echo \"✅ Tests terminés\"
"

if [[ "$DRY_RUN" = false ]]; then
    echo "$TEST_SCRIPT" > "test-deployment-$DOMAIN.sh"
    chmod +x "test-deployment-$DOMAIN.sh"
    print_success "Script de test sauvegardé dans test-deployment-$DOMAIN.sh"
else
    echo "DRY RUN - Script de test qui serait généré dans test-deployment-$DOMAIN.sh"
fi

# Résumé final
echo ""
echo "🎉 Déploiement WebSocket terminé!"
echo "================================="

if [[ "$DRY_RUN" = false ]]; then
    print_success "✅ Configuration .env mise à jour"
    print_success "✅ Fichiers JavaScript configurés"
    print_success "✅ Dépendances installées"
    print_success "✅ Laravel configuré"
    print_success "✅ Fichiers de configuration serveur générés"
    
    echo ""
    echo "📋 Prochaines étapes manuelles:"
    echo "1. Copier nginx-$DOMAIN.conf vers /etc/nginx/sites-available/"
    echo "2. Activer le site: ln -s /etc/nginx/sites-available/nginx-$DOMAIN.conf /etc/nginx/sites-enabled/"
    echo "3. Copier supervisor-reverb-$DOMAIN.conf vers /etc/supervisor/conf.d/"
    echo "4. Recharger Supervisor: supervisorctl reread && supervisorctl update"
    echo "5. Démarrer Reverb: supervisorctl start reverb-$DOMAIN"
    echo "6. Recharger Nginx: nginx -t && systemctl reload nginx"
    echo "7. Tester avec: ./test-deployment-$DOMAIN.sh"
    
    echo ""
    echo "🌐 URLs de test:"
    echo "👥 Interface Client: https://$DOMAIN/test/delivery-tracking/client/"
    echo "🚚 Interface Livreur: https://$DOMAIN/test/delivery-tracking/delivery/"
    
    echo ""
    echo "📁 Sauvegarde des anciens fichiers: $BACKUP_DIR/"
else
    echo "DRY RUN terminé - Aucune modification appliquée"
    echo "Exécutez sans --dry-run pour appliquer les changements"
fi

print_success "Script terminé avec succès!"