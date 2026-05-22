FROM webdevops/php:8.4

# Set production environment variables for the container
ENV WEB_DOCUMENT_ROOT=/var/www/public
ENV APP_ENV=production

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions for Laravel
RUN chmod -R 775 storage bootstrap/cache

# Optimize Laravel configuration caching during the build step
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Expose standard web traffic port
EXPOSE 80
