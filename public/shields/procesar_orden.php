<?php
  ob_start();
  require_once '../includes/config_database.php';
  require_once '../clases/OrdenServicios.php';

  // Procesar cambio de estado de orden
  if (isset($_GET['action']) && $_GET['action'] == 'cambiar_estado' && isset($_GET['id']) && isset($_GET['estado'])) {
    $id = intval($_GET['id']);
    $estado = $_GET['estado'];
    if (OrdenServicios::cambiarEstado($id, $estado)) {
      ob_end_clean();
      header("Location: ../views/listar_orden.php?success=estado");
      exit;
    } else {
      ob_end_clean();
      header("Location: ../views/listar_orden.php?error=estado");
      exit;
    }
  }

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vehiculo_id = intval($_POST['vehiculo_id']);
    // servicio_id puede ser un array (varios servicios) o un único valor
    $servicio_input = $_POST['servicio_id'];
    $costo = floatval($_POST['costo']);
    $fecha_realizado = trim($_POST['fecha_realizado']);

    // Validaciones en PHP
    $errors = [];

    // Validar costo
    if ($costo <= 0) 
      $errors[] = "El costo debe ser mayor a 0";

    // Validar fecha
    if (empty($fecha_realizado))
      $errors[] = "La fecha es requerida";

    if (count($errors) > 0) {
      ob_end_clean();
      header("Location: ../views/registrar_orden.php?error=" . urlencode(implode(", ", $errors)));
      exit;
    }

    // Crear una orden por cada servicio seleccionado (si vino un array)
    try {
      // Iniciar transacción para crear todas las órdenes juntas
      $conn->beginTransaction();

      $servicios_ids = [];
      if (is_array($servicio_input)) {
        foreach ($servicio_input as $s) {
          $servicios_ids[] = intval($s);
        }
      } else {
        $servicios_ids[] = intval($servicio_input);
      }

      if (count($servicios_ids) == 0)
        throw new Exception('Debe seleccionar al menos un servicio.');

      // Para cada servicio, obtener precio_base desde la BD y crear la orden correspondiente
      $stmt_precio = $conn->prepare("SELECT precio_base FROM servicios WHERE id = ?");

      foreach ($servicios_ids as $sid) {
        $stmt_precio->execute([$sid]);
        $res = $stmt_precio->fetch();

        if (!$res)
          throw new Exception("Servicio con ID {$sid} no encontrado.");

        $precio_servicio = floatval($res['precio_base']);

        // Crear orden con el precio del servicio (no con el total sumado)
        $orden = new OrdenServicios($vehiculo_id, $sid, $precio_servicio, $fecha_realizado);

        if (!$orden->guardar())
          throw new Exception('No se pudo crear la orden para el servicio ID ' . $sid);
      }

      // Si todas las inserciones fueron exitosas
      $conn->commit();
      ob_end_clean();
      header("Location: ../views/listar_orden.php?success=order");
      exit;
    } catch (Exception $e) {
      // Rollback si estaba en transacción
      if ($conn->inTransaction())
        $conn->rollBack();

      ob_end_clean();
      header("Location: ../views/registrar_orden.php?error=" . urlencode($e->getMessage()));
      exit;
    }
  }
