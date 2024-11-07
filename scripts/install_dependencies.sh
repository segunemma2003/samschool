#!/bin/bash
cd /var/www/html
composer install --no-interaction --prefer-dist --optimize-autoloader
npm install && npm run prod
