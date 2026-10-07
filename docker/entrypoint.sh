#!/bin/sh
set -e

# Las cachés dependen de las variables de entorno, por eso se generan al
# arrancar y no al construir la imagen.
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize

exec docker-php-entrypoint "$@"
