<?php
ob_start();
require_once '../includes/config_database.php';
require_once '../clases/OrdenServicios.php';

define('SERVICIO_REPUESTO_ID', 1); // ID real en tu tabla servicios

/* =========================
   CAMBIO DE ESTADO
========================= */
if (
  isset($_GET['action'], $_GET['id'], $_GET['estado']) &&
  $_GET['action'] === 'cambiar_estado'
) {
  $id = (int) $_GET['id'];
  $estado = $_GET['estado'];

  if (OrdenServicios::cambiarEstado($id, $estado)) {
    ob_end_clean();
    header("Location: ../views/listar_orden.php?success=estado");
    exit;
  }

  ob_end_clean();
  header("Location: ../views/listar_orden.php?error=estado");
  exit;
}

/* =========================
   ALTA DE ORDEN
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $vehiculo_id     = (int) $_POST['vehiculo_id'];
  $servicio_input  = $_POST['servicio_id'] ?? [];
  $repuesto_input  = $_POST['repuesto_id'] ?? [];
  $fecha_realizado = trim($_POST['fecha_realizado']);

  $errors = [];

  if (!$vehiculo_id)
    $errors[] = "Vehículo inválido";

  if (empty($fecha_realizado))
    $errors[] = "La fecha es requerida";

  /* Normalizar servicios */
  $servicios_ids = [];
  if (is_array($servicio_input)) {
    foreach ($servicio_input as $s)
      $servicios_ids[] = (int) $s;
  }

  /* Normalizar repuestos */
  $repuestos_ids = [];
  if (is_array($repuesto_input)) {
    foreach ($repuesto_input as $r)
      $repuestos_ids[] = (int) $r;
  }

  if (count($servicios_ids) === 0)
    $errors[] = "Debe seleccionar al menos un servicio";

  if ($errors) {
    ob_end_clean();
    header("Location: ../views/registrar_orden.php?error=" . urlencode(implode(', ', $errors)));
    exit;
  }

  try {
    $conn->beginTransaction();

    /* 1) Crear ORDEN */
    $stmt = $conn->prepare(
      "INSERT INTO ordenes (vehiculo_id, fecha_realizado, estado, total)
       VALUES (?, ?, 'pendiente', 0)"
    );

    if (!$stmt->execute([$vehiculo_id, $fecha_realizado]))
      throw new Exception("No se pudo crear la orden");

    $orden_id = $conn->lastInsertId();
    $total = 0;

    /* 2) SERVICIOS */
    $stmt_serv = $conn->prepare("SELECT precio_base FROM servicios WHERE id = ?");

    foreach ($servicios_ids as $sid) {
      $stmt_serv->execute([$sid]);
      $serv = $stmt_serv->fetch(PDO::FETCH_ASSOC);

      if (!$serv)
        throw new Exception("Servicio ID {$sid} no encontrado");

      $precio = (float) $serv['precio_base'];
      $total += $precio;

      $detalle = new OrdenServicios($orden_id, $sid, null, $precio);
      if (!$detalle->guardar())
        throw new Exception("No se pudo agregar servicio ID {$sid}");
    }

    /* 3) REPUESTOS */
    $stmt_rep = $conn->prepare("SELECT precio FROM repuestos WHERE id = ?");

    foreach ($repuestos_ids as $rid) {
      $stmt_rep->execute([$rid]);
      $rep = $stmt_rep->fetch(PDO::FETCH_ASSOC);

      if (!$rep)
        throw new Exception("Repuesto ID {$rid} no encontrado");

      $precio = (float) $rep['precio'];
      $total += $precio;

      $detalle = new OrdenServicios($orden_id, SERVICIO_REPUESTO_ID, $rid, $precio);
      if (!$detalle->guardar())
        throw new Exception("No se pudo agregar repuesto ID {$rid}");
    }

    /* 4) TOTAL */
    $stmt_total = $conn->prepare("UPDATE ordenes SET total = ? WHERE id = ?");
    $stmt_total->execute([$total, $orden_id]);

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
