# Etapa de build: Laravel 12 + Filament 5
FROM serversideup/php:8.4-cli AS build

USER root

RUN install-php-extensions intl pdo_pgsql

WORKDIR /app

RUN composer create-project laravel/laravel:^12.0 . --no-interaction --prefer-dist

COPY composer.json ./composer.json
COPY app ./app
COPY bootstrap/app.php ./bootstrap/app.php
COPY bootstrap/providers.php ./bootstrap/providers.php
COPY config/zarauz.php ./config/zarauz.php
COPY database/migrations ./database/migrations
COPY database/seeders ./database/seeders
COPY resources/views/public ./resources/views/public
COPY routes/web.php ./routes/web.php
COPY public/images ./public/images

RUN rm -f composer.lock \
    && composer update --no-dev --prefer-dist --optimize-autoloader --no-interaction \
    && composer dump-autoload --no-dev --optimize \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

FROM serversideup/php:8.4-fpm-nginx

USER root

RUN install-php-extensions intl pdo_pgsql

WORKDIR /var/www/html

ENV SSL_MODE=off
ENV PHP_OPCACHE_ENABLE=1
ENV HEALTHCHECK_PATH=/up

COPY --from=build --chown=www-data:www-data /app /var/www/html

RUN chown -R www-data:www-data storage bootstrap/cache

USER www-data
