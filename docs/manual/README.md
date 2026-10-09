# Manual de usuario

Fuente del `Manual_de_Usuario_Mecanica_Abel.pdf` de la raíz del proyecto.

| Archivo | Qué es |
| --- | --- |
| `manual.html` | El texto del manual (HTML para imprimir en A4). Se edita acá. |
| `capturas/` | Las capturas de pantalla que usa el manual. |
| `semilla.php` | Datos de ejemplo inventados (clientes, autos, órdenes, turnos, stock) para las capturas. |
| `capturar.js` | Saca las capturas desde una copia de prueba con esos datos. |
| `generar-pdf.js` | Arma el PDF a partir de `manual.html`. |

## Actualizar el manual

Si solo cambia el texto: editar `manual.html` y generar el PDF (paso 4).

Si cambiaron pantallas, rehacer también las capturas, **siempre sobre una base descartable**,
nunca la del taller:

1. Crear una base vacía y levantar una copia de la aplicación apuntando a ella, con el envío
   de emails apagado:

   ```bash
   P=$(docker compose exec -T database printenv MYSQL_ROOT_PASSWORD | tr -d '\r')
   docker compose exec -T database sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e "CREATE DATABASE taller_manual CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"'
   docker compose run -d --rm --no-deps -p 127.0.0.1:8099:80 -e DB_DATABASE=taller_manual \
     -e DB_USERNAME=root -e DB_PASSWORD="$P" -e MAIL_DSN=null://null --name abel_manual public
   ```

2. Entrar a <http://127.0.0.1:8099>, crear el administrador (usuario `abel`) y cargar los datos
   de ejemplo:

   ```bash
   docker exec abel_manual php /var/www/html/docs/manual/semilla.php
   ```

3. Sacar las capturas (hace falta Google Chrome y `playwright-core`, por ejemplo con
   `npm install playwright-core` en una carpeta aparte y `NODE_PATH` apuntando a su `node_modules`):

   ```bash
   BASE=http://127.0.0.1:8099 USUARIO=abel CLAVE=<la del paso 2> node docs/manual/capturar.js
   ```

4. Generar el PDF:

   ```bash
   node docs/manual/generar-pdf.js
   ```

5. Apagar la copia y borrar la base: `docker stop abel_manual` y `DROP DATABASE taller_manual`.

> La copia de prueba comparte `storage/` con la aplicación (configuración del taller, fotos,
> backups): las capturas muestran los datos reales del taller (nombre, dirección, CUIT). No
> subir imágenes ni guardar configuración desde la copia.
