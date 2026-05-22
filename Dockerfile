FROM webdevops/php:8.4

ENV WEB_DOCUMENT_ROOT=/var/www/public
ENV APP_ENV=production

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN chmod -R 775 storage bootstrap/cache

# Give execute permissions to the startup script
RUN chmod +x deploy.sh

EXPOSE 80

# Run the startup script when the container launches
CMD ["./deploy.sh"]
