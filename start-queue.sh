#!/bin/sh
while true; do
    sudo php /var/www/html/samschool/artisan queue:work --sleep=3 --tries=3
    sleep 60
done
