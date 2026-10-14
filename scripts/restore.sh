#!/usr/bin/env bash
# Restaura un backup generado por scripts/backup.sh.
#
#   scripts/restore.sh backups/db_2026-10-02_220000.sql.gz [backups/archivos_2026-10-02_220000.tar.gz]
#   scripts/restore.sh storage/backups/db_2026-10-02_220000.sql.gz   (backups automáticos)
#
# Los backups cifrados (.cif) se descifran antes, en el contenedor: pide la clave privada (sin eco)
# o la toma de BACKUP_CLAVE_PRIVADA. La clave no se guarda en ningún lado.
#
# ATENCIÓN: reemplaza los datos actuales de la base (y los archivos, si se indican).
set -euo pipefail
umask 077

cd "$(dirname "$0")/.."
DB="${1:?Indicá el archivo db_*.sql.gz a restaurar}"
ARCHIVOS="${2:-}"

# Backups cifrados: se descifran a una carpeta temporal (solo del usuario) que se borra al salir.
if [[ "$DB" == *.cif || "$ARCHIVOS" == *.cif ]]; then
  if [ -z "${BACKUP_CLAVE_PRIVADA:-}" ]; then
    read -r -s -p "Clave privada de backups: " BACKUP_CLAVE_PRIVADA; echo
  fi
  export BACKUP_CLAVE_PRIVADA
  TMP="$(mktemp -d)"; trap 'rm -rf "$TMP"' EXIT
  descifrar() {
    docker compose exec -T -e BACKUP_CLAVE_PRIVADA public php bin/backup.php descifrar - - < "$1" > "$TMP/$(basename "${1%.cif}")" \
      || { echo "No se pudo descifrar $1." >&2; exit 1; }
    echo "$TMP/$(basename "${1%.cif}")"
  }
  [[ "$DB" == *.cif ]] && DB="$(descifrar "$DB")"
  [[ "$ARCHIVOS" == *.cif ]] && ARCHIVOS="$(descifrar "$ARCHIVOS")"
fi

# Del archivo de archivos se restauran solo los que guardan los backups (storage/config y
# storage/uploads), sin enlaces, sin carpetas y sin "..": un archivo adulterado no puede pisar el
# código ni escribir fuera de storage/. Se revisa antes de tocar la base.
miembros=()
if [ -n "$ARCHIVOS" ]; then
  # grep -c lee todo el listado (con -q, tar moriría por SIGPIPE y pipefail lo daría por bueno).
  if [ "$(tar -tvzf "$ARCHIVOS" | grep -c '^[lh]')" -gt 0 ]; then
    echo "$ARCHIVOS trae enlaces: no se restaura nada." >&2; exit 1
  fi
  mapfile -t miembros < <(tar -tzf "$ARCHIVOS" | grep -E '^storage/(config|uploads)/[A-Za-z0-9._/-]*[^/]$' | grep -vF '..')
fi

read -r -p "Se van a reemplazar los datos actuales con $DB. ¿Continuar? (escribí SI) " respuesta
[ "$respuesta" = "SI" ] || { echo "Cancelado."; exit 1; }

gunzip -c "$DB" | docker compose exec -T database sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot "$MYSQL_DATABASE"'
echo "Base restaurada."

if [ "${#miembros[@]}" -gt 0 ]; then
  # Se extrae dentro del contenedor: storage/ es de www-data y el usuario del servidor no lo
  # puede pisar. Después, todo vuelve a www-data, que es quien lo usa.
  docker compose exec -T public sh -c 'tar -xzf - --no-same-owner --overwrite -- "$@" && chown -R www-data:www-data storage' tar "${miembros[@]}" < "$ARCHIVOS"
  echo "Archivos restaurados (${#miembros[@]})."
fi

# Por si el backup es de una versión anterior del sistema.
docker compose exec -T public php bin/migrate.php
