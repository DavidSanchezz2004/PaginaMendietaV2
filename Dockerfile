# ---------- Declarar ARGs para recibir variables de EasyPanel ----------
ARG APP_NAME=Laravel
ARG APP_ENV=production
ARG APP_KEY
ARG APP_DEBUG=false
ARG APP_URL=http://localhost
ARG APP_LOCALE=es
ARG APP_FALLBACK_LOCALE=es
ARG APP_FAKER_LOCALE=es_PE
ARG APP_MAINTENANCE_DRIVER=file
ARG BCRYPT_ROUNDS=12
ARG LOG_CHANNEL=stderr
ARG LOG_LEVEL=info
ARG DB_CONNECTION=mysql
ARG DB_HOST=127.0.0.1
ARG DB_PORT=3306
ARG DB_DATABASE=laravel
ARG DB_USERNAME=root
ARG DB_PASSWORD=
ARG CACHE_STORE=file
ARG SESSION_DRIVER=file
ARG SESSION_LIFETIME=120
ARG SESSION_ENCRYPT=false
ARG SESSION_PATH=/
ARG SESSION_DOMAIN=
ARG SESSION_SECURE_COOKIE=true
ARG SESSION_SAME_SITE=lax
ARG QUEUE_CONNECTION=sync
ARG BROADCAST_CONNECTION=log
ARG FILESYSTEM_DISK=local
ARG MAIL_MAILER=log
ARG MAIL_HOST=127.0.0.1
ARG MAIL_PORT=2525
ARG MAIL_FROM_ADDRESS=no-reply@example.com
ARG MAIL_FROM_NAME=Laravel
ARG VITE_APP_NAME=Laravel
ARG AQPFACT_TOKEN=
ARG FEASY_TOKEN=
ARG RECAPTCHA_SITE_KEY=
ARG RECAPTCHA_SECRET_KEY=
ARG RECAPTCHA_THRESHOLD=0.5
ARG SUNAT_BOT_URL=
ARG SUNAT_API_KEY=
ARG OPENAI_API_KEY=
ARG OPENAI_MODEL=gpt-4o-mini
ARG GIT_SHA=

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

# Redeclarar ARGs en esta etapa (requerido en multi-stage builds)
ARG APP_NAME=Laravel
ARG APP_ENV=production
ARG APP_KEY
ARG APP_DEBUG=false
ARG APP_URL=http://localhost
ARG APP_LOCALE=es
ARG APP_FALLBACK_LOCALE=es
ARG APP_FAKER_LOCALE=es_PE
ARG APP_MAINTENANCE_DRIVER=file
ARG BCRYPT_ROUNDS=12
ARG LOG_CHANNEL=stderr
ARG LOG_LEVEL=info
ARG DB_CONNECTION=mysql
ARG DB_HOST=127.0.0.1
ARG DB_PORT=3306
ARG DB_DATABASE=laravel
ARG DB_USERNAME=root
ARG DB_PASSWORD=
ARG CACHE_STORE=file
ARG SESSION_DRIVER=file
ARG SESSION_LIFETIME=120
ARG SESSION_ENCRYPT=false
ARG SESSION_PATH=/
ARG SESSION_DOMAIN=
ARG SESSION_SECURE_COOKIE=true
ARG SESSION_SAME_SITE=lax
ARG QUEUE_CONNECTION=sync
ARG BROADCAST_CONNECTION=log
ARG FILESYSTEM_DISK=local
ARG MAIL_MAILER=log
ARG MAIL_HOST=127.0.0.1
ARG MAIL_PORT=2525
ARG MAIL_FROM_ADDRESS=no-reply@example.com
ARG MAIL_FROM_NAME=Laravel
ARG VITE_APP_NAME=Laravel
ARG AQPFACT_TOKEN=
ARG FEASY_TOKEN=
ARG RECAPTCHA_SITE_KEY=
ARG RECAPTCHA_SECRET_KEY=
ARG RECAPTCHA_THRESHOLD=0.5
ARG SUNAT_BOT_URL=
ARG SUNAT_API_KEY=
ARG OPENAI_API_KEY=
ARG OPENAI_MODEL=gpt-4o-mini
ARG GIT_SHA=

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

