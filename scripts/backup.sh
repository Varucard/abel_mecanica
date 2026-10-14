#!/usr/bin/env bash
# Backup de la base de datos y de los archivos subidos (fotos, configuración).
#
#   scripts/backup.sh            guarda en backups/ y conserva los últimos 14
#   KEEP=30 scripts/backup.sh    cambia la cantidad de backups que se conservan
#
# Con BACKUP_CLAVE_PUBLICA en .env, los backups quedan cifrados (.cif), igual que los automáticos.
#
# Para hacerlo todos los días a las 22 h (crontab -e en el servidor):
#   0 22 * * * cd /ruta/al/proyecto && scripts/backup.sh >> backups/backup.log 2>&1
set -euo pipefail
# Los backups tienen todos los datos de clientes y los hashes de las claves: solo para el dueño.
umask 077

cd "$(dirname "$0")/.."
KEEP="${KEEP:-14}"
DESTINO="backups"
FECHA="$(date +%Y-%m-%d_%H%M%S)"
mkdir -p "$DESTINO"

# Cifra lo que entra por la entrada estándar si hay clave pública (lo hace PHP en el contenedor).
EXT=""
if docker compose exec -T public sh -c 'test -n "$BACKUP_CLAVE_PUBLICA"'; then EXT=".cif"; fi
cifrar() { if [ -n "$EXT" ]; then docker compose exec -T public php bin/backup.php cifrar - -; else cat; fi; }

echo "[$(date '+%F %T')] Backup de la base..."
docker compose exec -T database sh -c \
  'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysqldump -uroot --single-transaction --routines --no-tablespaces "$MYSQL_DATABASE"' \
  | gzip | cifrar > "$DESTINO/db_$FECHA.sql.gz$EXT"

echo "[$(date '+%F %T')] Backup de archivos..."
{ tar -cz --ignore-failed-read storage/config storage/uploads 2>/dev/null || true; } | cifrar > "$DESTINO/archivos_$FECHA.tar.gz$EXT"

# Rotación: conserva los últimos $KEEP de cada tipo.
shopt -s nullglob
for tipo in db archivos; do
  lista=("$DESTINO"/${tipo}_*.gz "$DESTINO"/${tipo}_*.gz.cif)
  if [ "${#lista[@]}" -gt 0 ]; then
    ls -1t "${lista[@]}" | tail -n +"$((KEEP + 1))" | xargs -r rm --
  fi
done

echo "[$(date '+%F %T')] Listo: $DESTINO/db_$FECHA.sql.gz$EXT y $DESTINO/archivos_$FECHA.tar.gz$EXT"
