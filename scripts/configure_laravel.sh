#!/bin/bash
cd /var/www/html
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan queue:restart
