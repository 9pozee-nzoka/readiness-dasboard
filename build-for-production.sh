#!/bin/bash

# Build script for production deployment to Truehost
# This creates a production-ready package to upload

set -e

echo "=========================================="
echo "St. Mary IMC Readiness Dashboard"
echo "Production Build Script"
echo "=========================================="
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Error: artisan file not found!${NC}"
    echo "Please run this script from the Laravel project root."
    exit 1
fi

echo -e "${BLUE}📦 Preparing production build...${NC}"
echo ""

# Install production dependencies
echo -e "${YELLOW}1. Installing production dependencies...${NC}"
if [ -d "vendor" ]; then
    echo "Vendor directory exists, running composer update..."
    composer update --optimize-autoloader --no-dev --prefer-dist
else
    echo "Installing fresh dependencies..."
    composer install --optimize-autoloader --no-dev --prefer-dist
fi
echo -e "${GREEN}✅ Composer dependencies ready${NC}"
echo ""

# Check if node_modules exists
echo -e "${YELLOW}2. Building frontend assets...${NC}"
if [ ! -d "node_modules" ]; then
    echo "Node modules not found. Installing..."
    npm install
fi

# Build for production
echo "Running production build..."
npm run build

if [ ! -d "public/build" ]; then
    echo -e "${RED}❌ Build failed! public/build directory not created.${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Frontend assets built${NC}"
echo ""

# Clean up storage and cache
echo -e "${YELLOW}3. Cleaning temporary files...${NC}"
rm -rf storage/framework/cache/*
rm -rf storage/framework/sessions/*
rm -rf storage/framework/views/*
rm -rf storage/logs/*.log
echo -e "${GREEN}✅ Temporary files cleaned${NC}"
echo ""

# Create deployment package
echo -e "${YELLOW}4. Creating deployment package...${NC}"
PACKAGE_NAME="stmary-dashboard-$(date +%Y%m%d-%H%M%S).tar.gz"

tar -czf "$PACKAGE_NAME" \
    --exclude=node_modules \
    --exclude=.git \
    --exclude=.env \
    --exclude=.env.local \
    --exclude=.env.backup \
    --exclude=storage/logs/*.log \
    --exclude=storage/framework/cache/* \
    --exclude=storage/framework/sessions/* \
    --exclude=storage/framework/views/* \
    --exclude=tests \
    --exclude=.github \
    --exclude=.vscode \
    --exclude=.idea \
    --exclude=.claude \
    --exclude=.agents \
    --exclude=.kiro \
    --exclude=.phpunit.cache \
    --exclude=.DS_Store \
    --exclude=*.log \
    .

PACKAGE_SIZE=$(du -h "$PACKAGE_NAME" | cut -f1)
echo -e "${GREEN}✅ Package created: $PACKAGE_NAME ($PACKAGE_SIZE)${NC}"
echo ""

# Restore dev dependencies
echo -e "${YELLOW}5. Restoring development environment...${NC}"
composer install
echo -e "${GREEN}✅ Development dependencies restored${NC}"
echo ""

# Summary
echo "=========================================="
echo -e "${GREEN}🎉 Build Complete!${NC}"
echo "=========================================="
echo ""
echo -e "${BLUE}📦 Package:${NC} $PACKAGE_NAME"
echo -e "${BLUE}📏 Size:${NC} $PACKAGE_SIZE"
echo ""
echo "=========================================="
echo "Next Steps:"
echo "=========================================="
echo "1. Upload '$PACKAGE_NAME' to Truehost"
echo "2. Extract to /home/stmaryim/laravel-app/"
echo "3. Follow TRUEHOST-SETUP.md guide"
echo ""
echo "Quick commands after upload:"
echo "  cd /home/stmaryim/laravel-app"
echo "  cp .env.production .env"
echo "  # Edit .env with your credentials"
echo "  php artisan key:generate --force"
echo "  php artisan migrate --force"
echo "  php artisan db:seed --force"
echo "  php artisan storage:link"
echo "  php artisan config:cache"
echo ""
echo "=========================================="
echo -e "${GREEN}✅ Ready for deployment!${NC}"
echo "=========================================="
