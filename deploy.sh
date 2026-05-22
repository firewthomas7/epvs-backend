#!/bin/sh

# Clear any cached configurations
php artisan config:clear
php artisan cache:clear

# Run database migrations and seeders safely at startup
php artisan migrate --force
php artisan db:seed --force

# Start the web server included in the webdevops image
exec /entrypoint supervisord
