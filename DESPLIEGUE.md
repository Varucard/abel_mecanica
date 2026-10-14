# Despliegue en producción

Cómo está instalado el sistema en el servidor y cómo se opera. Para levantarlo en una PC
de desarrollo, ver la *Puesta en marcha* del [README](README.md).

> El repositorio es **público**: acá no van claves, contraseñas ni el contenido del `.env`.
> Los pendientes que exponen puntos débiles del servidor van en `PENDIENTES.local.md`, que
> no se versiona.

**Estado:** en producción desde el 2026-10-05, con base vacía.

## Dónde está

| | |
| --- | --- |
| Dirección | <https://taller.pcfighter.com.ar> |
| Servidor | VPS Debian 13 · 2 núcleos · 3,8 GB de RAM · 2 GB de swap |
| DNS | FreeDNS (afraid.org): registro A `taller` → IP de la VPS |
| Certificado | Let's Encrypt con `certbot --apache`, se renueva solo |

La VPS **es compartida**: en el host corren el sitio WordPress de un colegio (Apache) y su
base MariaDB. El sistema del taller vive aparte, en Docker, y no comparte nada con ellos más
que el Apache que hace de proxy.

```mermaid
flowchart LR
  N[Navegador] -->|443 HTTPS| AP[Apache del host<br/>vhosts taller y pcfighter]
  AP -->|127.0.0.1:8050| PUB
  subgraph PUB[contenedor public · Apache + PHP]
    TA[vhost por defecto<br/>sistema del taller]
    PCF[vhost pcfighter.conf<br/>sitio de PC Fighter]
  end
  TA --> DB[(contenedor database<br/>MySQL 8.4)]
  TAR[contenedor tareas<br/>cada 15 min] --> DB
  AP -.otros vhosts.- WP[WordPress del colegio<br/>+ MariaDB del host]
```

