<?php
  require_once '../includes/config_database.php';
  require_once '../clases/OrdenServicios.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Taller Mecánico - Gestión</title>
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="card-header bg-primary text-white">
        <h1 class="mb-0">🔧 Taller Mecánico - Sistema de Gestión</h1>
      </div>
      <div class="card-body">

        <!-- Mensajes -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 'order'): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
            <strong>¡Orden creada!</strong> La orden de servicio ha sido registrada exitosamente.
          </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success']) && $_GET['success'] == 'estado'): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
            <strong>¡Estado actualizado!</strong> La orden ha sido marcada como finalizada.
          </div>
        <?php endif; ?>
        <!-- Fin mensajes -->

        <!-- Menú -->
        <div class="btn-group w-100" role="group" style="gap: 5px;">
          <div class="dropdown flex-fill">
            <a href="menu_principal.php" class="btn btn-primary w-100">🏠 Inicio</a>
            <div class="dropdown-menu">
              <a href="configurar_sistema.php">Configuración</a>
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
              <a href="listar_clientes.php">Ver Vehiculos</a>
            </div>
          </div>
          <div class="dropdown flex-fill">
            <a href="#" class="btn btn-warning w-100">📝 Ordenes</a>
            <div class="dropdown-menu">
              <a href="registrar_orden.php">Registrar Orden</a>
              <a href="listar_clientes.php">Ver Ordenes</a>
            </div>
          </div>
        </div>
        <!-- Fin menú -->
        
        <!-- Inicio tabla -->
        <h3>Órdenes de Servicio</h3>
        <table id="tabla_ordenes" class="table table-striped table-bordered">
          <thead>
            <tr>
              <th>Cliente</th>
              <th>Vehículo</th>
              <th>Patente</th>
              <th>Servicio</th>
              <th>Costo</th>
              <th>Fecha</th>
              <th>Fecha salida</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
            <tbody>
              <?php
              $ordenes = OrdenServicios::verOrdenes();
              while ($row = $ordenes->fetch()) {
                $estado_color = '';
                switch($row['estado_orden']) {
                  case 'pendiente': $estado_color = 'warning'; break;
                  case 'en_proceso': $estado_color = 'info'; break;
                  case 'finalizado': $estado_color = 'success'; break;
                  case 'cancelado': $estado_color = 'danger'; break;
                }
                ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['cliente_completo']); ?></td>
                  <td><?php echo htmlspecialchars($row['marca'] . ' ' . $row['modelo']); ?></td>
                  <td><?php echo htmlspecialchars($row['patente']); ?></td>
                  <td><?php echo htmlspecialchars($row['servicio']); ?></td>
                  <td>$<?php echo number_format($row['costo'], 2); ?></td>
                  <td><?php echo htmlspecialchars($row['fecha_realizado']); ?></td>
                  <td><?php echo !empty($row['fecha_salida']) ? htmlspecialchars($row['fecha_salida']) : '-'; ?></td>
                  <td><span class="badge bg-<?php echo $estado_color; ?>"><?php echo htmlspecialchars($row['estado_orden']); ?></span></td>
                  <td>
                    <?php if ($row['estado_orden'] != 'finalizado' && $row['estado_orden'] != 'cancelado'): ?>
                      <button class="btn btn-sm btn-success" onclick="cambiarEstadoOrden(<?php echo $row['orden_id']; ?>, 'finalizado')">
                        Marcar como Finalizado
                      </button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

  <script src="../assets/js/ordenes.js"></script>
</body>
</html>

