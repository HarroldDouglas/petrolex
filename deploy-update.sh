#!/bin/bash

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${BLUE}🚀 MISE À JOUR PRODUCTION - Petrolex${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Erreur: Ce script doit être exécuté depuis la racine du projet${NC}"
    exit 1
fi

echo -e "${BLUE}📥 Pull des dernières modifications...${NC}"
CURRENT_BRANCH=$(git branch --show-current)
echo -e "${YELLOW}📍 Branche: ${CURRENT_BRANCH}${NC}"
git pull origin $CURRENT_BRANCH

echo ""
echo -e "${YELLOW}⏸️  Mode maintenance...${NC}"
php artisan down --retry=60 --refresh=15 2>/dev/null || php artisan down

echo ""
echo -e "${BLUE}🎼 Dépendances Composer...${NC}"
composer install --optimize-autoloader --no-dev

echo ""
echo -e "${BLUE}🧹 Nettoyage des caches...${NC}"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo ""
echo -e "${BLUE}🗄️  Migrations...${NC}"
php artisan migrate --force

echo ""
echo -e "${BLUE}🔗 Lien storage...${NC}"
php artisan storage:link 2>/dev/null || echo "Déjà existant"

echo ""
echo -e "${BLUE}⚡ Optimisation...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

echo ""
echo -e "${BLUE}🔐 Permissions...${NC}"
chown -R www-data:www-data /var/www/isogaz
chmod -R 755 /var/www/isogaz
chmod -R 775 /var/www/isogaz/storage
chmod -R 775 /var/www/isogaz/bootstrap/cache

echo ""
echo -e "${BLUE}🔄 Redémarrage Supervisor...${NC}"
supervisorctl restart all

echo ""
echo -e "${BLUE}🔄 Redémarrage PHP-FPM et Nginx...${NC}"
systemctl restart php8.3-fpm
systemctl reload nginx

echo ""
echo -e "${GREEN}✅ Désactivation du mode maintenance...${NC}"
php artisan up

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${GREEN}✅ MISE À JOUR TERMINÉE !${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo -e "${GREEN}🌐 https://isogaz.afrik-solutions.com${NC}"
echo ""
echo -e "${YELLOW}📝 Vérifie les logs si besoin :${NC}"
echo "   tail -f /var/www/isogaz/storage/logs/laravel.log"
echo ""
echo -e "${BLUE}💡 Commandes utiles :${NC}"
echo "   supervisorctl status          # Voir le statut des workers"
echo "   supervisorctl restart all     # Redémarrer tous les workers"
echo "   php artisan queue:restart     # Redémarrer la queue"
echo "   systemctl status nginx        # Statut de Nginx"
echo "   systemctl status php8.3-fpm   # Statut de PHP-FPM"
echo ""
