#!/bin/sh
set -e

echo "🚀 Starting API Inspeccion..."

# Create .env file if it doesn't exist
touch /var/www/html/.env

# Load .env variables into shell (EasyPanel writes env vars here)
# Use line-by-line export to handle special characters (# in passwords, etc.)
if [ -s /var/www/html/.env ]; then
    while IFS= read -r line || [ -n "$line" ]; do
        case "$line" in \#*|"") continue ;; esac
        export "$line" 2>/dev/null || true
    done < /var/www/html/.env
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
    echo "⏳ Waiting for MySQL at ${DB_HOST}:${DB_PORT:-3306} (user: ${DB_USERNAME})..."
    MAX_RETRIES=30
    RETRY=0
    while [ $RETRY -lt $MAX_RETRIES ]; do
        if php -r "try { new PDO('mysql:host='.getenv('DB_HOST').';port='.(getenv('DB_PORT')?:'3306'), getenv('DB_USERNAME')?:'root', getenv('DB_PASSWORD')?:''); echo 'ok'; exit(0); } catch(Exception \$e) { echo \$e->getMessage().PHP_EOL; exit(1); }"; then break; fi
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

# Ensure composer is available and install dompdf if missing
echo "📦 Checking dependencies..."
if [ ! -f /usr/local/bin/composer ]; then
    php -r "copy('https://getcomposer.org/installer', '/tmp/composer-setup.php');"
    php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
    rm /tmp/composer-setup.php
fi

# Force install dompdf if not present (Docker build cache may have skipped it)
if [ ! -d /var/www/html/vendor/barryvdh/laravel-dompdf ]; then
    echo "📦 Installing barryvdh/laravel-dompdf..."
    cd /var/www/html
    composer require barryvdh/laravel-dompdf --no-interaction --optimize-autoloader
else
    echo "✅ dompdf already installed"
fi

# Cache routes and views (NOT config - env vars come from EasyPanel)
echo "⚡ Caching routes and views..."
php artisan route:cache
php artisan view:cache

# Run database migrations and seed
echo "🗄️ Running migrations..."
php artisan migrate --force
echo "🌱 Seeding database..."
php artisan db:seed --force

echo "✅ Application ready! Starting services..."

# Start Supervisor (manages PHP-FPM + Nginx)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
