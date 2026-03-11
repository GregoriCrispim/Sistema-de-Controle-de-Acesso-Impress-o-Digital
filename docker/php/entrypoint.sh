#!/bin/bash

mkdir -p /var/www/storage/logs

if [ -f /var/www/artisan ]; then
    php artisan migrate --force
    php artisan storage:link 2>/dev/null || true
fi

php-fpm -D

exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf
