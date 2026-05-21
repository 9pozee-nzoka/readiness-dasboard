#!/bin/sh
set -e

cd /var/www/html

# ── Generate app key if not provided ──────────────────────────────
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# ── Write .env from Render environment variables ──────────────────
# Order matters: write .env BEFORE config:cache so config/admin.php
# picks up ADMIN_EMAIL / ADMIN_PASSWORD via env().
cat > .env <<EOF
APP_NAME="${APP_NAME:-Readiness Dashboard}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-https://localhost}"
ASSET_URL="${APP_URL:-https://localhost}"

DB_CONNECTION="${DB_CONNECTION:-pgsql}"
DB_HOST="${DB_HOST}"
DB_PORT="${DB_PORT:-5432}"
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

ADMIN_EMAIL="${ADMIN_EMAIL}"
ADMIN_PASSWORD="${ADMIN_PASSWORD}"
ADMIN_NAME="${ADMIN_NAME:-System Admin}"
EOF

# ── Cache config / routes / views ─────────────────────────────────
# config:cache must run AFTER .env is written so config/admin.php
# reads the correct ADMIN_* values.
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ── Run migrations ────────────────────────────────────────────────
php artisan migrate --force --no-interaction

# ── Sync admin user from ADMIN_* env vars (runs every deploy) ─────
php artisan db:seed --class=Database\\Seeders\\AdminUserSeeder --force --no-interaction

# ── Seed event type requirement templates if empty ────────────────
php artisan tinker --execute "
if (App\Models\EventTypeRequirement::count() === 0) {
    Artisan::call('db:seed', ['--class' => 'EventTypeRequirementSeeder', '--force' => true]);
    echo 'Requirement templates seeded.' . PHP_EOL;
} else {
    echo 'Templates already exist — skipping.' . PHP_EOL;
}
"

# ── Fix permissions ───────────────────────────────────────────────
chown -R www-data:www-data storage bootstrap/cache

# ── Start PHP-FPM in background, Nginx in foreground ─────────────
php-fpm -D
exec nginx -g 'daemon off;'
