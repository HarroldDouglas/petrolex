#!/bin/bash
# ==============================================================================
# Reset Production Database
# Usage: ./scripts/reset-production-db.sh
# ==============================================================================

set -e

APP_DIR="/var/www/petrolex"
PHP="php"

cd "$APP_DIR"

echo ""
echo "=============================================="
echo "  RESET BASE DE DONNÉES - PRODUCTION"
echo "=============================================="
echo ""

# Étape 1 : Fix du symlink storage (public/storage doit être un symlink)
echo "[1/4] Fix symlink storage..."
if [ -d "$APP_DIR/public/storage" ] && [ ! -L "$APP_DIR/public/storage" ]; then
    echo "  → public/storage est un vrai dossier, conversion en symlink..."
    rm -rf "$APP_DIR/public/storage"
fi
$PHP artisan storage:link --force
echo "  ✓ Symlink OK"
echo ""

# Étape 2 : Migrations
echo "[2/4] Migrations..."
$PHP artisan migrate:fresh --force
echo ""

# Étape 3 : Seeders individuels (processus séparés = pas de bug Spatie)
echo "[3/4] Seeding..."
$PHP artisan db:seed --class="Database\\Seeders\\GeographicSeeder" --force
$PHP artisan db:seed --class="Database\\Seeders\\RolePermissionSeeder" --force
$PHP artisan db:seed --class="Database\\Seeders\\UserSeeder" --force
$PHP artisan db:seed --class="Database\\Seeders\\TestCustomerSeeder" --force
$PHP artisan db:seed --class="Database\\Seeders\\Production\\BottleTypeSeeder" --force
$PHP artisan db:seed --class="Database\\Seeders\\Production\\AccessoryTypeSeeder" --force
$PHP artisan db:seed --class="Database\\Seeders\\Production\\DistributionCenterSeeder" --force
$PHP artisan db:seed --class="Database\\Seeders\\Production\\ProductCategorySeeder" --force
$PHP artisan db:seed --class="Database\\Seeders\\Production\\DeliveryPersonSeeder" --force
$PHP artisan db:seed --class="Database\\Seeders\\AppVersionSeeder" --force
echo ""

# Étape 4 : Caches
echo "[4/4] Nettoyage des caches..."
$PHP artisan optimize:clear
echo ""

# Résumé
echo "----------------------------------------------"
$PHP artisan tinker --execute='
    echo "  Users           : " . \App\Models\User::count() . "\n";
    echo "  Bottle types    : " . \App\Models\BottleType::count() . "\n";
    echo "  Accessory types : " . \App\Models\AccessoryType::count() . "\n";
    echo "  Product cats    : " . \App\Models\ProductCategory::count() . "\n";
    $mediaCount = \Spatie\MediaLibrary\MediaCollections\Models\Media::count();
    echo "  Media (images)  : " . $mediaCount . "\n";
    echo "  Delivery persons: " . \App\Models\DeliveryPerson::count() . "\n";
' 2>/dev/null
echo "----------------------------------------------"
echo ""
echo "  Admin      : admin@isogaz.net"
echo "  Client test: test.customer@petrolex.com / TestPetrolex2026!"
echo "  Livreur    : livreur.douala@isogaz.net / Livreur@2026!"
echo ""
echo "=============================================="
echo "  RESET TERMINÉ AVEC SUCCÈS"
echo "=============================================="
echo ""
