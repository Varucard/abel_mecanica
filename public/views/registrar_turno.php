<?php
require_once '../includes/config_database.php';
require_once '../Clases/Turnos.php';
require_once '../Clases/Clientes.php';

/**
 * 1) Endpoint AJAX para traer vehículos por cliente
 *    registrar_turno.php?get_vehiculos=ID
 */
if (isset($_GET['get_vehiculos']) && is_numeric($_GET['get_vehiculos'])) {
  $cliente_id = (int) $_GET['get_vehiculos'];

  $stmt = $conn->prepare("
    SELECT v.id, CONCAT(m.nombre, ' ', mo.nombre, ' (', v.patente, ')') AS info
    FROM vehiculos v
    JOIN marcas m ON v.marca_id = m.id
    JOIN modelos mo ON v.modelo_id = mo.id
    WHERE v.cliente_id = ?
  ");
  $stmt->execute([$cliente_id]);
  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
  exit;
}

$editando = false;
$turno = null;

/**
 * 2) Si viene id por GET, estamos editando un turno
 */
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $id = (int) $_GET['id'];
  $turno = Turno::obtenerPorId($conn, $id);

  if (!$turno) {
    header('Location: listar_turno.php?error=turno_invalido');
    exit;
  }

  $editando = true;
}

/**
 * 3) Procesar envío del formulario (crear o actualizar)
 *    NOTA: ahora se procesa acá, no en TurnoController.php
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $esActualizacion = isset($_POST['id']) && is_numeric($_POST['id']);

  if ($esActualizacion) {
    // Actualizar turno existente
    $turnoObj = new Turno();
    $turnoObj->setId($_POST['id']);
    $turnoObj->setClienteId($_POST['cliente_id']);
    $turnoObj->setVehiculoId($_POST['vehiculo_id']);
    $turnoObj->setFecha($_POST['fecha']);
    $turnoObj->setHora($_POST['hora']);
    $turnoObj->setEstado($_POST['estado'] ?? 'pendiente');
    $turnoObj->setDescripcion($_POST['descripcion']);

    if ($turnoObj->actualizar($conn)) {
      echo json_encode(['status' => 'success']);
    } else {
      echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el turno']);
    }
    exit;
  } else {
    // Alta nueva (por si usás este archivo también para "Nuevo Turno")
    $turnoObj = new Turno(
      $_POST['cliente_id'],
      $_POST['vehiculo_id'],
      $_POST['fecha'],
      $_POST['hora'],
      $_POST['descripcion'],
      'pendiente'
    );

    if ($turnoObj->guardar($conn)) {
      echo json_encode(['status' => 'success']);
    } else {
      echo json_encode(['status' => 'error', 'message' => 'Error al agendar el turno']);
    }
    exit;
  }
}

/**
 * 4) Obtener datos para armar el formulario (solo GET)
 */

