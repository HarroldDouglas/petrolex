#!/bin/bash

# Simple production deployment script
# Usage: ./scripts/deploy-production.sh

echo "🚀 Déploiement en production..."
echo ""

# Connect to production server and deploy
ssh root@157.173.104.21 << 'ENDSSH'
cd /var/www/isogaz

echo "📥 Git pull..."
git pull origin dev

echo ""
echo "🔧 Post-deployment tasks..."
bash scripts/post-deploy.sh

echo ""
echo "✅ Déploiement terminé avec succès!"
ENDSSH
