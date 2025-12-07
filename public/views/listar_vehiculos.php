<?php
  require_once '../includes/config_database.php';
  require_once '../clases/Vehiculos.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../assets/img/logo_64.png">
  <title>Listado de Vehículos - Taller Mecánico</title>
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="card-header bg-info text-white">
        <h1 class="mb-0">Listado de Vehículos
          <img src="../assets/img/logo.png" alt="Logo" style="height:80px; width:80px; border-radius: 50%;">
        </h1>
      </div>
        <div class="card-body">

          <!-- Menú -->
        <div class="btn-group w-100" role="group" style="gap: 5px;">
          <div class="dropdown flex-fill">
            <a href="menu_principal.php" class="btn btn-primary w-100">🏠 Inicio</a>
            <div class="dropdown-menu">
              <a href="registrar_servicio.php">Servicios</a>
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

          <!-- Tabla de Vehículos -->
          <div class="card mt-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
              <h4 class="mb-0">Vehículos Registrados</h4>
              <a href="registrar_vehiculo.php" class="btn btn-info">+ Registrar Vehículo</a>
            </div>
            <div class="card-body">
              <table id="tabla_vehiculos" class="table table-striped table-bordered">
                <thead>
                  <tr>
                    <th>Cliente</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Año</th>
                    <th>Patente</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $vehiculos = Vehiculos::obtenerTodos();
                    while ($row = $vehiculos->fetch()) {
                      ?>
                      <tr>
                        <td><?php echo htmlspecialchars($row['cliente']); ?></td>
                        <td><?php echo htmlspecialchars($row['marca']); ?></td>
                        <td><?php echo htmlspecialchars($row['modelo']); ?></td>
                        <td><?php echo htmlspecialchars($row['anio']); ?></td>
                        <td><?php echo htmlspecialchars($row['patente']); ?></td>
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
  <script src="../assets/js/listar_vehiculos.js"></script>

</body>
</html>
