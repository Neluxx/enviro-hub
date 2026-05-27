# syntax=docker/dockerfile:1

# Stage 1: Build frontend assets with Vite
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci
# Copy the rest of the project; .dockerignore keeps this small
COPY . .
RUN npm run build

# Stage 2: Install PHP dependencies with Composer
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    --no-progress

# Stage 3: Final production image (PHP-FPM)
FROM php:8.3-fpm-alpine AS app

# Install runtime libs + build deps for PHP extensions, then drop build deps
RUN apk add --no-cache \
        tini icu-libs libzip \
    && apk add --no-cache --virtual .build-deps \
        icu-dev libzip-dev \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql intl zip opcache pcntl \
    && apk del .build-deps

# Production PHP config + opcache
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'expose_php=Off'; \
    } > "$PHP_INI_DIR/conf.d/zz-app.ini"

WORKDIR /var/www/html

# Copy composer binary (needed for dump-autoload below)
COPY --from=vendor /usr/bin/composer /usr/bin/composer

# Copy application source
COPY --chown=www-data:www-data . .

# Pull in built frontend assets and vendor dependencies
COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor

# Optimize autoloader (now with full app source), prepare writable dirs,
# stash pristine public/ for entrypoint to refresh into the shared volume
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev \
    && mkdir -p storage/framework/cache storage/framework/sessions \
                storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache \
    && cp -a public public-src \
    && chown -R www-data:www-data public-src \
    && rm /usr/bin/composer

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/sbin/tini", "--", "/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
