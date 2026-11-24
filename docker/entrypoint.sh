#!/bin/sh
set -e

if [ "$1" = "php-fpm" ]; then
    if [ -z "$APP_KEY" ]; then
        echo "APP_KEY is not set. Run: php artisan key:generate --show, then put it in .env" >&2
        exit 1
    fi

    php artisan storage:link --force >/dev/null 2>&1 || true
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan filament:cache-components || true

    if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
        php artisan migrate --force
    fi

    if [ "${SEED_DEMO_DATA:-false}" = "true" ] && [ "$(php artisan tinker --execute='echo \App\Models\Product::count();')" = "0" ]; then
        php artisan db:seed --force
    fi
fi

exec "$@"
