#!/bin/sh
set -e

echo "[1/5] Running migrations..."
php artisan migrate --force && echo "      ✅ Migrations done." || { echo "      ❌ Migrations failed."; exit 1; }

echo "[2/5] Seeding database..."
php artisan db:seed --force && echo "      ✅ Seeding done." || { echo "      ❌ Seeding failed."; exit 1; }

echo "[3/5] Linking storage..."
php artisan storage:link && echo "      ✅ Storage linked." || echo "      ⚠️  Storage link skipped (already exists)."

echo "[4/5] Caching config..."
php artisan config:cache && echo "      ✅ Config cached." || { echo "      ❌ Config cache failed."; exit 1; }

echo "[5/6] Caching routes..."
php artisan route:cache && echo "      ✅ Routes cached." || echo "      ⚠️  Route cache skipped (duplicate route name detected)."

echo "[6/6] Caching views..."
php artisan view:cache && echo "      ✅ Views cached." || echo "      ⚠️  View cache skipped."

echo "🚀 Starting nginx + php-fpm + queue worker..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
