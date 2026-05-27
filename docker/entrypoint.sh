#!/bin/sh
set -e

cd /var/www/html

# Sync public/ contents into the shared volume (volume persists across rebuilds,
# so we refresh it from the image on every start to pick up new builds/assets).
# Clear public/build first so stale hashed Vite assets don't accumulate.
if [ -d /var/www/html/public-src ]; then
    rm -rf /var/www/html/public/build
    cp -a /var/www/html/public-src/. /var/www/html/public/
fi

# Ensure storage and bootstrap/cache dirs exist (volume may be fresh)
mkdir -p storage/framework/cache storage/framework/sessions \
         storage/framework/views storage/logs bootstrap/cache

# APP_KEY must be provided via env (e.g. in .env loaded by compose `env_file`).
# Fail fast if missing rather than silently running insecure.
if [ -z "${APP_KEY:-}" ]; then
    echo "ERROR: APP_KEY is not set. Generate one with 'php artisan key:generate --show' and add to .env" >&2
    exit 1
fi

# Refuse to run with APP_DEBUG=true in production (leaks stack traces + env).
if [ "${APP_ENV:-}" = "production" ] && [ "${APP_DEBUG:-false}" = "true" ]; then
    echo "ERROR: APP_DEBUG must be false when APP_ENV=production" >&2
    exit 1
fi

# Cache config / routes / views / events for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Run database migrations
php artisan migrate --force --no-interaction

# Fix ownership/perms after the artisan commands above (entrypoint runs as
# root, so files written by artisan are root-owned; php-fpm workers run as
# www-data and need write access for sessions, cache, logs, etc.)
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

# Hand off to the main process (php-fpm by default)
exec "$@"
