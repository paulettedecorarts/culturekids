#!/bin/sh
set -e

echo "⏳ Waiting for MySQL..."
until php artisan db:monitor --databases=mysql > /dev/null 2>&1; do
    sleep 2
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
