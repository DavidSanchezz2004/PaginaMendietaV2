# ---------- Frontend build (Node 22) ----------
FROM node:22-bookworm AS nodebuild
WORKDIR /app

# Cache npm deps first
COPY package*.json ./
RUN npm ci || npm install

# Build Vite assets
COPY . .
RUN npm run build


# ---------- PHP runtime (Laravel) ----------
FROM php:8.3-cli AS app
WORKDIR /app

# System deps + required PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip zip libzip-dev \
  && docker-php-ext-install pdo_mysql zip \
  && rm -rf /var/lib/apt/lists/*

# Composer binary
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# App source
COPY . .

# Built assets from node stage
COPY --from=nodebuild /app/public/build /app/public/build

# PHP dependencies (production)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Laravel runtime dirs + permissions
RUN mkdir -p storage/framework/views \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# Optional caches (do not fail build)
RUN php artisan config:cache || true \
 && php artisan route:cache || true \
 && php artisan view:cache || true

EXPOSE 8080
CMD ["bash", "-lc", "php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
