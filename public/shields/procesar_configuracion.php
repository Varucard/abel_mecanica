<?php
ob_start();
require_once '../includes/config_database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  ob_end_clean();
  header("Location: ../views/configurar_sistema.php");
  exit;
}

// Recoger datos del formulario
$nombre = trim($_POST['nombre'] ?? '');
$cuit = trim($_POST['cuit'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$whatsapp = trim($_POST['whatsapp'] ?? '');
$email = trim($_POST['email'] ?? '');

$validez = (int) ($_POST['validez'] ?? 10);
$garantia = (int) ($_POST['garantia'] ?? 10);
$tiempo_estimado = (int) ($_POST['tiempo_estimado'] ?? 3);

$forma_pago_raw = trim($_POST['forma_pago'] ?? '');
$observaciones_raw = trim($_POST['observaciones'] ?? '');
$mensaje_legal = trim($_POST['mensaje_legal'] ?? '');

// Convertir textarea a arrays
$forma_pago = array_filter(array_map('trim', explode("\n", $forma_pago_raw)));
$observaciones = array_filter(array_map('trim', explode("\n", $observaciones_raw)));

// Validaciones básicas
$errors = [];
if (empty($nombre)) $errors[] = "El nombre del taller es obligatorio";
if (empty($cuit)) $errors[] = "El CUIT es obligatorio";
if (empty($direccion)) $errors[] = "La dirección es obligatoria";
if (empty($telefono)) $errors[] = "El teléfono es obligatorio";
if (empty($whatsapp)) $errors[] = "El WhatsApp es obligatorio";
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $errors[] = "El email es inválido";
}
if ($validez < 1) $errors[] = "La validez debe ser al menos 1 día";
if ($garantia < 1) $errors[] = "La garantía debe ser al menos 1 día";
if ($tiempo_estimado < 1) $errors[] = "El tiempo estimado debe ser al menos 1 día";
if (empty($forma_pago)) $errors[] = "Debe ingresar al menos una forma de pago";

if ($errors) {
  ob_end_clean();
  header("Location: ../views/configurar_sistema.php?error=" . urlencode(implode(', ', $errors)));
  exit;
}

// Generar el contenido del archivo PHP
$config_content = "<?php\n\n";
$config_content .= "/**\n";
$config_content .= " * Configuración del Taller\n";
$config_content .= " * Datos generales para usar en presupuestos, facturas, encabezados, etc.\n";
$config_content .= " */\n\n";

$config_content .= "\$config_taller = [\n";
$config_content .= "  'nombre' => " . var_export($nombre, true) . ",\n";
$config_content .= "  'cuit' => " . var_export($cuit, true) . ",\n";
$config_content .= "  'direccion' => " . var_export($direccion, true) . ",\n";
$config_content .= "  'telefono' => " . var_export($telefono, true) . ",\n";
$config_content .= "  'whatsapp' => " . var_export($whatsapp, true) . ",\n";
$config_content .= "  'email' => " . var_export($email, true) . ",\n";
$config_content .= "  'logo' => '../assets/img/logo.png',\n";
$config_content .= "];\n\n";

$config_content .= "\$config_trabajo = [\n";
$config_content .= "  'validez' => " . $validez . ",\n";
$config_content .= "  'garantia' => " . $garantia . ",\n";
$config_content .= "  'tiempo_estimado' => " . $tiempo_estimado . ",\n";
$config_content .= "  'forma_pago' => " . var_export(array_values($forma_pago), true) . ",\n";
$config_content .= "  'observaciones' => " . var_export(array_values($observaciones), true) . ",\n";
$config_content .= "  'mensaje_legal' => " . var_export($mensaje_legal, true) . "\n";
$config_content .= "];\n";

// Guardar el archivo
$config_file = '../includes/config_workshop.php';
$result = file_put_contents($config_file, $config_content);

if ($result === false) {
  ob_end_clean();
  header("Location: ../views/configurar_sistema.php?error=No se pudo guardar el archivo de configuración");
  exit;
}

ob_end_clean();
header("Location: ../views/configurar_sistema.php?success=1");
exit;
