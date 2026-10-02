#!/bin/sh
set -e

# Instala dependencias de Composer si todavía no están (el código se monta como volumen).
if [ ! -f vendor/autoload.php ]; then
  composer install --no-interaction --prefer-dist
fi

# Apache necesita poder escribir la configuración del taller y los logs.
mkdir -p storage/config storage/logs
chown -R www-data:www-data storage

# Aplica las migraciones pendientes (reintenta mientras la base termina de iniciar).
php bin/migrate.php 15 || echo "AVISO: no se pudieron aplicar las migraciones." >&2

exec docker-php-entrypoint "$@"
