FROM php:8.2-cli

# system deps
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# copy code
COPY . .

# install deps (no dev to speed up; add --dev if needed)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader || true

# generate key if not present
RUN php -r "file_exists('.env') || copy('.env.example', '.env');" \
 && php artisan key:generate --force || true

EXPOSE 80
CMD ["sh", "-c", "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=80"]