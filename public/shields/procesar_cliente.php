<?php
  ob_start();
  require_once 'includes/config_database.php';
  require_once 'clases/Clientes.php';

  // Procesar eliminación
  if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    if (Clientes::eliminar($conn, $id)) {
      ob_end_clean();
      header("Location: registrar_cliente.php?success=delete");
      exit;
    } else {
      ob_end_clean();
      header("Location: registrar_cliente.php?error=delete");
      exit;
    }
  }

  // Procesar cambio de estado
  if (isset($_GET['action']) && $_GET['action'] == 'cambiar_estado' && isset($_GET['id']) && isset($_GET['estado'])) {
    $id = intval($_GET['id']);
    $estado = $_GET['estado'] == 'activo' ? 'inactivo' : 'activo';
    if (Clientes::cambiarEstado($conn, $id, $estado)) {
      ob_end_clean();
      header("Location: registrar_cliente.php?success=estado");
      exit;
    } else {
      ob_end_clean();
      header("Location: registrar_cliente.php?error=estado");
      exit;
    }
  }

  // Procesar registro
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = mb_strtoupper(trim($_POST['nombre']));
    $apellido = mb_strtoupper(trim($_POST['apellido']));
    $dni = trim($_POST['dni']);
    $telefono = trim($_POST['telefono']);
    $direccion = mb_strtoupper(trim($_POST['direccion']));

    // Validaciones en PHP
    $errors = [];

    // Validar nombre
    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]{2,50}$/', $nombre))
      $errors[] = "El nombre solo debe contener letras y espacios, de 2 a 50 caracteres";

    // Validar apellido
    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]{2,50}$/', $apellido)) 
      $errors[] = "El apellido solo debe contener letras y espacios, de 2 a 50 caracteres";

    // Validar DNI
    if (!preg_match('/^[0-9]{6,8}$/', $dni)) 
      $errors[] = "El DNI debe tener entre 6 y 8 dígitos numéricos";

    // Validar teléfono
    if (!preg_match('/^[0-9]{10}$/', $telefono))
      $errors[] = "El teléfono debe tener exactamente 10 dígitos numéricos";

    // Validar dirección
    if (strlen($direccion) < 5 || strlen($direccion) > 200)
      $errors[] = "La dirección debe tener entre 5 y 200 caracteres";

    if (count($errors) > 0) {
      ob_end_clean();
      header("Location: registrar_cliente.php?error=" . urlencode(implode(", ", $errors)));
      exit;
    }

    // Crear objeto Cliente y guardar
    try {
      $cliente = new Clientes($nombre, $apellido, $dni, $telefono, $direccion);
      
      if ($cliente->guardar($conn)) {
        ob_end_clean();
        header("Location: registrar_cliente.php?success=create");
        exit;
      } else {
        ob_end_clean();
        header("Location: registrar_cliente.php?error=No se pudo registrar el cliente. Intente nuevamente.");
        exit;
      }
    } catch (Exception $e) {
      ob_end_clean();
      header("Location: registrar_cliente.php?error=" . urlencode($e->getMessage()));
      exit;
    }
  }

  // Mostrar mensajes
  $message = '';
  $alert_type = '';

  if (isset($_GET['success'])) {
    $message = "Cliente registrado exitosamente";
    $alert_type = 'success';
  }

  if (isset($_GET['error'])) {
    $message = $_GET['error'];
    $alert_type = 'danger';
  }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Procesando Cliente</title>
</head>
<body>
<?php if ($message): ?>
    <script>
      alert('<?php echo $message; ?>');
      window.location.href = 'registrar_cliente.php';
    </script>
  <?php endif; ?>
</body>
</html>