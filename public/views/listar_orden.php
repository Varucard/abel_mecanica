<?php
  require_once '../includes/config_database.php';
  require_once '../clases/OrdenServicios.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../assets/img/logo_64.png">
  <title>Listado de Órdenes - Taller Mecánico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="card-header bg-warning text-dark">
        <h1 class="mb-0">Listado de Órdenes de Servicio
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
                echo 'Orden creada exitosamente';
                break;
              case 'update':
                echo 'Orden actualizada exitosamente';
                break;
              case 'estado':
                echo 'Estado de la orden actualizado';
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
                echo 'Error al cambiar el estado de la orden';
                break;
              case 'estado_invalido':
                echo 'Estado inválido';
                break;
              case 'orden_invalida':
                echo 'Orden no encontrada';
                break;
              default:
                echo htmlspecialchars($_GET['error']);
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <!-- Tabla de Órdenes -->
        <div class="card mt-4">
          <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Órdenes Registradas</h4>
            <a href="registrar_orden.php" class="btn btn-warning">+ Crear Orden</a>
          </div>
          <div class="card-body">
            <table id="tabla_ordenes" class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th class="col-cliente">Cliente</th>
                  <th class="col-vehiculo">Vehículo</th>
                  <th>Servicio(s) - Repuesto(s)</th>
                  <th>Costo</th>
                  <th>Fecha</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $ordenes = OrdenServicios::obtenerTodas();
                while ($row = $ordenes->fetch()) {
                  $claseEstadoFila = 'estado-' . $row['estado']; // ej: estado-pendiente
                  ?>
                  <tr>
                    <td class="col-achicada"><?= htmlspecialchars($row['cliente']); ?></td>
                    <td class="col-achicada"><?= htmlspecialchars($row['vehiculo']); ?></td>
                    <td>
                      <?php if (!empty($row['servicios'])): ?>
                        <?= htmlspecialchars($row['servicios']); ?>
                      <?php endif; ?>

                      <?php if (!empty($row['repuestos'])): ?>
                        <div class="small text-muted mt-1">
                          <strong>Repuestos:</strong> <?= htmlspecialchars($row['repuestos']); ?>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td>$<?= number_format($row['costo'], 2, ',', '.'); ?></td>
                    <td><?= date('d/m/Y', strtotime($row['created_at'])); ?></td>
                    
                    <!-- Estado como select con color -->
                    <td>
                      <select class="form-select form-select-sm estado-orden-select <?= $claseEstadoFila; ?>"
                              data-id="<?= $row['id']; ?>">
                        <option value="pendiente"  <?= $row['estado'] === 'pendiente'  ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="en_proceso" <?= $row['estado'] === 'en_proceso' ? 'selected' : ''; ?>>En proceso</option>
                        <option value="finalizado" <?= $row['estado'] === 'finalizado' ? 'selected' : ''; ?>>Finalizado</option>
                        <option value="cancelado"  <?= $row['estado'] === 'cancelado'  ? 'selected' : ''; ?>>Cancelado</option>
                      </select>
                    </td>

                    <!-- Acciones -->
                    <td class="col-acciones">
                      <a href="../shields/procesar_orden.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                      <a href="ver_presupuesto.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-info">Ver</a>
                    </td>
                  </tr>
                <?php } ?>
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
  <script src="../assets/js/ordenes.js"></script>
  <script src="../assets/js/styles.js"></script>

</body>
</html>