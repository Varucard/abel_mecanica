# Mecánica Abel — Sistema de gestión de taller

Aplicación web en PHP para administrar un taller mecánico.

## Funcionalidades

- **Clientes** con ficha (foto, vehículos, historial de órdenes y turnos, deuda) y acceso directo a WhatsApp.
- **Vehículos** con motor, combustible, color, VIN, observaciones, galería de imágenes e historial.
- **Órdenes de servicio** con cantidades y precios por ítem, combos, mecánico asignado, km de ingreso,
  diagnóstico, trabajo realizado, notas internas y próximo service; presupuesto y comprobante de entrega
  (HTML imprimible y PDF). El presupuesto se **envía por email** y el cliente lo **acepta online**; si se
  modifica uno ya aceptado, se le avisa para que lo vuelva a aceptar. Al finalizar la orden, el cliente
  recibe el aviso de **vehículo listo** con el saldo a abonar.
- **Pagos** parciales por orden, saldos y listado de **deudores**.
- **Stock de repuestos**: precio de costo y margen, ingresos por proveedor, ajustes por conteo, descuento
  automático al finalizar órdenes, historial de movimientos y alerta de stock mínimo. **Proveedores**.
- **Actualización masiva de precios** por porcentaje, con redondeo y vista previa.
- **Turnos** según el horario de atención y los feriados, con cupos simultáneos configurables.
  Al agendar, el cliente recibe un **email para confirmar o cancelar** y, el día hábil anterior,
  un **recordatorio automático**.
- **Portal "Seguí tu vehículo"** (`/seguimiento`): el cliente consulta con DNI y patente el estado
  de sus trabajos y sus próximos turnos, sin necesidad de usuario. Desde el link del presupuesto
  entra directo, sin DNI ni patente.
- **Todo configurable** desde *Configuración > Sistema*: datos del taller, presupuestos, horario,
  feriados, textos de los mensajes, canales de aviso, stock y portal.
- **Panel de inicio** con la actividad del día, **agenda semanal** con cupos libres y **búsqueda rápida**.
- **Reportes** (cobranzas, más vendidos, por mecánico, stock valorizado) con exportación a Excel.
- **Aviso automático de próximo service** al cliente.
- **Auditoría** (quién hizo qué y cuándo) y **registro técnico** (logs) con visor.
- **Backup automático diario** de la base y las imágenes.
- **Usuarios** con roles (administrador / empleado) y **empleados** del taller.
- **App instalable (PWA)** en el celular o la PC, con barra de navegación inferior en pantallas chicas,
  tablas que pliegan sus columnas y aviso de "Sin conexión".

## Stack

| Componente | Versión |
| --- | --- |
| PHP + Apache | 8.4 (compatible con 8.2+) |
| MySQL | 8.4 LTS |
| phpMyAdmin | 5 |
| Dompdf (vía Composer) | 3.1 |
| Bootstrap · jQuery · DataTables (+ Responsive) · Tom Select | 5.3.8 · 3.7.1 · 2.3.8 (3.0.8) · 2.6.2 |
| Symfony Mailer | 7.4 |
| PHPUnit | 11.5 |

## Puesta en marcha

```bash
cp .env.example .env        # completar las claves
docker compose up -d --build
```

- Aplicación: <http://localhost:8050> (puertos configurables en `.env`)
- phpMyAdmin: <http://localhost:8051> (solo desde el propio servidor; MySQL también
  escucha solo en `127.0.0.1`. Para abrirlos a la red: `PMA_BIND` / `DB_BIND` en `.env`)

La imagen se construye para producción (`php.ini` de producción, sin Xdebug). Si el
sistema queda detrás de un proxy (nginx, Cloudflare), indicar sus IPs en
`TRUSTED_PROXIES` para que los bloqueos por intentos fallidos y la auditoría usen la
IP real del cliente.

Al iniciar, el contenedor `public` instala las dependencias (si faltan) y aplica
las migraciones pendientes. **La primera vez que entrás, la aplicación pide crear
el usuario administrador.**

Si se olvida la contraseña del administrador:

```bash
docker compose exec public php bin/usuario.php clave <usuario>
```

### Emails a clientes (confirmación y recordatorio de turnos)

