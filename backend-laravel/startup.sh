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

# Run migrations
php artisan migrate --force || echo "Migration failed"

echo "✅ Laravel setup complete, starting server..."

# Start the application
exec "$@"
