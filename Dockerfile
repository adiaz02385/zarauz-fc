# Referencia de contenedor para el despliegue web.
# Este starter todavía debe integrarse en una instalación Laravel completa.
FROM serversideup/php:8.4-fpm-nginx

WORKDIR /var/www/html

# El código de Laravel debe estar presente antes de construir esta imagen.
COPY --chown=www-data:www-data . /var/www/html

ENV HEALTHCHECK_PATH=/up
