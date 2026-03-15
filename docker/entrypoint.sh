#!/bin/sh
set -e

echo "🚀 Starting API Inspeccion..."

# Create .env file if it doesn't exist
touch /var/www/html/.env

# Load .env variables into shell (EasyPanel writes env vars here)
if [ -s /var/www/html/.env ]; then
    export $(grep -v '^#' /var/www/html/.env | grep -v '^\s*$' | xargs) 2>/dev/null || true
fi

# Ensure storage directories exist with proper permissions
mkdir -p /var/www/html/storage/framework/{sessions,views,cache}
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create supervisor log directory
mkdir -p /var/log/supervisor

# Wait for MySQL to be ready
if [ "$DB_CONNECTION" = "mysql" ] && [ -n "$DB_HOST" ]; then
    echo "⏳ Waiting for MySQL at ${DB_HOST}:${DB_PORT:-3306}..."
    MAX_RETRIES=30
    RETRY=0
    while [ $RETRY -lt $MAX_RETRIES ]; do
        php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'ok'; } catch(\Exception \$e) { echo 'fail'; exit(1); }" 2>/dev/null && break
        RETRY=$((RETRY + 1))
        echo "  Retry $RETRY/$MAX_RETRIES..."
        sleep 2
    done
    if [ $RETRY -eq $MAX_RETRIES ]; then
        echo "❌ Could not connect to MySQL after $MAX_RETRIES attempts"
        exit 1
    fi
    echo "✅ MySQL is ready!"
fi

# Cache routes and views (NOT config - env vars come from EasyPanel)
echo "⚡ Caching routes and views..."
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

echo "✅ Application ready! Starting services..."

# Start Supervisor (manages PHP-FPM + Nginx)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
