# SEGUNDO PARCIAL DE ALGORITMOS II Y BASE DE DATOS II

## 📋 Descripción

Sistema web desarrollado para registrar clientes y vehículos en un taller mecánico, aplicando Programación Orientada a Objetos con herencia, formularios HTML con validaciones e inserción de datos en MySQL.

## 🚀 Características

- **POO con Herencia**: Clase base Personas, heredada por Clientes
- **Gestión CRUD** completa de clientes
- **Registro de vehículos** con cascada marca/modelo mediante jQuery y Select2
- **Órdenes de servicio** vinculadas a vehículos y servicios
- **Validaciones** del lado cliente (HTML5) y servidor (PHP)
- **DataTables** para visualización de datos
- **Vistas SQL** para consultas optimizadas

## 📁 Estructura de Archivos

```
cueto-iozzoli-juan-ignacio/
├── clases/
│   ├── Personas.php
│   ├── Clientes.php
│   ├── Vehiculos.php
│   └── OrdenServicios.php
├── index.php
├── registrar_cliente.php
├── procesar_cliente.php
├── registrar_vehiculo.php
├── procesar_vehiculo.php
├── registrar_orden.php
├── procesar_orden.php
├── esquema_db.sql
├── README.md
└── includes/
    └── config.php
```

## 🛠️ Instalación

### 1. Estructura inicial

Para empezar, es necesario el repositorio del profesor Andrés Romano:

```bash
git clone git@gitlab.com:andres.romano.isft177/clases.git (SSH)
git clone https://gitlab.com/andres.romano.isft177/clases.git (HTTPS)
```
(Indistinto cuál de las dos opciones de clonación usar)

Una vez clonado el repositorio, se debe ubicar la carpeta 'cueto-iozzoli-juan-ignacio' dentro de la carpet 'public'.a estructura debe ser la siguiente:

```
├── clases/
│   ├── public/
│       ├── cueto-iozzoli-juan-ignacio/
│           ├── ...
│   ├── files/
│   ├── mongo/
│   ├── mysql/
```

El siguiente paso es correr el contenedor Docker:

```bash
docker-compose up -d
```

**Nota:** Con esta acción, el sitio ya puede ser visualizado desde http://localhost:8050/cueto-iozzoli-juan-ignacio/, pero no tendrá un correcto funcionamiento al carecer de una Base de Datos.

### 2. Importación de la Base de Datos

Acceder a phpMyAdmin en http://localhost:8051
-user: root
-pasword: root

Copiar y pegar el contenido de "esquema_db.sql" en la opción "SQL" de phpMyAdmin

**Nota:** La Base de Datos debe ser copiada y pegada tal cual aparece en esquema_db.sql hasta el punto señalizado (Linea 102). A partir de ese punto, los datos ingresados pueden ser diferentes, pero siempre respetando esa misma estructura.

### 3. Configuración

El archivo `config.php` ya está configurado para trabajar con el contenedor Docker:
- Host: `database`
- Database: `taller_mecanico`
- Usuario: `root`
- Password: `root`

### 4. Acceder al Sistema

Una vez importada la base de datos, acceder a:

```
http://localhost:8050/cueto-iozzoli-juan-ignacio/
```

**Nota:** El puerto 8050 es el que está configurado en tu `docker-compose.yml` para el contenedor de PHP.

## 📝 Clases PHP

### Personas (Clase Base)
- Atributos: nombre, apellido, dni
- Método: `guardar()`, `buscarPorDni()`

### Clientes (Hereda de Personas)
- Atributos adicionales: telefono, direccion
- Métodos CRUD: `guardar()`, `obtenerTodos()`, `obtenerPorId()`, `actualizar()`, `eliminar()`

### Vehiculos (Clase Independiente)
- Atributos: marca_id, modelo_id, anio, patente, cliente_id
- Métodos: `guardar()`, `obtenerTodos()`, `obtenerPorCliente()`, `obtenerMarcas()`, `obtenerModelosPorMarca()`

### OrdenServicios (Clase Independiente)
- Atributos: vehiculo_id, servicio_id, costo, fecha_realizado, estado
- Métodos: `guardar()`, `verOrdenes()` (consulta la vista SQL)

## 🎯 Funcionalidades

### Registro de Cliente
- Validación de DNI (6-8 dígitos)
- Validación de teléfono (10 dígitos)
- DataTable con lista de clientes y operaciones "Pasar a Inactivo", "Pasar a Activo" y "Eliminar"

### Registro de Vehículo
- Selección de cliente mediante Select2
- Cascada marca/modelo con jQuery AJAX
- Validación de patente (formato AB123CD o ABC123)
- Validación de año (1940-2025)

### Crear Orden de Servicio
- Selección de vehículo
- Selección de servicio (con precio base modificable)
- Fecha y costo
- Visualización en DataTable principal

## 🔍 Modelo de Base de Datos

```
personas ← clientes → vehiculos → ordenes
                        ↓           ↓
                    marcas/     servicios
                    modelos
```

## 📊 Vista SQL

Se crea automáticamente la vista `vw_ordenes_completas` que incluye:
- Nombre completo del cliente
- Datos del vehículo (marca, modelo, patente)
- Servicio realizado
- Costo, fecha y estado de la orden
- Posibilidad de marcar la orden como "Finalizada" (Marcando la fecha en la que fue finalizada)

## 🧪 Tecnologías Utilizadas

- PHP 8 (POO con herencia)
- MySQL 8
- Bootstrap 5.3
- jQuery 3.7
- DataTables 1.13.7
- Select2 4.1.0
- Docker (contenedores MySQL y phpMyAdmin)

## 👨‍💻 Autor

Juan Ignacio Cueto Iozzoli

**PD:** Poneme un 10 que quiero mantener la racha jajaja.

