#!/bin/sh
set -e

echo "🚀 Starting API Inspeccion..."

# Create SQLite database if using SQLite and file doesn't exist
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
    if [ ! -f /var/www/html/database/database.sqlite ]; then
        echo "📦 Creating SQLite database..."
        touch /var/www/html/database/database.sqlite
        chown www-data:www-data /var/www/html/database/database.sqlite
    fi
fi

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
