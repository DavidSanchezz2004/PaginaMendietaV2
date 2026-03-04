# =========================
# 1) Composer dependencies
# =========================
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader
COPY . .
RUN composer dump-autoload --optimize

# =========================
# 2) Node build (Vite)
# =========================
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# =========================
# 3) Runtime: Nginx + PHP-FPM
# =========================
FROM php:8.3-fpm-bookworm

# System deps
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx supervisor git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libicu-dev libonig-dev \
  && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) \
    pdo_mysql mbstring bcmath intl zip gd opcache

WORKDIR /var/www/html

# Copy app (vendor + source)
COPY --from=vendor /app /var/www/html
# Copy built assets
COPY --from=assets /app/public/build /var/www/html/public/build

# Nginx + Supervisor config
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/site.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Permissions (storage + cache)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
 && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
