# abel_mecanica

**abel_mecanica** es un entorno de desarrollo dockerizado que integra múltiples servicios (bases de datos SQL y NoSQL, servidor web PHP/Apache y herramientas de administración). Es ideal para desarrollar proyectos web en PHP, realizar prácticas, prototipos y trabajos de clase con una infraestructura completa lista para usar.

Este stack proporciona una base homogénea para trabajar con múltiples proyectos sin necesitar instalaciones locales complejas.

---

## 🚀 Stack Tecnológico

- **PHP 8.x + Apache** — Contenedor principal que ejecuta las aplicaciones ubicadas en `/public`.
- **MySQL 5.7** — Base de datos relacional.
- **phpMyAdmin** — Administración visual de MySQL.
- **MongoDB** — Base de datos NoSQL.
- **Mongo‑Express** — Interfaz web para MongoDB.
- **ChartDB** — Herramienta de diagramación de bases de datos.
- **Docker + docker-compose** — Orquestación completa del entorno.

---

## 📁 Estructura del Repositorio

```
/
├── Dockerfile
├── docker-compose.yml
├── mysqld.cnf
├── xdebug.ini
├── NOTAS.MD
├── README.md
├── public/
│   └── <proyecto_php>
├── mysql/
├── files/
└── .env (no incluido)
```

### 📌 Sobre la carpeta `/public`

Dentro de `/public` se encuentran los proyectos PHP que serán servidos por Apache dentro del contenedor principal.  
Cada subcarpeta representa un proyecto independiente. Por ejemplo:

```
/public
└── mecanica_app/
    ├── index.php
    ├── css/
    ├── js/
    ├── vistas/
    ├── controladores/
    └── modelos/
```

Para acceder al proyecto:

```
http://localhost:8050/mecanica_app/
```

Este entorno permite desarrollar aplicaciones PHP estructuradas bajo MVC, API REST, sistemas escolares, CRUDs y cualquier aplicación que requiera una base de datos SQL o NoSQL.

---

## 📝 Configuración Inicial

### 1. Clonar el repositorio

```bash
git clone https://github.com/Varucard/abel_mecanica.git
cd abel_mecanica
```

### 2. Crear el archivo `.env`

Ejemplo mínimo:

```
TZ=America/Argentina/Buenos_Aires
SQL_SERVER=database
MYSQL_ROOT_PASSWORD=root

# phpMyAdmin
PMA_HOST=mysqldb

# Mongo
MONGO_INITDB_ROOT_USERNAME=root
MONGO_INITDB_ROOT_PASSWORD=root

# Mongo Express
ME_CONFIG_OPTIONS_EDITORTHEME=neo
ME_CONFIG_MONGODB_SERVER=mongodb
ME_CONFIG_MONGODB_PORT=27017
ME_CONFIG_MONGODB_ENABLE_ADMIN=true
ME_CONFIG_MONGODB_ADMINUSERNAME=root
ME_CONFIG_MONGODB_ADMINPASSWORD=root
ME_CONFIG_BASICAUTH_USERNAME=admin
ME_CONFIG_BASICAUTH_PASSWORD=zaq123
```

> ⚠️ No uses estas credenciales en producción.

---

## ▶️ Cómo Levantar el Entorno

```bash
docker-compose up -d --build
```

Servicios disponibles:

| Servicio | URL |
|---------|-----|
| Proyecto PHP/Apache | http://localhost:8050/<tu_proyecto>/ |
| phpMyAdmin | http://localhost:8051 |
| Mongo‑Express | http://localhost:8052 |
| ChartDB | http://localhost:8053 |

---

## 🧑‍💻 Desarrollo dentro de `/public`

Para agregar un proyecto nuevo:

1. Crear carpeta dentro de `/public`, ej.:

```
public/mi_sistema/
```

2. Crear un `index.php`:

```php
<?php
echo "Proyecto funcionando";
```

3. Acceder desde el navegador:

```
http://localhost:8050/mi_sistema/
```

### Interacciones con la base de datos

El contenedor MySQL expone:

- **Host:** `mysqldb`
- **Usuario:** `root`
- **Contraseña:** definida en `.env`

Ejemplo conexión PDO:

```php
$pdo = new PDO("mysql:host=mysqldb;dbname=test;charset=utf8", "root", "root");
```

---

## ⚠️ Consideraciones Importantes

- El `.env` no está incluido: cada usuario debe generar el suyo.
- Las contraseñas por defecto deben cambiarse si se despliega fuera de entornos escolares/privados.
- Si agregas más proyectos, recomendación: cada uno tenga su propio README.
- La carpeta `/mysql` permite almacenar configuraciones y persistencia.
- El entorno está pensado para **desarrollo**, no para producción directa.

---

## 📌 Mejoras Futuras Sugeridas

- Añadir migraciones para MySQL.
- Incorporar Composer en los proyectos PHP.
- Agregar tests automatizados.
- Añadir script de backup/restore de bases de datos.
- Documentar la estructura de los proyectos dentro de `/public`.

---

## 📄 Licencia

Actualmente el proyecto **no declara licencia**.  
Si deseas compartirlo públicamente, se recomienda agregar un archivo `LICENSE`.

---

## ✨ Autor / Mantenimiento

Proyecto preparado para facilitar el desarrollo web en entornos educativos y personales.  
Ideal para prácticas de PHP, MySQL, MongoDB y Docker.

---

¡Entorno listo para desarrollar! 🚀
