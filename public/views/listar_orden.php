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
        <div class="btn-group w-100" role="group" style="gap: 5px;">
          <div class="dropdown flex-fill">
            <a href="#" class="btn btn-primary w-100">🏠 Inicio</a>
            <div class="dropdown-menu">
              <a href="registrar_servicio.php">Servicios</a>
              <a href="registrar_repuesto.php">Repuestos</a>
              <a href="registrar_marcas.php">Marcas</a>
              <a href="registrar_modelos.php">Modelos</a>
            </div>
          </div>
          <div class="dropdown flex-fill">
            <a href="#" class="btn btn-success w-100">👤 Clientes</a>
            <div class="dropdown-menu">
              <a href="registrar_cliente.php">Registrar Cliente</a>
              <a href="listar_clientes.php">Ver Clientes</a>
            </div>
          </div>
          <div class="dropdown flex-fill">
            <a href="#" class="btn btn-info w-100">🚗 Vehículos</a>
            <div class="dropdown-menu">
              <a href="registrar_vehiculo.php">Registrar Vehiculo</a>
              <a href="listar_vehiculos.php">Ver Vehiculos</a>
            </div>
          </div>
          <div class="dropdown flex-fill">
            <a href="#" class="btn btn-warning w-100">📝 Ordenes</a>
            <div class="dropdown-menu">
              <a href="registrar_orden.php">Registrar Orden</a>
              <a href="listar_orden.php">Ver Ordenes</a>
            </div>
          </div>
        </div>
        <!-- Fin menú -->

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
                  <th>Cliente</th>
                  <th>Vehículo</th>
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
                  $estadoClass = $row['estado'] === 'finalizado' ? 'success' : 'warning';
                  $estadoTexto = $row['estado'] === 'finalizado' ? 'Finalizado' : 'Pendiente';
                  ?>
                  <tr>
                    <td><?= htmlspecialchars($row['cliente']); ?></td>
                    <td><?= htmlspecialchars($row['vehiculo']); ?></td>
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
                    <td>$<?= number_format($row['costo'], 2); ?></td>
                    <td><?= date('d/m/Y', strtotime($row['created_at'])); ?></td>
                    <td><span class="badge bg-<?= $estadoClass; ?>"><?= $estadoTexto; ?></span></td>
                    <td>
                      <a href="../shields/procesar_orden.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                      <?php if ($row['estado'] === 'pendiente'): ?>
                        <button class="btn btn-sm btn-success" onclick="cambiarEstadoOrden(<?= $row['id']; ?>, 'finalizado')">
                          Finalizar
                        </button>
                      <?php else: ?>
                        <span class="text-muted"></span>
                      <?php endif; ?>
                        <a href="ver_presupuesto.php?id=<?= $row['id']; ?>" 
                          class="btn btn-sm btn-info">
                          Ver
                        </a>
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
