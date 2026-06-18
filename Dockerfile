FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache \
    nginx \
    supervisor \
    git \
    curl \
    zip \
    unzip \
    libpng \
    libjpeg-turbo \
    freetype \
    libzip \
    oniguruma \
    ghostscript \
    imagemagick

RUN apk add --no-cache --virtual .php-build-deps \
    $PHPIZE_DEPS \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    imagemagick-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        zip \
        gd \
        bcmath \
        pcntl \
    && pecl install imagick redis \
    && docker-php-ext-enable imagick redis \
    && apk del .php-build-deps \
    && rm -rf /tmp/pear

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ── Frontend assets (glibc Node — Vite 8 / Rolldown native bindings) ─────────
FROM node:22-bookworm-slim AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
# Coolify/build hosts often set NODE_ENV=production; postcss/tailwind live in devDependencies.
ENV NPM_CONFIG_PRODUCTION=false
RUN npm ci

COPY vite.config.js postcss.config.js tailwind.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm run build

# ── PHP dependencies ──────────────────────────────────────────────────────────
FROM base AS deps

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# ── Application build ─────────────────────────────────────────────────────────
FROM deps AS build

COPY . .
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer dump-autoload --optimize --no-dev --no-scripts
COPY --from=frontend /app/public/build ./public/build

# ── Production image ──────────────────────────────────────────────────────────
FROM base AS production

WORKDIR /var/www/html

COPY --from=build /var/www/html /var/www/html

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

RUN mkdir -p /tmp/nginx/client_body /tmp/nginx/fastcgi /tmp/nginx/proxy \
    && chown -R www-data:www-data /tmp/nginx \
    && chmod -R 770 /tmp/nginx

RUN chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]