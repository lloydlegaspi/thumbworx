#!/bin/bash

# Railway startup script for Laravel
echo "🚀 Starting Laravel on Railway..."

# Ensure storage directories exist and are writable
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# Set permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache

# Clear any cached config that might have wrong values
php artisan config:clear || echo "Config clear failed"
php artisan route:clear || echo "Route clear failed"
php artisan view:clear || echo "View clear failed"
php artisan cache:clear || echo "Cache clear failed"

# Try to generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "⚠️ APP_KEY not set, generating one..."
    php artisan key:generate --force || echo "Key generation failed"
fi

# Cache configuration for better performance (after clearing)
php artisan config:cache || echo "Config cache failed"
php artisan route:cache || echo "Route cache failed"
php artisan view:cache || echo "View cache failed"

# Run migrations
php artisan migrate --force || echo "Migration failed"

echo "✅ Laravel setup complete, starting web server..."

# Check if we're running with nginx (production) or artisan serve (fallback)
if command -v nginx > /dev/null 2>&1; then
    echo "🌐 Starting with nginx + PHP-FPM (production mode)"
    exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
else
    echo "🔧 Falling back to artisan serve (development mode)"
    exec php artisan serve --host=0.0.0.0 --port=${PORT:-80}
fi
