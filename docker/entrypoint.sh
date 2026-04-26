#!/bin/sh
set -e

echo "⏳ Waiting for MySQL..."
until mysqladmin ping -h"${DB_HOST:-mysql}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" --silent 2>/dev/null; do
    echo "  MySQL not ready, retrying in 3s..."
    sleep 3
done
echo "✅ MySQL ready."

echo "⏳ Running migrations..."
php artisan migrate --force

echo "⏳ Caching config & routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🚀 Starting services..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
