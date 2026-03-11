#!/bin/bash
# ==============================================================================
# Deploy a git tag to production
# Usage: ./scripts/deploy.sh <tag>
# Example: ./scripts/deploy.sh v1.2.0
# ==============================================================================

set -euo pipefail

# ── Config ────────────────────────────────────────────────────────────────────
TAG=${1:?$'\n  Usage: ./scripts/deploy.sh <tag>\n  Example: ./scripts/deploy.sh v1.2.0\n'}
SERVER="isogaz"
APP_DIR="/var/www/petrolex"
SUDO_PASS="IsoPTX@527."
ARCHIVE="/tmp/petrolex-${TAG}.tar.gz"

# ── Helpers ───────────────────────────────────────────────────────────────────
GREEN='\033[0;32m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; NC='\033[0m'
step()  { echo -e "\n${GREEN}[→]${NC} $1"; }
warn()  { echo -e "${YELLOW}[!]${NC} $1"; }
fail()  { echo -e "\n${RED}[✗]${NC} $1"; exit 1; }

# ── Pre-flight checks ─────────────────────────────────────────────────────────
step "Pre-flight checks..."

# Tag must exist locally
git rev-parse "refs/tags/$TAG" > /dev/null 2>&1 \
  || fail "Tag '$TAG' not found. Create it first: git tag $TAG && git push origin $TAG"

# Confirm
echo -e "  Tag     : ${GREEN}$TAG${NC}"
echo -e "  Server  : ${GREEN}$SERVER ($APP_DIR)${NC}"
echo ""
read -rp "  Deploy $TAG to production? [y/N] " confirm
[[ "$confirm" =~ ^[Yy]$ ]] || { echo "Cancelled."; exit 0; }

# ── 1. Create archive ─────────────────────────────────────────────────────────
step "Creating archive for $TAG..."
git archive --format=tar.gz "$TAG" -o "$ARCHIVE"
echo "  Archive: $ARCHIVE ($(du -sh "$ARCHIVE" | cut -f1))"

# ── 2. Upload ─────────────────────────────────────────────────────────────────
step "Uploading archive to server..."
scp "$ARCHIVE" "$SERVER:/tmp/"
rm -f "$ARCHIVE"

# ── 3. Deploy on server ───────────────────────────────────────────────────────
step "Deploying on server..."

ssh "$SERVER" "echo '$SUDO_PASS' | sudo -S bash -s -- '$TAG' '$APP_DIR' '$SUDO_PASS'" << 'ENDSSH'
set -e
TAG=$1
APP_DIR=$2
SUDO_PASS=$3
ARCHIVE="/tmp/petrolex-${TAG}.tar.gz"

echo "  [1/7] Maintenance mode ON..."
sudo -u www-data php "$APP_DIR/artisan" down --render="errors.503" 2>/dev/null || \
  sudo -u www-data php "$APP_DIR/artisan" down

echo "  [2/7] Extracting archive..."
tar -xzf "$ARCHIVE" -C "$APP_DIR"

echo "  [3/7] Fixing permissions..."
chown -R www-data:www-data "$APP_DIR"
find "$APP_DIR/storage" -type d -exec chmod 775 {} \;
find "$APP_DIR/bootstrap/cache" -type d -exec chmod 775 {} \;

echo "  [4/7] Installing dependencies..."
sudo -u www-data composer install \
  --no-dev \
  --optimize-autoloader \
  --no-interaction \
  --quiet \
  --working-dir="$APP_DIR"

echo "  [5/7] Running migrations..."
sudo -u www-data php "$APP_DIR/artisan" migrate --force

echo "  [6/7] Optimizing..."
sudo -u www-data php "$APP_DIR/artisan" optimize:clear
sudo -u www-data php "$APP_DIR/artisan" optimize

# Ensure storage symlink is correct
if [ ! -L "$APP_DIR/public/storage" ]; then
  echo "  [fix] Restoring storage symlink..."
  rm -rf "$APP_DIR/public/storage"
  sudo -u www-data php "$APP_DIR/artisan" storage:link --force
fi

echo "  [7/7] Restarting workers..."
supervisorctl restart petrolex-worker:* 2>/dev/null || true
supervisorctl restart petrolex-reverb 2>/dev/null || true

echo "  Maintenance mode OFF..."
sudo -u www-data php "$APP_DIR/artisan" up

# Cleanup
rm -f "$ARCHIVE"

echo ""
echo "  ✓ Deploy $TAG complete!"
ENDSSH

# ── Done ──────────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}══════════════════════════════════════${NC}"
echo -e "${GREEN}  $TAG is live on production!${NC}"
echo -e "${GREEN}══════════════════════════════════════${NC}"
echo ""