El contenedor `public` sirve además el **sitio institucional de PC Fighter**
(`www.pcfighter.com.ar`), como un segundo vhost: ver [*Sitio de PC Fighter*](#sitio-de-pc-fighter).

## Usuario y carpetas

Todo el proyecto pertenece al usuario de sistema **`abel`**:

- Sin contraseña (bloqueada) y sin `sudo`; no se puede entrar con él por SSH. Se usa desde
  el usuario administrador con `sudo -iu abel`.
- Está en el grupo `docker`, lo que en la práctica equivale a root: separa archivos y
  responsabilidades, no es una barrera de seguridad.
- Home en `/opt/abel` con permisos `750`. **Esto es lo que protege los datos**: el
  contenedor guarda `storage/` (fotos, configuración y backups con el volcado de la base)
  como `www-data`, que en el host es el usuario del Apache del colegio. Con `750`, el
  `www-data` del host no puede ni entrar a la carpeta.
- Código en `/opt/abel/abel_mecanica`, clonado por HTTPS desde `main`.
- Al lado, `/opt/abel/pc-fighter`: el sitio de PC Fighter. Ese repo es privado y se clona por
  SSH con una *deploy key* de solo lectura (`~/.ssh/pcfighter_deploy`, alias
  `github-pcfighter` en `~/.ssh/config`).

**No ponerle `umask 027` al usuario**: el contenedor lee el código como `www-data` y, con
ese umask, Apache daría 403. La protección la da `/opt/abel`; adentro, permisos normales.

## Contenedores

Se levantan tres de los cuatro servicios del `docker-compose.yml`:

| Servicio | Qué es | En producción |
| --- | --- | --- |
| `database` | MySQL 8.4 | ✅ siempre, en `127.0.0.1:8807` |
| `public` | La aplicación (Apache + PHP) | ✅ siempre, en `127.0.0.1:8050` |
| `tareas` | Recordatorios, avisos y backup diario | ✅ siempre |
| `admin` | phpMyAdmin | ❌ solo cuando hace falta |

Los tres puertos escuchan solo en `127.0.0.1`: desde internet se entra únicamente por el
Apache del host, con HTTPS. Docker abre los puertos publicados por encima de `ufw`, así que
esto no se puede delegar en el firewall.

Docker se instaló desde el repositorio oficial, con rotación de logs en
`/etc/docker/daemon.json` (`max-size` 10m, `max-file` 3) para que no llenen el disco.

## `.env` de producción

Se armó desde `.env.example` con `chmod 600`. Diferencias con el ejemplo:

| Variable | Valor |
| --- | --- |
| `APP_URL` | `https://taller.pcfighter.com.ar` |
| `TRUSTED_PROXIES` | `172.16.0.0/12` — el Apache del host llega al contenedor desde el gateway de la red de Docker |
| `DB_PASSWORD`, `MYSQL_ROOT_PASSWORD` | Generadas al azar con `openssl rand -hex 24` |
| `MAIL_DSN`, `MAIL_FROM` | Cuenta de Gmail con contraseña de aplicación |

`DB_PORT` queda en `3306`: la aplicación se conecta a MySQL por la red interna de Docker. El
`8807` es solo el puerto publicado en el host.

## Proxy de Apache

`/etc/apache2/sites-available/taller.conf` (módulos `proxy`, `proxy_http` y `headers`):

```apache
<VirtualHost *:80>
    ServerName taller.pcfighter.com.ar
    ProxyPreserveHost On
    RequestHeader set X-Forwarded-Proto expr=%{REQUEST_SCHEME}
    ProxyPass        / http://127.0.0.1:8050/
    ProxyPassReverse / http://127.0.0.1:8050/
</VirtualHost>
```

`certbot --apache` generó `taller-le-ssl.conf` y la redirección de HTTP a HTTPS.

`/etc/apache2/sites-available/pcfighter.conf` es igual, con `ServerName www.pcfighter.com.ar`,
`ServerAlias pcfighter.com.ar` y el **mismo** `127.0.0.1:8050`; su certificado cubre los dos
nombres. `ProxyPreserveHost On` es imprescindible: es lo que le deja al contenedor elegir el
vhost por el nombre. Sin eso, todo cae en el sistema del taller.

**No apuntar otros nombres del dominio a esta VPS sin su propio vhost**: Apache le sirve a
un nombre desconocido el primer sitio que tiene, que es el del colegio.

## Sitio de PC Fighter

Desde el 2026-10-14 el sitio de PC Fighter corre **dentro del contenedor `public`**, sin
contenedor propio, para no cargar la VPS con un servidor aparte. Es PHP y manda las consultas
del formulario con el mismo mecanismo que el sistema (Symfony Mailer, desde este `vendor/`).

Lo engancha `~/abel_mecanica/docker-compose.override.yml`, que no se versiona y Compose toma
solo en cada `docker compose up`. Sale de `docker-compose.override.ejemplo.yml` del repo de PC
Fighter y hace tres cosas sobre `public`:

- monta `../pc-fighter` en `/var/www/pcfighter`, de solo lectura;
- monta su `apache/pcfighter.conf` en `sites-enabled/`, el vhost de `www.pcfighter.com.ar`;
- agrega `host.docker.internal`, para su consulta de seguimiento.

**Si el override se pierde, el sitio de PC Fighter cae y el sistema sigue andando**: cualquier
nombre que el contenedor no reconoce va al vhost por defecto, que es el del taller. Si hace
falta otro montaje (por ejemplo, la copia de backups de *Pendientes*), se suma a ese mismo
archivo: no se crea otro.

El sitio tiene su propio `.env` en `/opt/abel/pc-fighter`, con `644`: lo lee el `www-data` del
contenedor, y no queda expuesto porque `/opt/abel` es `750`. Lo que deja vacío lo hereda del
`.env` de este sistema; así, las consultas salen por la misma cuenta de email.

**Actualizarlo** no requiere reiniciar nada (el código está montado y PHP lee cada pedido):

```bash
cd ~/pc-fighter && git pull
```

Solo si cambian su `apache/pcfighter.conf` o el override: `docker compose up -d public`, desde
`~/abel_mecanica`. Los errores del sitio aparecen en `docker compose logs public` con el prefijo
`PC Fighter:`.

```bash
curl -s -o /dev/null -w '%{http_code}\n' https://www.pcfighter.com.ar/   # 200 = OK
```

## Operación

Todos los comandos, como `abel` y parado en `~/abel_mecanica` (`sudo -iu abel`, después
`cd ~/abel_mecanica`).

**Actualizar** después de subir cambios a `main` (las migraciones se aplican solas y la
versión del pie llega con el `pull`, desde el archivo `VERSION`):

```bash
git pull
docker compose up -d --build database public tareas
```

`APP_URL` en el `.env` tiene que ser la dirección pública (`https://taller.pcfighter.com.ar`): con ella
se arman los links de los emails y de los mensajes de WhatsApp (aceptar el presupuesto, seguir el
auto). Con otra, al cliente le llega un link que no abre.

Si el `pull` trae un `composer.lock` nuevo (se agregó o actualizó una librería, como la de los
códigos QR en la 1.8), el contenedor `public` corre `composer install` solo al arrancar. El
`--build` hace falta cuando cambian `Dockerfile`, `docker/entrypoint.sh` o `docker/php.ini`.

**Estado y logs:**

```bash
docker compose ps
docker compose logs public --tail 50
```

**phpMyAdmin**, por túnel SSH desde la PC:

```bash
docker compose up -d admin            # en la VPS
ssh -L 8051:localhost:8051 <usuario>@taller.pcfighter.com.ar   # en la PC → http://localhost:8051
docker compose stop admin             # al terminar
```

**Contraseña olvidada del administrador:**

```bash
docker compose exec public php bin/usuario.php clave <usuario>
```

**Probar que responde** (un `HEAD` con `curl -I` también sirve: se atiende como `GET`):

```bash
curl -s -o /dev/null -w '%{http_code}\n' https://taller.pcfighter.com.ar/   # 303 = OK
```

## Pendientes

| # | Qué | Prioridad |
| --- | --- | --- |
| 1 | **Copia de los backups fuera de la VPS.** Hoy quedan solo en `storage/backups`, en el mismo disco: si se pierde el servidor, se pierde todo junto. El mecanismo ya existe (`BACKUP_COPIA_DIR` + `docker-compose.override.yml`, ver *Backups* en el README); falta decidir el destino (Drive con rclone, la PC del taller, otra VPS). El override ya existe por el sitio de PC Fighter: el montaje se suma ahí. | Alta |
| 2 | **Cuenta de email propia del taller.** Hoy los avisos salen de una cuenta personal de Gmail: los clientes ven ese remitente y Gmail limita a unos 500 envíos por día. | Media |
| 3 | **Monitor de disponibilidad** externo (UptimeRobot y similares). El router ya responde `HEAD`. | Baja |
