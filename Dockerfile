FROM php:8.3-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    sqlite \
    sqlite-dev \
    bash \
    libzip-dev \
    zip \
    unzip \
    git

# Install PHP extensions for Laravel & SQLite
RUN docker-php-ext-install pdo pdo_sqlite zip bcmath

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy application code
COPY . .

# Install composer packages
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set up permissions
RUN mkdir -p storage/framework/views \
             storage/framework/cache/data \
             storage/framework/sessions \
             storage/logs \
             bootstrap/cache \
             database \
    && chmod -R 777 storage bootstrap/cache database

ENV PORT=10000
EXPOSE 10000

# Start Laravel
CMD sh -c "touch database/database.sqlite && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"
