<?php
  ob_start();
  require_once '../includes/config_database.php';
  require_once '../clases/Vehiculos.php';
  require_once '../clases/Clientes.php';

  // Manejar petición AJAX para obtener modelos
  if (isset($_GET['action']) && $_GET['action'] == 'get_modelos' && isset($_GET['marca_id'])) {
    ob_end_clean();
    header('Content-Type: application/json');
    $marca_id = intval($_GET['marca_id']);
    $modelos = Vehiculos::obtenerModelosPorMarcaJSON($marca_id);
    echo json_encode($modelos);
    exit;
  }

  // Manejar petición AJAX para verificar vehículos del cliente
  if (isset($_GET['action']) && $_GET['action'] == 'verificar_cliente' && isset($_GET['cliente_id'])) {
    ob_end_clean();
    header('Content-Type: application/json');
    $cliente_id = intval($_GET['cliente_id']);
    $total = Vehiculos::contarPorCliente($cliente_id);
    echo json_encode(['total' => $total]);
    exit;
  }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrar Vehículo - Taller Mecánico</title>
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="card-header bg-info text-white">
        <h1 class="mb-0">Registrar Vehículo</h1>
      </div>
        <div class="card-body">

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

          <!-- Inicio mensajes -->
          <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
              <strong>¡Registro completado!</strong> El vehículo ha sido registrado exitosamente.
            </div>
          <?php endif; ?>
          
          <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger" role="alert" id="error-alert">
              <strong>Error al registrar:</strong> <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
          <?php endif; ?>
          <!-- Fin mensajes -->

          <!-- Formulario de Registro -->
          <div class="card mb-4">
            <div class="card-header bg-light">
              <h4>Nuevo Vehículo</h4>
            </div>
            <div class="card-body">
              <form action="procesar_vehiculo.php" method="POST" id="form_vehiculo" novalidate>
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="cliente_id" class="form-label">Cliente *</label>
                    <select class="form-control select2" id="cliente_id" name="cliente_id" required>
                        <option value="">Seleccione un cliente</option>
                        <?php
                        $clientes = Clientes::obtenerTodos($conn);
                        while ($cliente = $clientes->fetch()) {
                          echo '<option value="' . $cliente['id'] . '">' . 
                          htmlspecialchars($cliente['apellido'] . ', ' . $cliente['nombre'] . ' - DNI: ' . $cliente['dni']) . 
                          '</option>';
                        }
                        ?>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label for="marca_id" class="form-label">Marca *</label>
                    <select class="form-control" id="marca_id" name="marca_id" required>
                      <option value="">Seleccione una marca</option>
                      <?php
                      $marcas = Vehiculos::obtenerMarcas();
                      while ($marca = $marcas->fetch()) {
                        echo '<option value="' . $marca['id'] . '">' . htmlspecialchars($marca['nombre']) . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="modelo_id" class="form-label">Modelo *</label>
                    <select class="form-control" id="modelo_id" name="modelo_id" required disabled>
                      <option value="">Seleccione una marca primero</option>
                    </select>
                  </div>
                <div class="col-md-3">
              <label for="anio" class="form-label">Año *</label>
              <input type="number" class="form-control" id="anio" name="anio" 
                min="1940" max="2025" 
                title="Debe estar entre 1940 y 2025" required>
              </div>
                <div class="col-md-3">
                  <label for="patente" class="form-label">Patente *</label>
                  <input type="text" class="form-control" id="patente" name="patente" 
                    pattern="[A-Za-z]{2,3}[0-9]{3}[A-Za-z]{2}|[A-Za-z]{3}[0-9]{3}" 
                    title="Formato: AB123CD o ABC123" 
                    style="text-transform: uppercase" required>
                </div>
              </div>
                <button type="submit" class="btn btn-info">Registrar Vehículo</button>
              </form>
            </div>
          </div>

          <!-- Tabla de Vehículos -->
          <div class="card">
            <div class="card-header bg-light">
              <h4>Vehículos Registrados</h4>
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
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script src="../assets/js/vehiculos.js"></script>
</body>
</html>

