#!/bin/bash

# ============================================================
# deploy-from-local.sh — Déploiement Petrolex depuis le local
# La prod est un clone git (remote "github"). Ce script fait
# git fetch + reset --hard côté serveur, puis post-déploiement.
# ============================================================

set -euo pipefail

SERVER="isogaz"                 # alias SSH (~/.ssh/config)
REMOTE_PATH="/var/www/petrolex"
GIT_REMOTE="github"             # remote pointant vers GitHub sur le serveur
REF=""                          # tag à déployer (OBLIGATOIRE — ex: v1.7.0)
SKIP_MAINTENANCE=false

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; CYAN='\033[0;36m'; NC='\033[0m'

while [[ $# -gt 0 ]]; do
    case "$1" in
        --no-maintenance) SKIP_MAINTENANCE=true; shift ;;
        -h|--help)
            echo ""
            echo -e "${BLUE}Déploiement Petrolex${NC}"
            echo -e "${YELLOW}Usage:${NC}"
            echo "  ./deploy-from-local.sh <tag>                    # déploie un tag versionné (OBLIGATOIRE)"
            echo "  ./deploy-from-local.sh v1.7.0                   # exemple"
            echo "  ./deploy-from-local.sh v1.7.0 --no-maintenance  # sans page de maintenance"
            echo ""
            exit 0 ;;
        *) REF="$1"; shift ;;
    esac
done

# Garde-fou : la prod ne se déploie que par tag versionné (décision 2026-08-10),
# jamais par branche — une branche est mouvante, un tag est traçable et rollbackable.
if [[ -z "$REF" ]]; then
    echo -e "${RED}Ref manquante : indiquez le tag à déployer (ex: ./deploy-from-local.sh v1.7.0)${NC}"
    echo -e "Tags disponibles : $(git tag -l 'v*' | tail -3 | tr '\n' ' ')"
    exit 1
fi
if [[ ! "$REF" =~ ^v[0-9] ]]; then
    echo -e "${RED}'$REF' n'est pas un tag versionné (vX.Y.Z). La prod ne se déploie que par tag.${NC}"
    exit 1
fi

# Garde-fou : la ref ne doit contenir que des caractères sûrs (anti-injection SSH)
if [[ ! "$REF" =~ ^[A-Za-z0-9._/-]+$ ]]; then
    echo -e "${RED}Ref invalide: '$REF'${NC}"; exit 1
fi

echo ""
echo "============================================================"
echo -e "${BLUE}DÉPLOIEMENT PETROLEX${NC}"
echo "============================================================"
echo -e "  Serveur : ${YELLOW}${SERVER}:${REMOTE_PATH}${NC}"
echo -e "  Ref     : ${YELLOW}${REF}${NC}  (remote: ${GIT_REMOTE})"
echo -e "  Maintenance : $([ "$SKIP_MAINTENANCE" = true ] && echo "${CYAN}désactivée${NC}" || echo "${GREEN}activée${NC}")"
echo ""
read -p "Confirmer le déploiement ? (y/N) " -n 1 -r; echo ""
[[ $REPLY =~ ^[Yy]$ ]] || { echo -e "${RED}Annulé.${NC}"; exit 1; }

echo -e "${BLUE}» Déploiement en cours sur ${SERVER}...${NC}"

# REF est injecté dans l'environnement du shell distant ; le heredoc est quoté
# (aucune expansion locale) donc tout le reste s'exécute côté serveur.
ssh "$SERVER" "REF='$REF' REMOTE_PATH='$REMOTE_PATH' GIT_REMOTE='$GIT_REMOTE' SKIP_MAINTENANCE='$SKIP_MAINTENANCE' bash -s" <<'REMOTE'
set -e
cd "$REMOTE_PATH"

[ "$SKIP_MAINTENANCE" = true ] || php artisan down --retry=30 || true

echo "  → git fetch + reset --hard"
git fetch "$GIT_REMOTE" --tags --prune --quiet
# Cible = branche distante si elle existe, sinon tag/commit
TARGET="$(git rev-parse --verify --quiet "$GIT_REMOTE/$REF" || git rev-parse --verify --quiet "$REF")"
[ -n "$TARGET" ] || { echo "Ref introuvable: $REF"; php artisan up || true; exit 1; }
git reset --hard "$TARGET"

echo "  → composer + migrations"
composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -2
php artisan migrate --force

echo "  → ré-assertion des permissions (leçon session: l'app écrit dans resources/lang)"
# Fait en tant que propriétaire (isogaz-ptlx), pas besoin de sudo.
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true
if command -v setfacl >/dev/null; then
    setfacl -R -m g:www-data:rwX -d -m g:www-data:rwX resources/lang 2>/dev/null || true
fi

echo "  → caches"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache || php artisan route:clear
php artisan view:cache

echo "  → workers + OPcache"
php artisan queue:restart
sudo -n systemctl reload php8.2-fpm
sudo -n supervisorctl restart all >/dev/null

[ "$SKIP_MAINTENANCE" = true ] || php artisan up

echo "  ✓ HEAD déployé : $(git --no-pager log --oneline -1)"
REMOTE

echo ""
echo -e "${BLUE}» Health check...${NC}"
CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://isogaz.net" || echo "000")
if [[ "$CODE" =~ ^(200|301|302)$ ]]; then
    echo -e "${GREEN}✅ DÉPLOIEMENT TERMINÉ — site répond HTTP ${CODE}${NC}"
else
    echo -e "${RED}⚠️  Site répond HTTP ${CODE} — vérifie les logs :${NC}"
    echo "   ssh $SERVER 'tail -f $REMOTE_PATH/storage/logs/laravel.log'"
    exit 1
fi
echo ""
