FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache \
    nginx \
    supervisor \
    nodejs \
    npm \
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

# ── Dependencies ──────────────────────────────────────────────────────────────
FROM base AS deps

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY package.json ./
RUN npm install

# ── Build ─────────────────────────────────────────────────────────────────────
FROM deps AS build

COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && npm run build

# ── Production image ──────────────────────────────────────────────────────────
FROM base AS production

WORKDIR /var/www/html

COPY --from=build /var/www/html /var/www/html

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

RUN chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]