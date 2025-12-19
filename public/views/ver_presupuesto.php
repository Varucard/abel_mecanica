<?php
require_once '../includes/config_database.php';
require_once '../clases/OrdenServicios.php';
require_once '../clases/Clientes.php';
require_once '../clases/Vehiculos.php';
require_once '../includes/config_workshop.php'; // <-- usamos $config_taller y $config_trabajo

// Validar que venga el ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
  die('Error: No se especificó el ID de la orden.');
}

$orden_id = intval($_GET['id']);

// Obtener datos de la ORDEN (cabecera)
$stmt_orden = $conn->prepare("
  SELECT o.*, v.patente, v.anio, v.cliente_id, v.kilometraje,
         ma.nombre AS marca, mo.nombre AS modelo
  FROM ordenes o
  INNER JOIN vehiculos v ON o.vehiculo_id = v.id
  INNER JOIN marcas ma ON v.marca_id = ma.id
  INNER JOIN modelos mo ON v.modelo_id = mo.id
  WHERE o.id = ?
");
$stmt_orden->execute([$orden_id]);
$orden = $stmt_orden->fetch(PDO::FETCH_ASSOC);

if (!$orden) {
  die('Error: Orden no encontrada.');
}

// Obtener CLIENTE
$cliente = Clientes::obtenerPorId($conn, $orden['cliente_id']);
if (!$cliente) {
  die('Error: Cliente no encontrado.');
}

// Obtener SERVICIOS y REPUESTOS de la orden
$stmt_detalle = $conn->prepare("
  SELECT 
    os.costo,
    s.nombre AS servicio_nombre,
    r.nombre AS repuesto_nombre,
    os.repuesto_id
  FROM ordenes_servicios os
  INNER JOIN servicios s ON os.servicio_id = s.id
  LEFT JOIN repuestos r ON os.repuesto_id = r.id
  WHERE os.orden_id = ?
  ORDER BY s.nombre, r.nombre
");
$stmt_detalle->execute([$orden_id]);
$detalle = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC);

// Calcular total costos
$total = 0;
foreach ($detalle as $item) {
  $total += floatval($item['costo']);
}

// Detectar si es para imprimir (abre nuevo tab y auto-print)
$print_mode = isset($_GET['print']) && $_GET['print'] == '1';

// Ahora incluimos el template que usa $config_taller y $config_trabajo
include '../templates/presupuesto_template.php';
