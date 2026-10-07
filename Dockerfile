# Imagen de producción: FrankenPHP (Caddy + PHP en un solo proceso, HTTPS automático).
FROM dunglas/frankenphp:1-php8.4-alpine

RUN install-php-extensions pdo_pgsql intl zip gd bcmath

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php.ini $PHP_INI_DIR/conf.d/zz-app.ini
COPY docker/entrypoint.sh /usr/local/bin/app-entrypoint

WORKDIR /app

# Dependencias primero para aprovechar la caché de capas.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --ansi \
    && php artisan filament:assets

# Usuario sin privilegios; setcap le permite escuchar en 80/443.
RUN apk add --no-cache libcap \
    && adduser -D app \
    && setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp \
    && chmod +x /usr/local/bin/app-entrypoint \
    && chown -R app:app /app/storage /app/bootstrap/cache /data/caddy /config/caddy

USER app

ENTRYPOINT ["app-entrypoint"]
CMD ["--config", "/etc/frankenphp/Caddyfile", "--adapter", "caddyfile"]
