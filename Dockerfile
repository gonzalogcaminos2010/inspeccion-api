# ============================================
# Stage 1: Install PHP dependencies
# ============================================
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

COPY . .

RUN composer dump-autoload --optimize --no-dev

# ============================================
# Stage 2: Production image (PHP-FPM + Nginx)
# ============================================
FROM php:8.2-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    nginx \
    supervisor \
    sqlite \
    sqlite-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    libzip-dev \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_sqlite \
    pdo_mysql \
    gd \
    bcmath \
    zip \
    opcache \
    && rm -rf /var/cache/apk/*

# Configure OPcache for production
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=4000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

# Set working directory
WORKDIR /var/www/html

# Copy Nginx config
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Copy Supervisor config
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy application code
COPY --from=vendor /app/vendor ./vendor
COPY . .

# Remove dev files not needed in production
RUN rm -rf node_modules tests .env .env.example docker-compose* \
    storage/logs/*.log

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Copy and set entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
