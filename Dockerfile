FROM php:8.4-fpm-alpine

# ── System dependencies ───────────────────────────────────────────
RUN apk add --no-cache \
    nginx \
    nodejs \
    npm \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    postgresql-dev \
    $PHPIZE_DEPS

# ── PHP extensions ────────────────────────────────────────────────
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        opcache

# ── Composer ──────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ── App source ────────────────────────────────────────────────────
WORKDIR /var/www/html
COPY . .

# ── PHP dependencies ──────────────────────────────────────────────
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist \
    --no-scripts

# ── Frontend build (Vite) ─────────────────────────────────────────
RUN npm ci && npm run build && rm -rf node_modules

# ── Laravel permissions ───────────────────────────────────────────
RUN mkdir -p storage/framework/{sessions,views,cache} \
             storage/logs \
             bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# ── Cache config/routes/views (avoid migrate here) ────────────────
RUN php artisan package:discover --ansi || true
RUN php artisan config:cache || true
RUN php artisan route:cache || true
RUN php artisan view:cache || true

# ── Nginx config ──────────────────────────────────────────────────
COPY docker/nginx.conf /etc/nginx/nginx.conf

# ── PHP-FPM config ────────────────────────────────────────────────
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# ── Startup script ────────────────────────────────────────────────
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080

CMD ["/start.sh"]