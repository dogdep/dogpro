#!/bin/bash
set -e

if [ -f /var/www/composer.json ] && [ ! -d /var/www/vendor ]; then
    echo "[info] Running composer"
    composer install --no-dev --optimize-autoloader --working-dir=/var/www
fi

echo "[info] generating key"
php artisan key:generate

echo "[info] Changing permissions for storage/"
chmod -R 777 /var/www/storage/framework /var/www/storage/logs /var/www/storage/app

echo "[info] Waiting for mysql"
sleep 10

echo "[info] Migrating database"
php /var/www/artisan migrate || true

echo "Run: $@"
exec "$@"
