<?php
// /shields/descargar_presupuesto.php

require_once __DIR__ . '/../includes/config_database.php';
require_once __DIR__ . '/../clases/OrdenServicios.php';
require_once __DIR__ . '/../clases/Clientes.php';
require_once __DIR__ . '/../clases/Vehiculos.php';
require_once __DIR__ . '/../includes/config_workshop.php';
require_once __DIR__ . '/../libraries/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// =======================
// VALIDAR ID
// =======================
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('Error: No se especificó el ID de la orden.');
}
$orden_id = (int) $_GET['id'];

// =======================
// ORDEN
// =======================
$stmt_orden = $conn->prepare("
    SELECT o.*, 
           v.patente, v.anio, v.kilometraje, v.cliente_id,
           ma.nombre AS marca, 
           mo.nombre AS modelo
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

// =======================
// CLIENTE
// =======================
$cliente = Clientes::obtenerPorId($conn, $orden['cliente_id']);
if (!$cliente) {
    die('Error: Cliente no encontrado.');
}

// =======================
// SERVICIOS / REPUESTOS
// =======================
$stmt_servicios = $conn->prepare("
    SELECT 
        os.costo,
        os.servicio_id,
        os.repuesto_id,
        s.nombre AS servicio_nombre,
        r.nombre AS repuesto_nombre
    FROM ordenes_servicios os
    LEFT JOIN servicios s ON os.servicio_id = s.id
    LEFT JOIN repuestos r ON os.repuesto_id = r.id
    WHERE os.orden_id = ?
    ORDER BY s.nombre, r.nombre
");
$stmt_servicios->execute([$orden_id]);
$servicios = $stmt_servicios->fetchAll(PDO::FETCH_ASSOC);

// =======================
// TOTALES
// =======================
$subtotal = 0;
foreach ($servicios as $serv) {
    $subtotal += (float) $serv['costo'];
}
$total = $subtotal;

// =======================
// EMAIL Y KM
// =======================
$email_raw    = isset($cliente['email']) ? trim((string) $cliente['email']) : '';
$email_cliente = ($email_raw === '') ? '—' : $email_raw;

$km_raw = isset($orden['kilometraje']) ? $orden['kilometraje'] : null;
if ($km_raw === null || $km_raw === '' || !is_numeric($km_raw)) {
    $km = '—';
} else {
    $km = number_format((float) $km_raw, 0, ',', '.') . ' km';
}

// =======================
// LOGO → DATA URI
// =======================
$logo_fs_path  = realpath(__DIR__ . '/../assets/img/logo.png');
$logo_data_uri = '';
if ($logo_fs_path && is_file($logo_fs_path)) {
    $img_data  = file_get_contents($logo_fs_path);
    $mime_type = 'image/png'; // cambiar si usás JPG
    $logo_data_uri = 'data:' . $mime_type . ';base64,' . base64_encode($img_data);
}

// =======================
// RENDERIZAR TEMPLATE HTML
// =======================
// variables disponibles para el template:
// $orden, $cliente, $servicios, $total, $email_cliente, $km,
// $logo_data_uri, $config_taller, $config_trabajo

ob_start();
require __DIR__ . '../../templates/presupuesto_pdf_template.php';
$html = ob_get_clean();

// =======================
// GENERAR PDF
// =======================
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = 'Presupuesto_' . str_pad($orden['id'], 4, '0', STR_PAD_LEFT) . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);