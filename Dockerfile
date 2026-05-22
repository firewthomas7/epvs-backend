FROM webdevops/php:8.4

# Configure container variables for webdevops image
ENV WEB_DOCUMENT_ROOT=/var/www/public
ENV APP_ENV=production

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

# Run production installations
RUN composer install --no-dev --optimize-autoloader
RUN chmod -R 775 storage bootstrap/cache

# Run optimizations inside the build stage instead of runtime
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

EXPOSE 80
