FROM webdevops/php:8.4

ENV WEB_DOCUMENT_ROOT=/var/www/public
ENV APP_ENV=production

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

# Run production installations
RUN composer install --no-dev --optimize-autoloader
RUN chmod -R 775 storage bootstrap/cache

# Run optimizations inside the build stage
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Change this to 8000 to match the webdevops runtime engine
EXPOSE 8000
