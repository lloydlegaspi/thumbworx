FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev nginx supervisor && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application code
COPY . .

# Install dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Create storage directories and set permissions
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chmod -R 755 storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

# Copy environment file if it doesn't exist
RUN php -r "file_exists('.env') || copy('.env.example', '.env');"

# Configure nginx
RUN rm /etc/nginx/sites-enabled/default
COPY docker/nginx.conf /etc/nginx/sites-enabled/laravel

# Configure supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Configure PHP-FPM
RUN sed -i 's/listen = 127.0.0.1:9000/listen = \/var\/run\/php\/php8.2-fpm.sock/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/;listen.owner = www-data/listen.owner = www-data/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/;listen.group = www-data/listen.group = www-data/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/;listen.mode = 0660/listen.mode = 0660/' /usr/local/etc/php-fpm.d/www.conf

# Create PHP-FPM socket directory
RUN mkdir -p /var/run/php && chown www-data:www-data /var/run/php

EXPOSE 80

CMD ["sh", "-c", "php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan migrate --force && supervisord -c /etc/supervisor/conf.d/supervisord.conf"]