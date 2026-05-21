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

echo "[1/5] Running migrations..."
php artisan migrate --force && echo "      ✅ Migrations done." || { echo "      ❌ Migrations failed."; exit 1; }

if [ "${RUN_DB_SEED:-false}" = "true" ]; then
    echo "[2/5] Seeding database (RUN_DB_SEED=true)..."
    php artisan db:seed --force && echo "      ✅ Seeding done." || { echo "      ❌ Seeding failed."; exit 1; }
else
    echo "[2/5] Skipping seed (set RUN_DB_SEED=true on first deploy only)."
fi

echo "[3/5] Linking storage..."
php artisan storage:link --force 2>/dev/null && echo "      ✅ Storage linked." || echo "      ⚠️  Storage link skipped."

echo "[4/5] Clearing caches..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

echo "[5/5] Rebuilding caches..."
php artisan livewire:clean-temp-uploads 2>/dev/null || true
php artisan package:discover --ansi && echo "      ✅ Packages discovered." || { echo "      ❌ Package discover failed."; exit 1; }
php artisan config:cache && echo "      ✅ Config cached." || { echo "      ❌ Config cache failed."; exit 1; }
php artisan route:cache && echo "      ✅ Routes cached." || echo "      ⚠️  Route cache skipped."
php artisan view:cache && echo "      ✅ Views cached." || echo "      ⚠️  View cache skipped."

# php-fpm runs as www-data — fix ownership if artisan above ran as root during build/exec
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

echo "🚀 Starting nginx + php-fpm + queue worker..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
