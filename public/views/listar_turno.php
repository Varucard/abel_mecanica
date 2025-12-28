<?php
require_once '../includes/config_database.php';
require_once '../Clases/Turnos.php';

// Procesar cambio de estado rápido vía AJAX
if (isset($_POST['cambiar_estado'])) {
  $turno = new Turno();
  $turno->setId($_POST['id']);
  $turno->setEstado($_POST['nuevo_estado']);
  if ($turno->cambiarEstado($conn)) {
    echo json_encode(['status' => 'success']);
  } else {
    echo json_encode(['status' => 'error']);
  }
  exit;
}

$turnos = Turno::obtenerTodos($conn);
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../assets/img/logo_64.png">
  <title>Agenda de Turnos - Taller Mecánico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
  <div class="container">
    <div class="card">
      <div class="card-header bg-warning text-dark">
        <h1 class="mb-0">Agenda de Turnos
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

        <!-- Mensajes de éxito/error -->
        <?php if (isset($_GET['success'])): ?>
          <div class="alert alert-success alert-dismissible fade show mt-3" role="alert" id="success-alert">
            <?php
            switch ($_GET['success']) {
              case 'create':
                echo 'Turno agendado exitosamente';
                break;
              case 'update':
                echo 'Turno actualizado exitosamente';
                break;
              case 'estado':
                echo 'Estado del turno actualizado';
                break;
              default:
                echo 'Operación exitosa';
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
          <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert" id="error-alert">
            <?php
            switch ($_GET['error']) {
              case 'estado':
                echo 'Error al cambiar el estado del turno';
                break;
              case 'estado_invalido':
                echo 'Estado inválido';
                break;
              case 'turno_invalido':
                echo 'Turno no encontrado';
                break;
              default:
                echo htmlspecialchars($_GET['error']);
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <!-- Tabla de Turnos -->
        <div class="card mt-4">
          <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Turnos Registrados</h4>
            <a href="registrar_turno.php" class="btn btn-warning">+ Nuevo Turno</a>
          </div>
          <div class="card-body">
            <table id="tabla_turnos" class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>Fecha/Hora</th>
                  <th class="col-cliente">Cliente</th>
                  <th class="col-vehiculo">Vehículo</th>
                  <th>Descripción</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($t = $turnos->fetch()):
                  $claseEstadoFila = 'estado-' . $t['estado'];
                ?>
                  <tr>
                    <td>
                      <strong><?= date('d/m/Y', strtotime($t['fecha'])) ?></strong><br>
                      <small class="text-muted"><?= date('H:i', strtotime($t['hora'])) ?> hs</small>
                    </td>
                    <td class="col-achicada"><?= htmlspecialchars($t['cliente_nombre']) ?></td>
                    <td class="col-achicada"><?= htmlspecialchars($t['vehiculo_info']) ?></td>
                    <td><small><?= htmlspecialchars($t['descripcion']) ?></small></td>

                    <td>
                      <select class="form-select form-select-sm estado-turno-select <?= $claseEstadoFila; ?>"
                        data-id="<?= $t['id']; ?>">
                        <option value="pendiente" <?= $t['estado'] === 'pendiente'  ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="confirmado" <?= $t['estado'] === 'confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                        <option value="realizado" <?= $t['estado'] === 'realizado'  ? 'selected' : ''; ?>>Realizado</option>
                        <option value="cancelado" <?= $t['estado'] === 'cancelado'  ? 'selected' : ''; ?>>Cancelado</option>
                        <option value="no_asistio" <?= $t['estado'] === 'no_asistio' ? 'selected' : ''; ?>>No Asistió</option>
                      </select>
                    </td>

                    <td class="col-acciones">
                      <a href="registrar_turno.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                      <a href="registrar_turno.php?id=<?= $t['id']; ?>" class="btn btn-sm btn-info">Ver</a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script src="../assets/js/turnos.js"></script>
  <script src="../assets/js/styles.js"></script>

</body>

</html>