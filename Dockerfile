# Construye una instalación Laravel 12 limpia e integra el código del repositorio.
FROM composer:2 AS build

WORKDIR /app

RUN composer create-project laravel/laravel:^12.0 . --no-interaction --prefer-dist

COPY composer.json ./composer.json
COPY app ./app
COPY bootstrap/providers.php ./bootstrap/providers.php
COPY config/zarauz.php ./config/zarauz.php
COPY database/migrations ./database/migrations
COPY database/seeders ./database/seeders
COPY resources/views/public ./resources/views/public
COPY routes/web.php ./routes/web.php

# El composer.lock del esqueleto Laravel no corresponde al composer.json de esta app.
RUN rm -f composer.lock \
    && composer update --no-dev --prefer-dist --optimize-autoloader --no-interaction \
    && composer dump-autoload --no-dev --optimize

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# NGINX + PHP-FPM; Railway termina HTTPS en su edge y enruta al puerto 8080.
FROM serversideup/php:8.4-fpm-nginx

WORKDIR /var/www/html

ENV SSL_MODE=off
ENV PHP_OPCACHE_ENABLE=1
ENV HEALTHCHECK_PATH=/up

COPY --from=build --chown=www-data:www-data /app /var/www/html