# Generar .env desde los build-args
RUN printf "APP_NAME=%s\n\
APP_ENV=%s\n\
APP_KEY=%s\n\
APP_DEBUG=%s\n\
APP_URL=%s\n\
APP_LOCALE=%s\n\
APP_FALLBACK_LOCALE=%s\n\
APP_FAKER_LOCALE=%s\n\
APP_MAINTENANCE_DRIVER=%s\n\
BCRYPT_ROUNDS=%s\n\
LOG_CHANNEL=%s\n\
LOG_LEVEL=%s\n\
DB_CONNECTION=%s\n\
DB_HOST=%s\n\
DB_PORT=%s\n\
DB_DATABASE=%s\n\
DB_USERNAME=%s\n\
DB_PASSWORD=%s\n\
CACHE_STORE=%s\n\
SESSION_DRIVER=%s\n\
SESSION_LIFETIME=%s\n\
SESSION_ENCRYPT=%s\n\
SESSION_PATH=%s\n\
SESSION_DOMAIN=%s\n\
SESSION_SECURE_COOKIE=%s\n\
SESSION_SAME_SITE=%s\n\
QUEUE_CONNECTION=%s\n\
BROADCAST_CONNECTION=%s\n\
FILESYSTEM_DISK=%s\n\
MAIL_MAILER=%s\n\
MAIL_HOST=%s\n\
MAIL_PORT=%s\n\
MAIL_FROM_ADDRESS=%s\n\
MAIL_FROM_NAME=%s\n\
VITE_APP_NAME=%s\n\
AQPFACT_TOKEN=%s\n\
FEASY_TOKEN=%s\n\
RECAPTCHA_SITE_KEY=%s\n\
RECAPTCHA_SECRET_KEY=%s\n\
RECAPTCHA_THRESHOLD=%s\n\
SUNAT_BOT_URL=%s\n\
SUNAT_API_KEY=%s\n\
OPENAI_API_KEY=%s\n\
OPENAI_MODEL=%s\n" \
  "$APP_NAME" "$APP_ENV" "$APP_KEY" "$APP_DEBUG" "$APP_URL" \
  "$APP_LOCALE" "$APP_FALLBACK_LOCALE" "$APP_FAKER_LOCALE" "$APP_MAINTENANCE_DRIVER" "$BCRYPT_ROUNDS" \
  "$LOG_CHANNEL" "$LOG_LEVEL" \
  "$DB_CONNECTION" "$DB_HOST" "$DB_PORT" "$DB_DATABASE" "$DB_USERNAME" "$DB_PASSWORD" \
  "$CACHE_STORE" "$SESSION_DRIVER" "$SESSION_LIFETIME" "$SESSION_ENCRYPT" "$SESSION_PATH" \
  "$SESSION_DOMAIN" "$SESSION_SECURE_COOKIE" "$SESSION_SAME_SITE" \
  "$QUEUE_CONNECTION" "$BROADCAST_CONNECTION" "$FILESYSTEM_DISK" \
  "$MAIL_MAILER" "$MAIL_HOST" "$MAIL_PORT" "$MAIL_FROM_ADDRESS" "$MAIL_FROM_NAME" "$VITE_APP_NAME" \
  "$AQPFACT_TOKEN" "$FEASY_TOKEN" \
  "$RECAPTCHA_SITE_KEY" "$RECAPTCHA_SECRET_KEY" "$RECAPTCHA_THRESHOLD" \
  "$SUNAT_BOT_URL" "$SUNAT_API_KEY" \
  "$OPENAI_API_KEY" "$OPENAI_MODEL" \
  > .env

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

# Caches de Laravel (sin conexión a DB, solo config y rutas)
RUN php artisan config:cache || true \
 && php artisan route:cache || true \
 && php artisan view:cache || true

EXPOSE 8080
CMD ["bash", "-lc", "php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
