#!/bin/sh
set -e

if ! getent ahostsv4 "${DB_HOST:-mysql}" >/dev/null 2>&1; then
    echo "❌ Cannot resolve hostname '${DB_HOST:-mysql}'."
    echo "   Deploy the full stack (app + mysql + redis)."
    exit 1
fi

php /var/www/html/docker/wait-for-mysql.php || exit 1

mkdir -p \
    storage/app/livewire-tmp \
    storage/app/public \
    storage/logs \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true
touch storage/logs/laravel.log storage/logs/uploads.log 2>/dev/null || true
chown www-data:www-data storage/logs/*.log 2>/dev/null || true

echo "[1/4] Running migrations..."
php artisan migrate --force && echo "      ✅ Migrations done." || { echo "      ❌ Migrations failed."; exit 1; }

if [ "${RUN_DB_SEED:-false}" = "true" ]; then
    echo "[2/4] Seeding database (RUN_DB_SEED=true — can take several minutes)..."
    echo "      ⚠️  Set RUN_DB_SEED=false in Coolify after first deploy to avoid 504 timeouts."
    php artisan db:seed --force && echo "      ✅ Seeding done." || { echo "      ❌ Seeding failed."; exit 1; }
else
    echo "[2/4] Skipping seed (RUN_DB_SEED is not true)."
fi

echo "[3/4] Linking storage..."
php artisan storage:link --force 2>/dev/null && echo "      ✅ Storage linked." || echo "      ⚠️  Storage link skipped."

echo "[4/4] Application cache (fast path)..."
php artisan package:discover --ansi && echo "      ✅ Packages discovered." || { echo "      ❌ Package discover failed."; exit 1; }
php artisan config:cache && echo "      ✅ Config cached." || { echo "      ❌ Config cache failed."; exit 1; }

# Slow steps — off by default. Nginx does not listen until this script finishes; enabling these causes 504s behind Cloudflare/Coolify.
if [ "${RUN_VIEW_CACHE:-false}" = "true" ]; then
    php artisan view:cache && echo "      ✅ Views cached." || echo "      ⚠️  View cache skipped."
fi

if [ "${RUN_ROUTE_CACHE:-false}" = "true" ]; then
    php artisan route:cache && echo "      ✅ Routes cached." || echo "      ⚠️  Route cache skipped."
fi

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

echo "🚀 Starting nginx + php-fpm + queue worker..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
