FROM php:8.3-fpm-alpine AS base

# System deps
RUN apk add --no-cache \
    nginx \
    supervisor \
    nodejs \
    npm \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    ghostscript \
    imagemagick \
    imagemagick-dev \
    $PHPIZE_DEPS

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        zip \
        gd \
        bcmath \
        opcache \
        pcntl

RUN pecl install imagick redis \
    && docker-php-ext-enable imagick redis

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ── Dependencies ──────────────────────────────────────────────────────────────
FROM base AS deps

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY package.json package-lock.json* ./
RUN npm ci

# ── Build ─────────────────────────────────────────────────────────────────────
FROM deps AS build

COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && npm run build

# ── Production image ──────────────────────────────────────────────────────────
FROM base AS production

WORKDIR /var/www/html

COPY --from=build /var/www/html /var/www/html

# Nginx config
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Supervisor config (web + queue worker)
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# PHP config
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

# Entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
