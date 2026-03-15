#!/bin/sh
set -e

echo "🚀 Starting API Inspeccion..."

# Ensure storage directories exist with proper permissions
mkdir -p /var/www/html/storage/framework/{sessions,views,cache}
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create supervisor log directory
mkdir -p /var/log/supervisor

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Wait for MySQL to be ready (if using MySQL)
if [ "$DB_CONNECTION" = "mysql" ]; then
    echo "⏳ Waiting for MySQL at ${DB_HOST}:${DB_PORT:-3306}..."
    MAX_RETRIES=30
    RETRY=0
    while [ $RETRY -lt $MAX_RETRIES ]; do
        php -r "
            try {
                new PDO('mysql:host=${DB_HOST};port=${DB_PORT}', '${DB_USERNAME}', '${DB_PASSWORD}');
                echo 'connected';
                exit(0);
            } catch (Exception \$e) {
                exit(1);
            }
        " 2>/dev/null && break
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

# SQLite fallback
if [ "$DB_CONNECTION" = "sqlite" ]; then
    if [ ! -f /var/www/html/database/database.sqlite ]; then
        echo "📦 Creating SQLite database..."
        touch /var/www/html/database/database.sqlite
        chown www-data:www-data /var/www/html/database/database.sqlite
    fi
fi

# Cache configuration for performance
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

echo "✅ Application ready! Starting services..."

# Start Supervisor (manages PHP-FPM + Nginx)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
