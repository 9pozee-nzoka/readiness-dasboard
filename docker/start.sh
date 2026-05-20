#!/bin/sh
set -e

cd /var/www/html

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Write .env from environment variables (Render injects them directly)
# Laravel reads env vars from the environment, so no .env file needed —
# but we create a minimal one so artisan commands work correctly.
cat > .env <<EOF
APP_NAME="${APP_NAME:-Readiness Dashboard}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-http://localhost}"

DB_CONNECTION=mysql
DB_HOST="${DB_HOST}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE}"
DB_USERNAME="${DB_USERNAME}"
DB_PASSWORD="${DB_PASSWORD}"

SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database

LOG_CHANNEL=stderr
LOG_LEVEL=error

FILESYSTEM_DISK=local
EOF

# Cache config for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations (--force required in production)
php artisan migrate --force --no-interaction

# Seed event type requirement templates if table is empty
php artisan tinker --execute "
if (App\Models\EventTypeRequirement::count() === 0) {
    Artisan::call('db:seed', ['--class' => 'EventTypeRequirementSeeder', '--force' => true]);
    echo 'Templates seeded.' . PHP_EOL;
}
"

# Fix permissions after cache generation
chown -R www-data:www-data storage bootstrap/cache

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground (keeps container alive)
exec nginx -g 'daemon off;'