// Obtener clientes para el select
$clientes = $conn->query("
  SELECT c.id, CONCAT(p.nombre, ' ', p.apellido) AS nombre
  FROM clientes c
  JOIN personas p ON c.persona_id = p.id
  ORDER BY p.apellido ASC
");

// Obtener vehículos del cliente actual (si estamos editando)
$vehiculos = [];
if ($editando) {
  $stmtVeh = $conn->prepare("
    SELECT v.id, CONCAT(m.nombre, ' ', mo.nombre, ' (', v.patente, ')') AS info
    FROM vehiculos v
    JOIN marcas m ON v.marca_id = m.id
    JOIN modelos mo ON v.modelo_id = mo.id
    WHERE v.cliente_id = ?
  ");
  $stmtVeh->execute([$turno['cliente_id']]);
  $vehiculos = $stmtVeh->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../assets/img/logo_64.png">
  <title><?= $editando ? 'Editar Turno' : 'Registrar Turno' ?> - Taller Mecánico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
  <div class="container">
    <div class="card">
      <div class="card-header bg-warning text-dark">
        <h1 class="mb-0">
          <?= $editando ? 'Editar Turno' : 'Registrar Turno' ?>
          <img src="../assets/img/logo.png" alt="Logo" style="height:80px; width:80px; border-radius: 50%;">
          <button id="btnDarkMode"
            class="btn btn-sm btn-outline-light"
            type="button">
            🌙 Modo oscuro
          </button>
        </h1>
      </div>
      <div class="card-body">
        <!-- Menú -->
        <?php require_once '../views/navbar.php'; ?>
        <!-- Fin menú -->

        <?php if (isset($_GET['error'])): ?>
          <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert" id="error-alert">
            <?= htmlspecialchars($_GET['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <div class="card mt-4">
          <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
              <?= $editando ? 'Editar Turno #' . $turno['id'] : 'Agendar Nuevo Turno' ?>
            </h4>
            <a href="listar_turno.php" class="btn btn-secondary">Volver a la Agenda</a>
          </div>
          <div class="card-body">
            <form id="formTurnoEdit">
              <?php if ($editando): ?>
                <input type="hidden" name="id" value="<?= $turno['id'] ?>">
              <?php endif; ?>
              <input type="hidden" name="accion" value="<?= $editando ? 'actualizar' : 'crear' ?>">

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Cliente</label>
                  <select name="cliente_id" id="cliente_id" class="form-select" required>
                    <option value="">Seleccione un cliente...</option>
                    <?php while ($c = $clientes->fetch(PDO::FETCH_ASSOC)): ?>
                      <option value="<?= $c['id'] ?>"
                        <?= $editando && $c['id'] == $turno['cliente_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nombre']) ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Vehículo</label>
                  <select name="vehiculo_id" id="vehiculo_id" class="form-select" required
                    <?= $editando ? '' : 'disabled' ?>>
                    <option value="">
                      <?= $editando ? 'Seleccione un vehículo...' : 'Seleccione primero un cliente...' ?>
                    </option>
                    <?php if ($editando): ?>
                      <?php foreach ($vehiculos as $v): ?>
                        <option value="<?= $v['id'] ?>"
                          <?= $v['id'] == $turno['vehiculo_id'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($v['info']) ?>
                        </option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>

                <div class="col-md-4 mb-3">
                  <label class="form-label">Fecha</label>
                  <input type="date"
                    name="fecha"
                    class="form-control"
                    required
                    value="<?= $editando ? htmlspecialchars($turno['fecha']) : date('Y-m-d') ?>">
                </div>

                <div class="col-md-4 mb-3">
                  <label class="form-label">Hora</label>
                  <input type="time"
                    name="hora"
                    class="form-control"
                    required
                    value="<?= $editando ? htmlspecialchars(substr($turno['hora'], 0, 5)) : '' ?>">
                </div>

                <div class="col-md-4 mb-3">
                  <label class="form-label">Estado</label>
                  <?php $estadoActual = $editando ? $turno['estado'] : 'pendiente'; ?>
                  <select name="estado" class="form-select">
                    <option value="pendiente" <?= $estadoActual === 'pendiente'  ? 'selected' : '' ?>>Pendiente</option>
                    <option value="confirmado" <?= $estadoActual === 'confirmado' ? 'selected' : '' ?>>Confirmado</option>
                    <option value="realizado" <?= $estadoActual === 'realizado'  ? 'selected' : '' ?>>Realizado</option>
                    <option value="cancelado" <?= $estadoActual === 'cancelado'  ? 'selected' : '' ?>>Cancelado</option>
                    <option value="no_asistio" <?= $estadoActual === 'no_asistio' ? 'selected' : '' ?>>No Asistió</option>
                  </select>
                </div>

                <div class="col-md-12 mb-3">
                  <label class="form-label">Descripción / Motivo de visita</label>
                  <textarea name="descripcion"
                    class="form-control"
                    rows="3"
                    placeholder="Ej: Ruido en frenos, service de los 10k..."><?= $editando ? htmlspecialchars($turno['descripcion']) : '' ?></textarea>
                </div>
              </div>

              <button type="submit" class="btn btn-warning">
                <?= $editando ? 'Guardar Cambios' : 'Confirmar Turno' ?>
              </button>
              <a href="listar_turno.php" class="btn btn-secondary ms-2">Cancelar</a>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="../assets/js/registrar_turno.js"></script>
  <script src="../assets/js/styles.js"></script>

</body>

</html>