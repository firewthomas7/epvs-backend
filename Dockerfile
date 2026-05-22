FROM php:8.4-fpm-alpine

# Install system dependencies for PostgreSQL
RUN apk add --no-cache postgresql-dev linux-headers

# Install PHP extensions required for Laravel & PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www
COPY . .

# Install dependencies and set production permissions
RUN composer install --no-dev --optimize-autoloader
RUN chmod -R 775 storage bootstrap/cache

# Optimize Laravel configuration caching
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

EXPOSE 8000

# Run Laravel's server directly on port 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
