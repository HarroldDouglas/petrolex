#!/bin/bash

# ============================================================
# reset-prod-db.sh — RÉINITIALISE la base de PRODUCTION
# ⚠️ EFFACE TOUTES les données (clients, commandes, paiements,
# bouteilles, produits...) et recrée le baseline :
#   - Super admin (admin@isogaz.net via ADMIN_* du .env)
#   - Centre Logbessou (stock 0), Bouteille 9Kg + accessoires
#   - Géo, rôles, comptes de test
# Un backup complet de la BD est fait AVANT le wipe.
# ============================================================

set -euo pipefail

SERVER="isogaz"
REMOTE_PATH="/var/www/petrolex"

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'

echo ""
echo -e "${RED}============================================================${NC}"
echo -e "${RED}  RESET BASE DE PRODUCTION — DESTRUCTIF ET IRRÉVERSIBLE${NC}"
echo -e "${RED}============================================================${NC}"
echo -e "${YELLOW}  Cible : ${SERVER}:${REMOTE_PATH}${NC}"
echo -e "${YELLOW}  Toutes les données réelles seront EFFACÉES (backup fait avant).${NC}"
echo ""
read -p "Tape exactement 'RESET' pour confirmer : " CONFIRM
if [ "$CONFIRM" != "RESET" ]; then
    echo -e "${RED}Annulé.${NC}"; exit 1
fi

echo -e "${YELLOW}» Exécution sur ${SERVER}...${NC}"

ssh "$SERVER" bash -s <<'REMOTE'
set -e
cd /var/www/petrolex

# Creds DB lus depuis .env (jamais dans le repo)
DB_DATABASE=$(grep -E '^DB_DATABASE=' .env | cut -d= -f2- | tr -d '"'"'"'')
DB_USERNAME=$(grep -E '^DB_USERNAME=' .env | cut -d= -f2- | tr -d '"'"'"'')
DB_PASSWORD=$(grep -E '^DB_PASSWORD=' .env | cut -d= -f2- | tr -d '"'"'"'')

STAMP=$(date +%Y%m%d-%H%M%S)
BK="$HOME/petrolex-backups/${STAMP}-prereset"
mkdir -p "$BK"
echo "  → backup BD : $BK/db.sql.gz"
mysqldump -u"$DB_USERNAME" -p"$DB_PASSWORD" --no-tablespaces --single-transaction --routines --triggers "$DB_DATABASE" 2>/dev/null | gzip > "$BK/db.sql.gz"
echo "    ($(zcat "$BK/db.sql.gz" | grep -c 'CREATE TABLE') tables sauvegardées)"

php artisan down --retry=30 || true

# Les seeders (UserSeeder) utilisent des factories → Faker (dep DEV) requis
# le temps du seed. On réinstalle dev, on seede, puis on repasse en --no-dev.
echo "  → composer (avec dev, pour Faker)"
composer install --optimize-autoloader --no-interaction >/dev/null 2>&1

echo "  → migrate:fresh --seed (WIPE + baseline)"
php artisan migrate:fresh --seed --force

echo "  → composer --no-dev (retire Faker)"
composer install --no-dev --optimize-autoloader --no-interaction >/dev/null 2>&1

echo "  → permissions + caches"
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true
command -v setfacl >/dev/null && setfacl -R -m g:www-data:rwX -d -m g:www-data:rwX resources/lang 2>/dev/null || true
php artisan optimize:clear
php artisan config:cache
php artisan route:cache || php artisan route:clear
php artisan view:cache

echo "  → workers + OPcache"
php artisan queue:restart
sudo -n systemctl reload php8.2-fpm
sudo -n supervisorctl restart all >/dev/null

php artisan up

echo "  ✓ Baseline recréé :"
mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" -N -e \
  "SELECT CONCAT('    centres=', (SELECT COUNT(*) FROM distribution_centers), ' (', (SELECT name FROM distribution_centers LIMIT 1), '), bouteilles=', (SELECT COUNT(*) FROM bottle_types), ', admin=', (SELECT email FROM users ORDER BY id LIMIT 1));" 2>/dev/null
REMOTE

echo ""
CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://isogaz.net" || echo "000")
if [[ "$CODE" =~ ^(200|301|302)$ ]]; then
    echo -e "${GREEN}✅ RESET TERMINÉ — site HTTP ${CODE}. Admin: admin@isogaz.net (ADMIN_PASSWORD du .env).${NC}"
else
    echo -e "${RED}⚠️  Site HTTP ${CODE} — vérifie: ssh $SERVER 'tail -50 $REMOTE_PATH/storage/logs/laravel.log'${NC}"
    exit 1
fi
echo ""
