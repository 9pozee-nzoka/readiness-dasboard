#!/bin/bash

# Deployment script for St. Mary IMC Readiness Dashboard
# Usage: bash deploy.sh

set -e

echo "=========================================="
echo "St. Mary IMC Readiness Dashboard"
echo "Production Deployment Script"
echo "=========================================="
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${YELLOW}⚠️  .env file not found!${NC}"
    echo "Copying .env.production to .env..."
    cp .env.production .env
    echo -e "${GREEN}✅ .env file created. Please edit it with your production values.${NC}"
    echo ""
    exit 1
fi

echo -e "${GREEN}✅ .env file found${NC}"
echo ""

# Check PHP version
echo "Checking PHP version..."
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "Current PHP version: $PHP_VERSION"

if php -r "exit(version_compare(PHP_VERSION, '8.2.0', '<') ? 1 : 0);"; then
    echo -e "${RED}❌ PHP 8.2 or higher is required!${NC}"
    exit 1
fi
echo -e "${GREEN}✅ PHP version is compatible${NC}"
echo ""

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo -e "${RED}❌ Composer is not installed!${NC}"
    echo "Please install composer first: https://getcomposer.org/download/"
    exit 1
fi
echo -e "${GREEN}✅ Composer found${NC}"
echo ""

# Install dependencies (skip if in production)
if [ ! -d "vendor" ]; then
    echo "Installing PHP dependencies..."
    composer install --optimize-autoloader --no-dev
    echo -e "${GREEN}✅ Dependencies installed${NC}"
else
    echo -e "${GREEN}✅ Vendor directory exists${NC}"
fi
echo ""

# Generate application key if not set
APP_KEY=$(grep "^APP_KEY=" .env | cut -d '=' -f2)
if [ -z "$APP_KEY" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
    echo -e "${GREEN}✅ Application key generated${NC}"
else
    echo -e "${GREEN}✅ Application key already set${NC}"
fi
echo ""

# Set permissions
echo "Setting proper permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/app storage/framework storage/logs
echo -e "${GREEN}✅ Permissions set${NC}"
echo ""

# Create storage directories if they don't exist
echo "Ensuring storage directories exist..."
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
echo -e "${GREEN}✅ Storage directories ready${NC}"
echo ""

# Run database migrations
echo "Running database migrations..."
read -p "This will run migrations on the database. Continue? (y/n) " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force
    echo -e "${GREEN}✅ Migrations completed${NC}"
else
    echo -e "${YELLOW}⚠️  Skipped migrations${NC}"
fi
echo ""

# Seed database
read -p "Do you want to seed the database? (y/n) " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Seeding database..."
    php artisan db:seed --force
    echo -e "${GREEN}✅ Database seeded${NC}"
else
    echo -e "${YELLOW}⚠️  Skipped database seeding${NC}"
fi
echo ""

# Link storage
echo "Linking storage directory..."
php artisan storage:link
echo -e "${GREEN}✅ Storage linked${NC}"
echo ""

# Clear and cache configuration
echo "Optimizing application..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✅ Application optimized${NC}"
echo ""

# Check if queue table exists
echo "Checking queue configuration..."
php artisan queue:failed-table 2>/dev/null || true
echo -e "${GREEN}✅ Queue configured${NC}"
echo ""

# Final checks
echo "=========================================="
echo "Deployment Summary"
echo "=========================================="
echo ""

APP_URL=$(grep "^APP_URL=" .env | cut -d '=' -f2)
APP_ENV=$(grep "^APP_ENV=" .env | cut -d '=' -f2)
DB_DATABASE=$(grep "^DB_DATABASE=" .env | cut -d '=' -f2)

echo "Environment: $APP_ENV"
echo "Application URL: $APP_URL"
echo "Database: $DB_DATABASE"
echo ""

echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
echo ""
echo "=========================================="
echo "Next Steps:"
echo "=========================================="
echo "1. Test your website: $APP_URL"
echo "2. Login with admin credentials from .env"
echo "3. Set up cron jobs for Laravel scheduler"
echo "4. Set up queue worker for email processing"
echo "5. Enable HTTPS/SSL certificate"
echo ""
echo "Cron jobs to add in cPanel:"
echo "* * * * * cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1"
echo "* * * * * cd $(pwd) && php artisan queue:work --stop-when-empty >> /dev/null 2>&1"
echo ""
echo "For detailed instructions, see DEPLOYMENT.md"
echo "=========================================="
