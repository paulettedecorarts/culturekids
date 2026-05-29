#!/bin/bash

# 0. System Dependencies Check
echo "⏳ Checking system dependencies..."

# Check and install Imagick (required for PDF panel extraction)
if ! php -m | grep -q imagick; then
    echo "⚠️  php8.3-imagick not found. Installing..."
    sudo apt-get install -y php8.3-imagick imagemagick ghostscript > /dev/null 2>&1
    echo "✅ Imagick installed."
else
    echo "✅ Imagick is available."
fi

# Ensure ImageMagick PDF policy allows read/write
POLICY_FILE="/etc/ImageMagick-6/policy.xml"
if [ -f "$POLICY_FILE" ]; then
    if grep -q 'pattern="PDF"' "$POLICY_FILE" && grep -q 'rights="none"' "$POLICY_FILE"; then
        echo "⚠️  ImageMagick PDF policy is restricted. Fixing..."
        sudo sed -i 's/<policy domain="coder" rights="none" pattern="PDF" \/>/<policy domain="coder" rights="read|write" pattern="PDF" \/>/' "$POLICY_FILE"
        echo "✅ ImageMagick PDF policy updated."
    fi
fi

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

echo "⏳ Running seeders (idempotent backfills)..."
php artisan db:seed --class=SyncComicsToActivitiesSeeder --force

# 5. Start Queue Worker in background
echo "⚙️ Starting Queue Worker (background)..."
php artisan queue:work --queue=image-processing,default,media-processing,pdf-extraction --tries=2 --timeout=960 &
QUEUE_PID=$!

# Trap Ctrl+C to kill the background queue worker
trap "echo -e '\n🛑 Stopping services...'; kill $QUEUE_PID; exit" SIGINT SIGTERM

# 7. Start Web Server
echo "🚀 Starting Web Server on all interfaces (0.0.0.0)..."
IP_ADDR=$(hostname -I | awk '{print $1}')
echo "📡 Server accessible at: http://$IP_ADDR:8000"

php -d upload_max_filesize=8192M -d post_max_size=8192M -d max_file_uploads=50 -d max_execution_time=0 -d max_input_time=600 -d memory_limit=512M artisan serve --host=0.0.0.0 --port=8000
