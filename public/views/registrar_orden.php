<?php
  require_once '../includes/config_database.php';
  require_once '../clases/OrdenServicios.php';
  require_once '../clases/Vehiculos.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../assets/img/logo_64.png">
  <title>Registrar Orden - Taller Mecánico</title>
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="card-header bg-warning text-dark">
        <h1 class="mb-0">Crear Orden de Servicio
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

        <!-- Mensajes de éxito/error -->
        <?php if (isset($_GET['success'])): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
            <strong>¡Orden creada!</strong> La orden de servicio ha sido registrada exitosamente.
          </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
          <div class="alert alert-danger" role="alert" id="error-alert">
            <strong>Error al crear la orden:</strong> <?php echo htmlspecialchars($_GET['error']); ?>
          </div>
        <?php endif; ?>

        <!-- Formulario de Registro -->
        <div class="card mb-4">
          <div class="card-header bg-light">
            <h4>Nueva Orden</h4>
          </div>
          <div class="card-body">
            <form action="../shields/procesar_orden.php" method="POST" id="form_orden" novalidate>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="vehiculo_id" class="form-label">Vehículo *</label>
                  <select class="form-control select2" id="vehiculo_id" name="vehiculo_id" required>
                    <option value="">Seleccione un vehículo</option>
                    <?php
                    $vehiculos = Vehiculos::obtenerParaSelect();
                    while ($vehiculo = $vehiculos->fetch()) {
                      echo '<option value="' . $vehiculo['id'] . '">' . 
                      htmlspecialchars($vehiculo['patente'] . ' - ' . $vehiculo['cliente'] . ' (' . $vehiculo['marca'] . ' ' . $vehiculo['modelo'] . ')') . 
                      '</option>';
                    }
                    ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label for="servicio_id" class="form-label">Servicio *</label>
                  <select class="form-control select2" id="servicio_id" name="servicio_id[]" multiple="multiple" required>
                    <?php
                    $servicios = OrdenServicios::obtenerServicios();
                    while ($servicio = $servicios->fetch()) {
                      echo '<option value="' . $servicio['id'] . '" data-precio="' . $servicio['precio_base'] . '">' . 
                      htmlspecialchars($servicio['nombre']) . ' ($' . number_format($servicio['precio_base'], 2) . ')' . 
                      '</option>';
                    }
                    ?>
                  </select>
                  <div class="form-text">Seleccione uno o varios servicios (mantener Ctrl/Cmd para múltiples). El costo se calculará automáticamente.</div>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="costo" class="form-label">Costo total *</label>
                  <input type="number" class="form-control" id="costo" name="costo" 
                    step="0.01" min="0.01" 
                    title="Suma automática de los precios base de los servicios seleccionados" required readonly>
                </div>
                <div class="col-md-6">
                  <label for="fecha_realizado" class="form-label">Fecha *</label>
                  <input type="date" class="form-control" id="fecha_realizado" name="fecha_realizado" 
                    max="<?php echo date('Y-m-d'); ?>" 
                    required>
                </div>
              </div>
              <button type="submit" class="btn btn-warning">Crear Orden</button>
              <a href="listar_orden.php" class="btn btn-secondary">Ver Órdenes Registradas</a>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="../assets/js/ordenes.js"></script>
</body>
</html>
