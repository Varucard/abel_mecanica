<?php
ob_start();
require_once '../includes/config_database.php';
require_once '../clases/OrdenServicios.php';

define('SERVICIO_REPUESTO_ID', 1);

/*
|--------------------------------------------------------------------------
| 1) CAMBIAR ESTADO (GET)
|--------------------------------------------------------------------------
| URL esperada:
|   ../shields/procesar_orden.php?action=cambiar_estado&id=123&estado=finalizado
*/
if (
  isset($_GET['action'], $_GET['id'], $_GET['estado']) &&
  $_GET['action'] === 'cambiar_estado'
) {
  $id     = (int) $_GET['id'];
  $estado = $_GET['estado'];

  // Validar ID mínimo
  if ($id <= 0) {
    ob_end_clean();
    header("Location: ../views/listar_orden.php?error=orden_invalida");
    exit;
  }

  // Validar que el estado sea uno de los permitidos en el ENUM
  $estadosValidos = ['pendiente', 'en_proceso', 'finalizado', 'cancelado'];
  if (!in_array($estado, $estadosValidos, true)) {
    ob_end_clean();
    header("Location: ../views/listar_orden.php?error=estado_invalido");
    exit;
  }

  // Cambiar estado usando la clase de dominio
  if (OrdenServicios::cambiarEstado($id, $estado)) {
    ob_end_clean();
    header("Location: ../views/listar_orden.php?success=estado");
    exit;
  }

  ob_end_clean();
  header("Location: ../views/listar_orden.php?error=estado");
  exit;
}

/*
|--------------------------------------------------------------------------
| 2) POST → CREAR / EDITAR ORDEN
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $id              = isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null;
  $vehiculo_id     = isset($_POST['vehiculo_id']) ? (int) $_POST['vehiculo_id'] : 0;
  $servicio_input  = $_POST['servicio_id'] ?? [];
  $repuesto_input  = $_POST['repuesto_id'] ?? [];

  $errors = [];

  if ($vehiculo_id <= 0) {
    $errors[] = "Vehículo inválido";
  }

  if (count($servicio_input) === 0) {
    $errors[] = "Debe seleccionar al menos un servicio";
  }

  if ($errors) {
    ob_end_clean();
    $msg = urlencode(implode(', ', $errors));
    header("Location: ../views/registrar_orden.php?error={$msg}" . ($id ? "&id=$id" : ""));
    exit;
  }

  try {
    $conn->beginTransaction();

    /* =========================
       CREAR / EDITAR ORDEN
    ========================= */

    if ($id) {
      // EDITAR
      $stmt = $conn->prepare(
        "UPDATE ordenes SET vehiculo_id = ? WHERE id = ?"
      );
      $stmt->execute([$vehiculo_id, $id]);

      // Borrar detalles anteriores
      $conn->prepare("DELETE FROM ordenes_servicios WHERE orden_id = ?")
           ->execute([$id]);

      $orden_id = $id;
    } else {
      // CREAR (siempre inicia en 'pendiente')
      $stmt = $conn->prepare(
        "INSERT INTO ordenes (vehiculo_id, estado, total)
         VALUES (?, 'pendiente', 0)"
      );
      $stmt->execute([$vehiculo_id]);
      $orden_id = (int) $conn->lastInsertId();
    }

    $total = 0;

    /* =========================
       SERVICIOS
    ========================= */
    $stmt_serv = $conn->prepare("SELECT precio_base FROM servicios WHERE id = ?");

    foreach ($servicio_input as $sid) {
      $sid = (int) $sid;
      if ($sid <= 0) continue;

      $stmt_serv->execute([$sid]);
      $serv = $stmt_serv->fetch(PDO::FETCH_ASSOC);

      if (!$serv) {
        throw new Exception("Servicio ID {$sid} no encontrado");
      }

      $precio = (float) $serv['precio_base'];
      $total += $precio;

      $detalle = new OrdenServicios($orden_id, $sid, null, $precio);
      if (!$detalle->guardar()) {
        throw new Exception("No se pudo agregar servicio");
      }
    }

    /* =========================
       REPUESTOS (opcionales)
    ========================= */
    $stmt_rep = $conn->prepare("SELECT precio FROM repuestos WHERE id = ?");

    foreach ($repuesto_input as $rid) {
      $rid = (int) $rid;
      if ($rid <= 0) continue;

      $stmt_rep->execute([$rid]);
      $rep = $stmt_rep->fetch(PDO::FETCH_ASSOC);

      if (!$rep) {
        throw new Exception("Repuesto ID {$rid} no encontrado");
      }

      $precio = (float) $rep['precio'];
      $total += $precio;

      // Usás SERVICIO_REPUESTO_ID como "servicio genérico" para repuestos
      $detalle = new OrdenServicios($orden_id, SERVICIO_REPUESTO_ID, $rid, $precio);
      if (!$detalle->guardar()) {
        throw new Exception("No se pudo agregar repuesto");
      }
    }

    /* =========================
       TOTAL
    ========================= */
    $conn->prepare("UPDATE ordenes SET total = ? WHERE id = ?")
         ->execute([$total, $orden_id]);

    $conn->commit();
    ob_end_clean();

    header("Location: ../views/listar_orden.php?success=" . ($id ? "update" : "create"));
    exit;

  } catch (Exception $e) {
    if ($conn->inTransaction()) {
      $conn->rollBack();
    }

    ob_end_clean();
    $msg = urlencode($e->getMessage());
    header("Location: ../views/registrar_orden.php?error={$msg}" . ($id ? "&id=$id" : ""));
    exit;
  }
}

/*
|--------------------------------------------------------------------------
| 3) GET id → IR A FORMULARIO DE EDITAR
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && !isset($_GET['action'])) {
  $id = (int) $_GET['id'];

  ob_end_clean();
  header("Location: ../views/registrar_orden.php?id=" . $id);
  exit;
}

/*
|--------------------------------------------------------------------------
| 4) FALLBACK
|--------------------------------------------------------------------------
*/
ob_end_clean();
header("Location: ../views/listar_orden.php");
exit;