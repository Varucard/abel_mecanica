# Manual de usuario

Fuente del manual en sus dos formatos: la **Ayuda** dentro del sistema (`/ayuda`, ver
`App\Support\Manual`) y el `Manual_de_Usuario_Mecanica_Abel.pdf` de la raíz del proyecto.
Para la Ayuda alcanza con editar `manual.html`: el PDF hay que regenerarlo (paso 4). Desde
la Ayuda también se imprime, con **Imprimir o guardar en PDF**.

- **No cambiar los `id` de los `<section>`**: los botones de Ayuda de cada pantalla enlazan
  ahí. Un capítulo nuevo lleva su `<section id="…">`. Si explica una pantalla, se agrega en
  `Manual::DESTINOS`.
- **Cambiar el texto de un subtítulo (`<h3>`) le cambia el id** (`capitulo-texto-del-subtitulo`)
  y algunos son destino de un botón de Ayuda: si `ManualTest` falla, actualizar
  `Manual::DESTINOS`.
- **La versión de la portada no se escribe a mano**: la ponen `generar-pdf.js` y la Ayuda,
  desde el archivo `VERSION`. Las capturas salen **sin** la versión del pie (`capturar.js` la
  oculta), así no quedan viejas cada vez que sube la versión.

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
   mkdir -p /tmp/storage_manual   # storage/ propio: la copia no toca la configuración ni las fotos del taller
   docker compose run -d --rm --no-deps -p 127.0.0.1:8099:80 -v /tmp/storage_manual:/var/www/html/storage \
     -e DB_DATABASE=taller_manual -e DB_USERNAME=root -e DB_PASSWORD="$P" -e MAIL_DSN=null://null --name abel_manual public
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

> La copia de prueba usa su propio `storage/` (el `-v` del paso 1): lo que se sube o se configura
> ahí (fotos, firmas, encuestas, el modo mostrador) no llega a la aplicación. Por eso las capturas
> muestran los datos del taller por defecto (`config/taller.php`).
