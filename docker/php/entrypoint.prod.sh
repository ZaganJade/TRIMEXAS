#!/bin/sh
set -e

mkdir -p \
  storage/app/public \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

if [ ! -f public/storage ]; then
  php artisan storage:link --force >/dev/null 2>&1 || true
fi

php artisan package:discover --ansi >/dev/null 2>&1 || true
php artisan config:cache --ansi >/dev/null 2>&1 || true
php artisan route:cache --ansi >/dev/null 2>&1 || true
php artisan view:cache --ansi >/dev/null 2>&1 || true

exec "$@"
