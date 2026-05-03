#!/bin/sh
set -e

# Fix permissions dynamically as root
if [ "$(id -u)" = '0' ]; then
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
fi

# Auto-generate .env for local testing if completely missing
if [ ! -f ".env" ]; then
    echo "📄 .env missing, copying from .env.example..."
    cp .env.example .env
    echo "🔑 Generating app key..."
    php artisan key:generate --force || echo "⚠️ Failed to generate key, check if php is working."
    chown www-data:www-data .env
fi

# Auto-install composer dependencies if missing
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "📦 Vendor dependencies missing! Running composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader || echo "⚠️ Composer install failed."
fi

# Clear any cached config
echo "🧹 Clearing Laravel cache..."
if [ "$(id -u)" = '0' ]; then
    su-exec www-data php artisan config:clear || echo "⚠️ config:clear failed (likely DB connection issue)"
    su-exec www-data php artisan cache:clear || echo "⚠️ cache:clear failed"
else
    php artisan config:clear || echo "⚠️ config:clear failed"
    php artisan cache:clear || echo "⚠️ cache:clear failed"
fi

echo "🚀 Starting process: $@"

# Verify php-fpm can start
if [ "$1" = 'php-fpm' ]; then
    echo "ℹ️ php-fpm will listen on port 9000"
    exec "$@"
else
    # For other commands (like artisan), run as www-data
    if [ "$(id -u)" = '0' ]; then
        exec su-exec www-data "$@"
    else
        exec "$@"
    fi
fi
