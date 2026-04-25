#!/bin/bash

# 1. Environment Check
if [ ! -f .env ]; then
    echo "❌ .env file not found!"
    exit 1
fi

# 2. Check MySQL
echo "⏳ Checking MySQL connection..."
php artisan db:monitor --databases=mysql > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo "❌ Cannot connect to MySQL. Is the service running?"
    exit 1
fi
echo "✅ MySQL is ready."

# 3. Check Redis
echo "⏳ Checking Redis connection..."
# Use PHP to check Redis connection based on .env values using Predis
php -r "
require 'vendor/autoload.php';
\$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
\$dotenv->safeLoad();

try {
    \$host = \$_ENV['REDIS_HOST'] ?? '127.0.0.1';
    \$port = \$_ENV['REDIS_PORT'] ?? 6379;
    \$pass = \$_ENV['REDIS_PASSWORD'] ?? null;
    
    \$client = new Predis\Client([
        'scheme' => 'tcp',
        'host'   => \$host,
        'port'   => (int)\$port,
        'password' => \$pass,
    ]);
    \$client->ping();
    exit(0);
} catch (Exception \$e) {
    exit(1);
}
"
if [ $? -ne 0 ]; then
    echo "❌ Cannot connect to Redis. Is the service running?"
    exit 1
fi
echo "✅ Redis is ready."

# 4. Database Setup
echo "⏳ Running migrations..."
php artisan migrate --force

# 5. Run All Services
echo "🚀 Starting all services..."
# We use npx which is standard for running local binaries in Node projects
npx concurrently --kill-others \
  "php -d upload_max_filesize=8192M -d post_max_size=8192M -d max_file_uploads=50 -d max_execution_time=0 -d max_input_time=600 -d memory_limit=512M artisan serve" \
  "npm run dev" \
  "php artisan queue:work --queue=default,media-processing,pdf-extraction,image-processing --tries=3 --timeout=300"
