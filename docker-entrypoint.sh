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
    php artisan key:generate --force
    chown www-data:www-data .env
fi

# Auto-install composer dependencies if missing
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "📦 Vendor dependencies missing! Running composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Clear any cached config
if [ "$(id -u)" = '0' ]; then
    su-exec www-data php artisan config:clear
    su-exec www-data php artisan cache:clear
else
    php artisan config:clear
    php artisan cache:clear
fi

echo "🚀 Starting process: $@"

# If the command is php-fpm, run it as root (it will drop privileges itself)
if [ "$1" = 'php-fpm' ]; then
    exec "$@"
else
    # For other commands (like artisan), run as www-data
    if [ "$(id -u)" = '0' ]; then
        exec su-exec www-data "$@"
    else
        exec "$@"
    fi
fi