Completar en `.env` el servidor SMTP y la dirección pública del sistema (se usa en
los links de los emails). Por ejemplo, con Gmail y una
[contraseña de aplicación](https://myaccount.google.com/apppasswords):

```
APP_URL=https://turnos.mitaller.com.ar
MAIL_DSN=smtp://usuario%40gmail.com:CLAVE_DE_APLICACION@smtp.gmail.com:587
MAIL_FROM=usuario@gmail.com
```

Sin `MAIL_DSN` no se envían emails (el resto funciona igual). El estado de cada
canal y el registro de avisos enviados están en *Configuración > Avisos*.

### Tareas automáticas

El servicio `tareas` de Docker ejecuta `bin/tareas.php` cada 15 minutos: envía los
recordatorios de turnos del día hábil siguiente y los avisos de próximo service
(solo en días hábiles, dentro del horario de atención y desde la hora configurada),
genera el backup diario y limpia registros y logs viejos. Sin Docker, agregar
al crontab: `*/15 * * * * cd /ruta/al/proyecto && php bin/tareas.php`.

### WhatsApp Business (preparado, no activo)

Los avisos pasan por canales intercambiables (`app/Notificaciones/`). Hoy está
activo el email; `WhatsAppCanal` ya tiene la interfaz y sus plantillas de mensaje.
Para activarlo hace falta una cuenta de WhatsApp Business verificada en Meta, un
número dedicado, plantillas aprobadas, las variables `WHATSAPP_TOKEN` y
`WHATSAPP_PHONE_ID`, e implementar el envío en `WhatsAppCanal::enviar()`.
Mientras tanto, el botón manual "WhatsApp" abre la conversación con el mensaje armado.

### Sin Docker

Requiere PHP 8.2+ con `pdo_mysql`, `gd`, `mbstring` y `dom`, más un MySQL accesible.

```bash
composer install
cp .env.example .env        # DB_HOST=127.0.0.1, etc.
composer migrate
php -S localhost:8000 -t public public/index.php
```

## Arquitectura

Patrón MVC liviano, sin framework, con separación estricta de responsabilidades:

```
app/
├── Core/           Infraestructura: App, Router, Container (autowiring), Request,
│                   View, Session (flash + CSRF), Auth, Logger, Migrator, Database, Env
├── Controllers/    Reciben la petición, llaman al servicio y renderizan. Sin SQL ni reglas.
├── Services/       Lógica de negocio y validaciones (precios congelados, disponibilidad
│                   de turnos, bajas protegidas, configuración, PDF).
├── Repositories/   Único lugar con SQL (PDO + sentencias preparadas).
├── Models/         Entidades del dominio (Persona → Cliente, Vehiculo, Orden, Turno…).
├── Enums/          Estados de órdenes, turnos y clientes/vehículos.
├── Notificaciones/ Canales de aviso (email activo, WhatsApp preparado).
├── Support/        Validator, ImageUpload, HorarioAtencion, ConsultaPaginada (DataTables en el servidor).
└── helpers.php     Funciones para vistas: e(), url(), asset(), money(), csrf_field()…
bin/                migrate.php (migraciones), tareas.php (recordatorios) y usuario.php (recuperar acceso).
config/
├── routes.php      Todas las rutas de la aplicación y su nivel de acceso.
└── taller.php      Valores por defecto de la configuración (y variables de los mensajes).
views/              Templates PHP: layouts/, partials/, componentes/ (campo, estado) y una carpeta por módulo.
public/             Única carpeta expuesta por Apache: index.php, sw.js, offline.html y assets/
                    (css/ por capas, js/, vendor/ con las librerías servidas localmente).
database/
├── migrations/     NNNN_*.sql, se aplican en orden y quedan registradas en `migraciones`.
└── legacy/         Script único para bases de la versión anterior.
scripts/            backup.sh y restore.sh.
storage/            Configuración, imágenes subidas y logs (no versionado).
tests/              Unit/ (sin base) e Integration/ (contra MySQL).
```

Flujo de una petición: `public/index.php` → `App` → `Router` (control de acceso)
→ `Controller` → `Service` → `Repository` → vista.

Para cambiar la base de datos se agrega un archivo nuevo en `database/migrations/`
(por ejemplo `0008_descripcion.sql`); nunca se modifica uno ya aplicado.

### Rutas principales

| Módulo | Rutas |
| --- | --- |
| Clientes | `/clientes`, `/clientes/crear`, `/clientes/{id}/editar` |
| Vehículos | `/vehiculos`, `/vehiculos/crear`, `/vehiculos/{id}/editar` |
| Órdenes | `/ordenes`, `/ordenes/crear`, `/ordenes/{id}/editar`, `/ordenes/{id}/presupuesto`, `/ordenes/{id}/presupuesto/pdf` |
| Turnos | `/turnos`, `/turnos/crear`, `/turnos/{id}/editar` |
| Catálogos | `/marcas`, `/modelos`, `/servicios`, `/repuestos` |
| Inicio | `/` (panel) |
| Clientes | `/clientes/{id}` (ficha), `/deudores` |
| Vehículos | `/vehiculos/{id}` (ficha con imágenes) |
| Órdenes | `/ordenes/{id}` (ficha con pagos), `/ordenes/{id}/entrega`, `/ordenes/{id}/entrega/pdf` |
| Stock | `/repuestos/{id}/stock`, `/proveedores` |
| Acceso | `/login`, `/perfil/clave` |
| Públicas (clientes) | `/seguimiento`, `/turno/{token}` (confirmar o cancelar), `/presupuesto/{token}` (aceptar o rechazar) |
| Búsqueda y agenda | `/buscar?q=`, `/turnos/semana` |
| Solo administradores | `/configuracion/{seccion}`, `/usuarios`, `/empleados`, `/precios`, `/reportes`, `/auditoria`, `/logs` |

Todas las acciones que modifican datos (alta, edición, baja, cambio de estado)
son `POST` con token CSRF.

### Interfaz

- **Colores en un solo lugar**: `public/assets/css/tokens.css` define cada color con su versión
  clara y oscura. Bootstrap y las librerías leen de ahí, así que el modo oscuro (nativo de
  Bootstrap, `data-bs-theme` en `<html>`) funciona en cualquier componente sin reglas extra.
- **CSS por capas** (`app.css`): vendor → tokens → base → componentes → librerías → páginas →
  utilidades. Una capa posterior siempre le gana a una anterior: no hace falta `!important`.
  Nada de `style="…"` en las vistas: para un ancho puntual hay clases en `utilidades.css`.
- **Componentes de vista**: `componentes/campo` (etiqueta + control + ayuda, con `old()`;
  admite prefijo `$`, desplegable con buscador y múltiple), `componentes/estado` (badge de
  orden o turno) y `componentes/vacio` (estado vacío). Helpers: `importe()` para montos,
  `icono()` (Bootstrap Icons) y `boton_accion()` para las acciones de fila. El menú vive en
  `App\Support\MenuPrincipal` y se dibuja como botones en la PC y como barra inferior +
  menú lateral en el celular.
- **Botones según su función**: la acción principal de la pantalla va en el color de la
  sección (`btn-seccion`); ver y editar, con contorno; lo destructivo, en rojo. Dentro de
  las tablas y en el celular, las acciones de fila muestran solo el ícono.
- **Confirmaciones y avisos**: `data-confirm="¿…?"` en un formulario abre un modal (rojo si
  borra, anula o desactiva); desde JS, `window.confirmar()` y `window.avisar()`. Los
  mensajes de éxito son avisos flotantes; los errores quedan en la página.
- **Contraste**: cada color de fondo tiene su color de texto en `tokens.css`, elegido para
  llegar a 4.5:1.
- **Librerías locales** en `public/assets/vendor/` (sin CDN): la app instalada abre aunque la
  conexión sea mala. `asset()` agrega la fecha del archivo a la URL, así un cambio nunca queda
  tapado por el caché. Al cambiar `public/sw.js`, subir su `VERSION`.

## Reglas de negocio

- **Clientes**: el DNI no se modifica al editar. Un cliente con vehículos o turnos
  no se elimina (se desactiva).
- **Vehículos**: patente Mercosur (`AB123CD`) o anterior (`ABC123`); año entre 1940
  y el año próximo. Se activan/desactivan en lugar de borrarse.
- **Órdenes**: al menos un servicio. Cada ítem tiene cantidad y precio unitario;
  el precio sugerido es el del catálogo y queda congelado en la orden. Solo se
  editan órdenes pendientes o en proceso, y el total no puede quedar por debajo
  de lo pagado. Al finalizar se registra la fecha y se descuenta el stock de los
  repuestos (se repone si la orden se reabre o cancela).
- **Pagos**: parciales, sin superar el saldo, con las formas de pago configuradas.
  Un cliente es deudor si tiene órdenes finalizadas con saldo. Anular un pago es
  solo para administradores.
- **Turnos**: el vehículo debe pertenecer al cliente; no se agenda en fechas
  pasadas, fuera del horario de atención, en feriados ni por encima de los cupos
  simultáneos (los cancelados y ausentes liberan el lugar). Si se reprograma, se
  vuelve a pedir la confirmación al cliente.
- **Portal**: por defecto pide DNI y patente (con solo el DNI cualquiera podría ver
  datos ajenos; se puede cambiar en Configuración). No muestra datos de contacto,
  da el mismo error si el DNI no existe o la patente no coincide, y bloquea por
  15 minutos tras 10 consultas fallidas.
- **Catálogos**: no se puede borrar una marca, modelo, servicio, repuesto o
  proveedor en uso.
- **Seguridad**: contraseñas con `password_hash`, bloqueo de 15 minutos tras 5
  intentos fallidos, cierre de sesión por 8 h de inactividad, CSRF en todos los
  formularios y siempre al menos un administrador activo.
- **Imágenes**: JPG/PNG/WEBP hasta 8 MB, guardadas fuera de `public/` y
  re-codificadas (se eliminan metadatos como la ubicación GPS).

## Migrar desde la versión anterior

La versión anterior usaba MySQL 5.7 con los datos en la carpeta `mysql/`. La
nueva usa MySQL 8.4 con el volumen Docker `db_data`, así que los datos viejos no
se tocan y se migran así:

1. **Backup** con el stack anterior todavía levantado:
   ```bash
   docker exec database sh -c 'mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" taller_mecanico' > backup.sql
   ```
2. Bajar el stack anterior, actualizar el código y crear el `.env` nuevo a partir
   de `.env.example`.
3. Levantar **solo** la base nueva, importar el backup y aplicar el script de
   la versión anterior:
   ```bash
   docker compose up -d database
   docker compose exec -T database sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" taller_mecanico' < backup.sql
   docker compose exec -T database sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" taller_mecanico' < database/legacy/2026_10_02_desde_version_anterior.sql
   docker compose up -d
   ```
   Al levantar `public` se aplican solas las migraciones nuevas.
4. Entrar a la aplicación y crear el usuario administrador.
5. La configuración del taller ahora se guarda en `storage/config/taller.json`.
   Si se había modificado desde la pantalla de Sistema, volver a cargarla ahí.

La migración también funciona directamente sobre MySQL 5.7.

## Backups

El backup diario es automático (servicio `tareas`): a partir de la hora configurada
en *Configuración > Backups* guarda en `storage/backups` la base de datos
(`db_*.sql.gz`) y las imágenes y configuración (`archivos_*.tar.gz`), conservando los
últimos N. Desde esa misma pantalla se puede generar uno a mano y descargarlo. El
volcado se hace desde una foto consistente de la base, aunque se esté usando el sistema.

**Copia fuera del servidor:** con `BACKUP_COPIA_DIR` cada backup se copia además a otra
carpeta (un disco externo o una carpeta sincronizada con la nube), que hay que montar en
los contenedores, por ejemplo con un `docker-compose.override.yml`:

```yaml
services:
  public: { volumes: ["/mnt/disco_externo/backups_taller:/backups_externos"] }
  tareas: { volumes: ["/mnt/disco_externo/backups_taller:/backups_externos"] }
```

y `BACKUP_COPIA_DIR=/backups_externos` en `.env`. Si la copia falla, queda en el registro
del sistema y el backup local se conserva igual.

También se puede hacer desde la consola del servidor:

```bash
scripts/backup.sh                       # en backups/ (usa mysqldump del contenedor de la base)
scripts/restore.sh storage/backups/db_<fecha>.sql.gz storage/backups/archivos_<fecha>.tar.gz
```

## Logs y auditoría

- **Auditoría** (*Configuración > Auditoría*): acciones del negocio con usuario, IP y detalle
  (cambios de estado, pagos, stock, precios, configuración, ingresos al sistema). También se ve
  en la ficha de cada orden.
- **Registro del sistema** (*Configuración > Registro del sistema*): archivos diarios
  `storage/logs/app-AAAA-MM-DD.log` (una línea JSON por evento) con errores, avisos de PHP y
  eventos. Cada petición tiene un id (`X-Request-Id`) que aparece en la página de error, para
  encontrarla en el registro. Nivel y días a conservar: `LOG_LEVEL` y `LOG_DIAS` en `.env`.

## Desarrollo

```bash
docker compose exec public composer lint     # sintaxis
docker compose exec public composer migrate  # aplicar migraciones
docker compose exec -e DB_TEST_HOST=database -e DB_TEST_PASSWORD="$MYSQL_ROOT_PASSWORD" public composer test
```

Los tests de integración usan una base descartable (`taller_test`) y se omiten si
no está definida `DB_TEST_HOST`. En GitHub Actions corren en cada push y PR.

La hoja de ruta con lo hecho y lo pendiente está en [ROADMAP.md](ROADMAP.md).

Para depurar con Xdebug: `ENTORNO=desarrollo` y `XDEBUG_MODE=debug` en `.env`,
reconstruir (`docker compose up -d --build`) y usar la configuración de
`.vscode/launch.json` (puerto 9004).

Si una migración falla a la mitad, el error indica en qué sentencia; una vez corregida,
`composer migrate` continúa desde esa sentencia (las anteriores ya quedaron aplicadas).

Con `APP_DEBUG=true` se muestran los mensajes de error; en producción dejarlo en
`false` (los errores quedan en `storage/logs/app.log`).
