# Mecánica Abel — Sistema de gestión de taller

Aplicación web en PHP para administrar un taller mecánico: clientes, vehículos,
órdenes de servicio con presupuesto imprimible/PDF, agenda de turnos y catálogos
(marcas, modelos, servicios y repuestos).

## Stack

| Componente | Versión |
| --- | --- |
| PHP + Apache | 8.4 (compatible con 8.2+) |
| MySQL | 8.4 LTS |
| phpMyAdmin | 5 |
| Dompdf (vía Composer) | 3.1 |
| Bootstrap · jQuery · DataTables · Select2 | 5.3.8 · 3.7.1 · 1.13.11 · 4.1.0 |
| PHPUnit | 11.5 |

## Puesta en marcha

```bash
cp .env.example .env        # completar las claves
docker compose up -d --build
```

- Aplicación: <http://localhost:8050>
- phpMyAdmin: <http://localhost:8051>

En el primer arranque el contenedor ejecuta `composer install` y MySQL crea las
tablas desde `database/schema.sql` (solo cuando el volumen de datos está vacío).

### Sin Docker

Requiere PHP 8.2+ con `pdo_mysql`, `gd`, `mbstring` y `dom`, más un MySQL accesible.

```bash
composer install
cp .env.example .env        # DB_HOST=127.0.0.1, etc.
php -S localhost:8000 -t public public/index.php
```

## Arquitectura

Patrón MVC liviano, sin framework, con separación estricta de responsabilidades:

```
app/
├── Core/           Infraestructura: App, Router, Container (autowiring), Request,
│                   View, Session (flash + CSRF), Database, Env
├── Controllers/    Reciben la petición, llaman al servicio y renderizan. Sin SQL ni reglas.
├── Services/       Lógica de negocio y validaciones (precios congelados, disponibilidad
│                   de turnos, bajas protegidas, configuración, PDF).
├── Repositories/   Único lugar con SQL (PDO + sentencias preparadas).
├── Models/         Entidades del dominio (Persona → Cliente, Vehiculo, Orden, Turno…).
├── Enums/          Estados de órdenes, turnos y clientes/vehículos.
├── Support/        Validator.
└── helpers.php     Funciones para vistas: e(), url(), asset(), money(), csrf_field()…
config/
├── routes.php      Todas las rutas de la aplicación.
└── taller.php      Valores por defecto de la configuración del taller.
views/              Templates PHP (layout, parciales y una carpeta por módulo).
public/             Única carpeta expuesta por Apache: index.php + assets.
database/
├── schema.sql      Esquema completo para instalaciones nuevas.
└── migrations/     Scripts para actualizar bases existentes.
storage/            Configuración editada desde la app y logs (no versionado).
tests/              Tests unitarios (PHPUnit).
```

Flujo de una petición: `public/index.php` → `App` → `Router` → `Controller` →
`Service` → `Repository` → vista.

### Rutas principales

| Módulo | Rutas |
| --- | --- |
| Clientes | `/clientes`, `/clientes/crear`, `/clientes/{id}/editar` |
| Vehículos | `/vehiculos`, `/vehiculos/crear`, `/vehiculos/{id}/editar` |
| Órdenes | `/ordenes`, `/ordenes/crear`, `/ordenes/{id}/editar`, `/ordenes/{id}/presupuesto`, `/ordenes/{id}/presupuesto/pdf` |
| Turnos | `/turnos`, `/turnos/crear`, `/turnos/{id}/editar` |
| Catálogos | `/marcas`, `/modelos`, `/servicios`, `/repuestos` |
| Sistema | `/configuracion` |

Todas las acciones que modifican datos (alta, edición, baja, cambio de estado)
son `POST` con token CSRF.

## Reglas de negocio

- **Clientes**: el DNI no se modifica al editar. Un cliente con vehículos o turnos
  no se elimina (se desactiva).
- **Vehículos**: patente Mercosur (`AB123CD`) o anterior (`ABC123`); año entre 1940
  y el año próximo. Se activan/desactivan en lugar de borrarse.
- **Órdenes**: al menos un servicio. El costo de cada ítem queda congelado: si
  cambia el precio del catálogo, las órdenes existentes no se alteran. Solo se
  editan órdenes pendientes o en proceso. Al finalizar se registra la fecha.
- **Turnos**: el vehículo debe pertenecer al cliente; no se agenda en fechas
  pasadas ni en un horario ya ocupado (los cancelados y ausentes liberan el horario).
- **Catálogos**: no se puede borrar una marca, modelo, servicio o repuesto en uso.

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
3. Levantar **solo** la base nueva **sin** el esquema automático, importar y migrar:
   ```bash
   docker compose up -d database
   docker compose exec -T database sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e "DROP DATABASE taller_mecanico; CREATE DATABASE taller_mecanico"'
   docker compose exec -T database sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" taller_mecanico' < backup.sql
   docker compose exec -T database sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" taller_mecanico' < database/migrations/2026_10_02_profesionalizacion.sql
   docker compose up -d
   ```
4. La configuración del taller ahora se guarda en `storage/config/taller.json`.
   Si se había modificado desde la pantalla de Sistema, volver a cargarla ahí.

La migración también funciona directamente sobre MySQL 5.7.

## Desarrollo

```bash
docker compose exec public composer test   # tests unitarios
docker compose exec public composer lint   # chequeo de sintaxis
```

Para depurar con Xdebug: `XDEBUG_MODE=debug` en `.env`, reiniciar el contenedor
`public` y usar la configuración de `.vscode/launch.json` (puerto 9004).

Con `APP_DEBUG=true` se muestran los mensajes de error; en producción dejarlo en
`false` (los errores quedan en `storage/logs/app.log`).
