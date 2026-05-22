#!/bin/sh

# 1. Run migrations first so all database tables exist
php artisan migrate --force

# 2. Clear and optimize application cache safely
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Correct production boot command for webdevops images
exec /entrypoint.sh supervisord
