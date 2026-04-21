#!/bin/sh

# Fix permissions dynamically if needed
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

# Auto-generate .env for local testing if completely missing
if [ ! -f ".env" ]; then
    echo "📄 .env missing, copying from .env.example..."
    cp .env.example .env
    php artisan key:generate --force
fi

# Auto-install composer dependencies if missing (happens when bound to empty local repo)
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "📦 Vendor dependencies missing! Running composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Auto-compile Vite assets if missing
if [ ! -d "public/build" ]; then
    echo "🎨 Frontend assets missing! Running npm install & build..."
    npm install
    npm run build
fi

# Clear any cached config that might refer to old database settings
php artisan config:clear
php artisan cache:clear

echo "🚀 Starting process: $@"
exec "$@"
