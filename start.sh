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
php artisan cache:clear > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo "❌ Cannot connect to Redis/Cache. Is the service running?"
    exit 1
fi
echo "✅ Cache/Redis is ready."

# 4. Database Setup
echo "⏳ Running migrations..."
php artisan migrate --force

# 5. Start Queue Worker in background
echo "⚙️ Starting Queue Worker (background)..."
php artisan queue:work --queue=default,media-processing,pdf-extraction,image-processing --tries=3 --timeout=300 &
QUEUE_PID=$!

# Trap Ctrl+C to kill the background queue worker
trap "echo -e '\n🛑 Stopping services...'; kill $QUEUE_PID; exit" SIGINT SIGTERM

# 7. Start Web Server
echo "🚀 Starting Web Server..."
php -d upload_max_filesize=8192M -d post_max_size=8192M -d max_file_uploads=50 -d max_execution_time=0 -d max_input_time=600 -d memory_limit=512M artisan serve
