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
    $vehiculo_id     = intval($_POST['vehiculo_id']);
    $servicio_input  = $_POST['servicio_id']; // puede ser array o valor único
    $fecha_realizado = trim($_POST['fecha_realizado']);

    $errors = [];

    // Validar fecha
    if (empty($fecha_realizado))
      $errors[] = "La fecha es requerida";

    // Normalizar servicios a array
    $servicios_ids = [];
    if (is_array($servicio_input)) {
      foreach ($servicio_input as $s) {
        $servicios_ids[] = intval($s);
      }
    } else {
      $servicios_ids[] = intval($servicio_input);
    }

    if (count($servicios_ids) == 0)
      $errors[] = 'Debe seleccionar al menos un servicio.';

    if (count($errors) > 0) {
      ob_end_clean();
      header("Location: ../views/registrar_orden.php?error=" . urlencode(implode(", ", $errors)));
      exit;
    }

    try {
      $conn->beginTransaction();

      // 1) Crear cabecera de ORDEN (una sola)
      $sql_orden = "INSERT INTO ordenes (vehiculo_id, fecha_realizado, estado, total)
                    VALUES (?, ?, 'pendiente', 0)";
      $stmt_orden = $conn->prepare($sql_orden);

      if (!$stmt_orden->execute([$vehiculo_id, $fecha_realizado])) {
        throw new Exception("No se pudo crear la orden.");
      }

      $orden_id = $conn->lastInsertId();

      // 2) Por cada servicio, obtener precio_base y crear detalle en ordenes_servicios
      $stmt_precio = $conn->prepare("SELECT precio_base FROM servicios WHERE id = ?");
      $total = 0;

      foreach ($servicios_ids as $sid) {
        $stmt_precio->execute([$sid]);
        $res = $stmt_precico = $stmt_precio->fetch();

        if (!$res)
          throw new Exception("Servicio con ID {$sid} no encontrado.");

        $precio_servicio = floatval($res['precio_base']);
        $total += $precio_servicio;

        // Crear detalle
        $detalle = new OrdenServicios($orden_id, $sid, $precio_servicio);

        if (!$detalle->guardar())
          throw new Exception('No se pudo agregar el servicio ID ' . $sid . ' a la orden.');
      }

      // 3) Actualizar total de la orden
      $stmt_total = $conn->prepare("UPDATE ordenes SET total = ? WHERE id = ?");
      if (!$stmt_total->execute([$total, $orden_id])) {
        throw new Exception("No se pudo actualizar el total de la orden.");
      }

      $conn->commit();
      ob_end_clean();
      header("Location: ../views/listar_orden.php?success=order");
      exit;

    } catch (Exception $e) {
      if ($conn->inTransaction())
        $conn->rollBack();

      ob_end_clean();
      header("Location: ../views/registrar_orden.php?error=" . urlencode($e->getMessage()));
      exit;
    }
  }
