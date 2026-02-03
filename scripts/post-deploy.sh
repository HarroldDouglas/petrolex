#!/bin/bash

# Post-deployment script for production
# Run this after every git pull

echo "🚀 Post-deployment script started..."

# Fix ownership
echo "📁 Fixing ownership..."
chown -R www-data:www-data /var/www/isogaz

# Fix permissions
echo "🔒 Fixing permissions..."
chmod -R 755 /var/www/isogaz
chmod -R 775 /var/www/isogaz/storage
chmod -R 775 /var/www/isogaz/bootstrap/cache
chmod -R 775 /var/www/isogaz/resources/lang

# Clear caches
echo "🧹 Clearing caches..."
cd /var/www/isogaz
php artisan optimize:clear
php artisan config:cache
php artisan route:cache

# Restart queue workers
echo "🔄 Restarting queue workers..."
supervisorctl restart isogaz-worker:*

# Restart reverb if running
echo "🔄 Restarting reverb..."
supervisorctl restart isogaz-reverb 2>/dev/null || true

echo "✅ Post-deployment completed successfully!"
