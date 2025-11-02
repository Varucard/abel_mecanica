# Stack completo
## Materias:

+ Bases de Datos
+ Algoritmos y Estructuras de datos 2

Esta configuración de docker es la que utilizaremos para
los ejemplos y trabajos realizados en clase, para que 
todos tengamos la misma configuracion y evitar errores.

> Esta compuesto por MySQL + PhpMyAdmin, MongoDB + Express, PHP 8.2 + Apache2 y ChartDB

### Contenedores

+ database: Contiene la version 5.7 del motor MySQL
+ mongo: Contiene la ultima version de MongoDB
+ admin: Administrador web de Bases de Datos MySQL, PhpMyAdmin
+ mongo-express: Administrador web de Bases de Datos NoSQL, Express para MongoDB
+ public: Dentro podemos alojar nuestros proyectos, contiene PHP y Apache
+ chartdb: Herramienta para el diseño de bases de datos relacionales

### Archivos

+ .env: contiene las variables de entorno para configurar los contenedores
+ .gitignore: sirve para excluir archivos del repositorio
+ Dockerfile: Contiene los comandos para recrear la imagen del contenedor public
+ mysqld.cnf: archivo de configuracion del motor de bases de datos MySQL

### Variables de entorno

Se debe crear el archivo .env dentro de la carpeta "clases/"
```
~$ cd clases
~/clases$ nano .env
```
con las siguientes variables
``` 
TZ=America/Argentina/Buenos_Aires
SQL_SERVER=database 
MYSQL_ROOT_PASSWORD=root 
PMA_HOST=mysqldb
MONGO_INITDB_ROOT_USERNAME=root
MONGO_INITDB_ROOT_PASSWORD=root
ME_CONFIG_OPTIONS_EDITORTHEME=neo
ME_CONFIG_MONGODB_SERVER=mongodb
ME_CONFIG_MONGODB_PORT=27017
ME_CONFIG_MONGODB_ENABLE_ADMIN=true
ME_CONFIG_MONGODB_ADMINUSERNAME=root
ME_CONFIG_MONGODB_ADMINPASSWORD=root
ME_CONFIG_BASICAUTH_USERNAME=admin
ME_CONFIG_BASICAUTH_PASSWORD=zaq123
```
### Levantar los contenedores

Comando para iniciar los contenedores, ingresando a la carpeta "clases/"
```
~$ cd clases
~/clases$ sudo docker-compose up -d --build
```

### Accesos Web

+ http://localhost:8050/mi_proyecto -> servidor apache
+ http://localhost:8051 -> PhpMyAdmin (user: root pass: root)
+ http://localhost:8052/ -> Mongo-Express (user: adnin pass: zaq123)
+ http://localhost:8053/ -> ChartDB

> mi_proyecto seria el nombre de la carpeta de nuestro proyecto, o cualquier otra carpeta que querramos explorar por el navegador.

### Accesos bash

Para poder acceder a las terminales de los contenedores
```
~$ sudo docker exec -it nombre_contenedor bash
```
> nombre_contenedor se debe reemplazar por el nombre correspondiente al contenedor que necesitamos ingresar.

### Nota

Para poder ver las carpetas y sea más fácil el acceso desde el navegador, dentro de la carpeta public existe el archivo .htaccess con una única línea de código
```
Options +Indexes
```

Prof. Andrés D. Romano
