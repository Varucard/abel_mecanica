-- Base de datos para Taller Mecánico
CREATE DATABASE IF NOT EXISTS taller_mecanico;
USE taller_mecanico;

-- Tabla de Personas (clase base)
CREATE TABLE IF NOT EXISTS personas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL,
  apellido VARCHAR(50) NOT NULL,
  dni VARCHAR(8) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Clientes (hereda de Persona)
CREATE TABLE IF NOT EXISTS clientes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  persona_id INT NOT NULL UNIQUE,
  telefono VARCHAR(10) NOT NULL,
  direccion VARCHAR(200) NOT NULL,
  estado ENUM('activo', 'inactivo') DEFAULT 'activo',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (persona_id) REFERENCES personas(id) ON DELETE CASCADE
);

-- Tabla de Marcas de vehículos
CREATE TABLE IF NOT EXISTS marcas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL UNIQUE
);

-- Tabla de Modelos de vehículos
CREATE TABLE IF NOT EXISTS modelos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  marca_id INT NOT NULL,
  nombre VARCHAR(50) NOT NULL,
  FOREIGN KEY (marca_id) REFERENCES marcas(id) ON DELETE CASCADE,
  UNIQUE KEY unique_modelo (marca_id, nombre)
);

-- Tabla de Vehículos
CREATE TABLE IF NOT EXISTS vehiculos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  marca_id INT NOT NULL,
  modelo_id INT NOT NULL,
  anio INT NOT NULL,
  patente VARCHAR(10) NOT NULL UNIQUE,
  estado ENUM('activo', 'inactivo') DEFAULT 'activo',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
  FOREIGN KEY (marca_id) REFERENCES marcas(id),
  FOREIGN KEY (modelo_id) REFERENCES modelos(id)
);

-- Tabla de Servicios
CREATE TABLE IF NOT EXISTS servicios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL UNIQUE,
  descripcion TEXT,
  precio_base DECIMAL(10, 2),
  estado ENUM('activo', 'inactivo') DEFAULT 'activo'
);

-- Tabla de Órdenes de Servicio
CREATE TABLE IF NOT EXISTS ordenes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehiculo_id INT NOT NULL,
  servicio_id INT NOT NULL,
  costo DECIMAL(10, 2) NOT NULL,
  fecha_realizado DATE NOT NULL,
  fecha_finalizado DATE DEFAULT NULL,
  estado ENUM('pendiente', 'en_proceso', 'finalizado', 'cancelado') DEFAULT 'pendiente',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE CASCADE,
  FOREIGN KEY (servicio_id) REFERENCES servicios(id)
);

-- Vista para consultar órdenes con datos completos
CREATE OR REPLACE VIEW vw_ordenes_completas AS
SELECT 
  o.id AS orden_id,
  CONCAT(p.apellido, ', ', p.nombre) AS cliente_completo,
  m.nombre AS marca,
  mo.nombre AS modelo,
  v.anio AS anio_vehiculo,
  v.patente,
  s.nombre AS servicio,
  o.costo,
  o.fecha_realizado,
  o.fecha_finalizado AS fecha_salida,
  o.estado AS estado_orden,
  o.created_at
FROM ordenes o
INNER JOIN vehiculos v ON o.vehiculo_id = v.id
INNER JOIN modelos mo ON v.modelo_id = mo.id
INNER JOIN marcas m ON v.marca_id = m.id
INNER JOIN clientes c ON v.cliente_id = c.id
INNER JOIN personas p ON c.persona_id = p.id
INNER JOIN servicios s ON o.servicio_id = s.id
WHERE o.estado != 'cancelado';

-- TODO EL CONTENIDO POR ENCIMA DE ESTE PUNTO ES FUNDAMENTAL PARA EL CORRECTO FUNCIONAMIENTO DE LA BASE DE DATOS.

-- EL CONTENIDO POR DEBAJO DE ESTE PUNTO ES SOLO PARA PRUEBAS Y EJEMPLOS. PUEDE SER MODIFICADO O ELIMINADO SEGÚN SEA NECESARIO/DESEADO.

-- Insertar algunas marcas y modelos de ejemplo
INSERT INTO marcas (nombre) VALUES 
('Toyota'), 
('Ford'), 
('Chevrolet'), 
('Volkswagen'),
('Fiat'),
('Renault'),
('Peugeot')
ON DUPLICATE KEY UPDATE nombre=nombre;

-- Insertar modelos
INSERT INTO modelos (marca_id, nombre) VALUES 
(1, 'Corolla'), (1, 'Camry'), (1, 'Rav4'),
(2, 'Fiesta'), (2, 'Focus'), (2, 'Ranger'),
(3, 'Cruze'), (3, 'Onix'), (3, 'Equinox'),
(4, 'Gol'), (4, 'Polo'), (4, 'Amarok'),
(5, 'Palio'), (5, 'Uno'), (5, 'Siena'),
(6, 'Clio'), (6, 'Duster'), (6, 'Logan'),
(7, '208'), (7, '307'), (7, '308')
ON DUPLICATE KEY UPDATE nombre=nombre;

-- Insertar servicios
INSERT INTO servicios (nombre, descripcion, precio_base) VALUES 
('Cambio de aceite', 'Cambio de aceite y filtro', 2500.00),
('Alineación y balanceo', 'Alineación y balanceo de ruedas', 3500.00),
('Frenos', 'Revisión y cambio de pastillas de freno', 4500.00),
('Revisión general', 'Revisión completa del vehículo', 5000.00),
('Lavado completo', 'Lavado exterior e interior', 3000.00),
('Cambio de batería', 'Cambio de batería del vehículo', 4000.00)
ON DUPLICATE KEY UPDATE nombre=nombre;
