#!/bin/sh
set -e

wait_for_mysql() {
    host="${DB_HOST:-mysql}"
    port="${DB_PORT:-3306}"
    user="${DB_USERNAME:-paulette}"
    pass="${DB_PASSWORD:?DB_PASSWORD is not set}"

    echo "Waiting for MySQL at ${host}:${port}..."
    attempt=0
    max_attempts=60

    while [ "$attempt" -lt "$max_attempts" ]; do
        if php -r "
            try {
                new PDO(
                    'mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306'),
                    getenv('DB_USERNAME'),
                    getenv('DB_PASSWORD')
                );
                exit(0);
            } catch (Throwable \$e) {
                exit(1);
            }
        " 2>/dev/null; then
            echo "      ✅ MySQL is ready."
            return 0
        fi
        attempt=$((attempt + 1))
        sleep 2
    done

    echo "      ❌ MySQL not reachable after ${max_attempts} attempts."
    exit 1
}

wait_for_mysql

echo "[1/5] Running migrations..."
php artisan migrate --force && echo "      ✅ Migrations done." || { echo "      ❌ Migrations failed."; exit 1; }

if [ "${RUN_DB_SEED:-false}" = "true" ]; then
    echo "[2/5] Seeding database (RUN_DB_SEED=true)..."
    php artisan db:seed --force && echo "      ✅ Seeding done." || { echo "      ❌ Seeding failed."; exit 1; }
else
    echo "[2/5] Skipping seed (set RUN_DB_SEED=true on first deploy only)."
fi

echo "[3/5] Linking storage..."
php artisan storage:link --force && echo "      ✅ Storage linked." || echo "      ⚠️  Storage link skipped."

echo "[4/5] Clearing all caches..."
php artisan config:clear && echo "      ✅ Config cleared."
php artisan route:clear && echo "      ✅ Routes cleared."
php artisan view:clear && echo "      ✅ Views cleared."
php artisan cache:clear && echo "      ✅ Cache cleared."
php artisan event:clear && echo "      ✅ Events cleared."

echo "[5/5] Rebuilding caches..."
php artisan config:cache && echo "      ✅ Config cached." || { echo "      ❌ Config cache failed."; exit 1; }
php artisan route:cache && echo "      ✅ Routes cached." || echo "      ⚠️  Route cache skipped (duplicate route name detected)."
php artisan view:cache && echo "      ✅ Views cached." || echo "      ⚠️  View cache skipped."

echo "🚀 Starting nginx + php-fpm + queue worker..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
