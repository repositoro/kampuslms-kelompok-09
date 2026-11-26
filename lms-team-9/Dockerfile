FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY vite.config.js ./
COPY public ./public
RUN npm run build

FROM php:8.4-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libxml2-dev \
        libzip-dev \
        supervisor \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath exif gd intl mbstring pdo_mysql pcntl zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV APP_NAME=KampusLMS \
    APP_ENV=production \
    APP_DEBUG=false \
    SESSION_SECURE_COOKIE=true \
    SESSION_DRIVER=file \
    QUEUE_CONNECTION=database

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* \
    && a2enmod mpm_prefork rewrite \
    && sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY . .
COPY --from=frontend /app/public/build ./public/build
COPY docker/supervisord.conf /etc/supervisor/conf.d/kampuslms.conf
COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader \
    && mkdir -p storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8080

CMD ["sh", "-c", "set -eu; port=\"${PORT:-8080}\"; mkdir -p storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache; chown -R www-data:www-data storage bootstrap/cache; sed -ri \"s/^Listen .*/Listen ${port}/\" /etc/apache2/ports.conf; sed -ri \"s#<VirtualHost \\*:.*>#<VirtualHost *:${port}>#\" /etc/apache2/sites-available/000-default.conf; exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf"]