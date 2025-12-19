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

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

<div class="container">
  <div class="card">

    <div class="card-header bg-warning text-dark">
      <h1 class="mb-0">
        Crear Orden de Servicio
        <img src="../assets/img/logo.png" style="height:80px;width:80px;border-radius:50%">
        <button id="btnDarkMode" class="btn btn-sm btn-outline-dark">🌙 Modo oscuro</button>
      </h1>
    </div>

    <div class="card-body">

      <!-- MENÚ -->
      <div class="btn-group w-100 mb-3" style="gap:5px">
        <div class="dropdown flex-fill">
          <button class="btn btn-primary w-100 dropdown-toggle" data-bs-toggle="dropdown">🏠 Inicio</button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="registrar_servicio.php">Servicios</a></li>
            <li><a class="dropdown-item" href="registrar_repuesto.php">Repuestos</a></li>
            <li><a class="dropdown-item" href="registrar_marcas.php">Marcas</a></li>
            <li><a class="dropdown-item" href="registrar_modelos.php">Modelos</a></li>
          </ul>
        </div>

        <div class="dropdown flex-fill">
          <button class="btn btn-success w-100 dropdown-toggle" data-bs-toggle="dropdown">👤 Clientes</button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="registrar_cliente.php">Registrar Cliente</a></li>
            <li><a class="dropdown-item" href="listar_clientes.php">Ver Clientes</a></li>
          </ul>
        </div>

        <div class="dropdown flex-fill">
          <button class="btn btn-info w-100 dropdown-toggle" data-bs-toggle="dropdown">🚗 Vehículos</button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="registrar_vehiculo.php">Registrar Vehículo</a></li>
            <li><a class="dropdown-item" href="listar_vehiculos.php">Ver Vehículos</a></li>
          </ul>
        </div>

        <div class="dropdown flex-fill">
          <button class="btn btn-warning w-100 dropdown-toggle" data-bs-toggle="dropdown">📝 Órdenes</button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="registrar_orden.php">Registrar Orden</a></li>
            <li><a class="dropdown-item" href="listar_orden.php">Ver Órdenes</a></li>
          </ul>
        </div>
      </div>

      <!-- === Mensajes === -->
        <?php if (isset($_GET['success'])): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
            <?php echo htmlspecialchars($_GET['success']); ?>
          </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
          <div class="alert alert-danger" role="alert" id="error-alert">
            <strong>Error:</strong> <?php echo htmlspecialchars($_GET['error']); ?>
          </div>
        <?php endif; ?>

      <!-- FORMULARIO -->
      <div class="card mb-4">
        <div class="card-header bg-light">
          <h4>Nueva Orden</h4>
        </div>
        <div class="card-body">

          <form action="../shields/procesar_orden.php" method="POST" id="form_orden" novalidate>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Vehículo *</label>
                <select class="form-control select2" name="vehiculo_id" required>
                  <option value="">Seleccione un vehículo</option>
                  <?php
                    $vehiculos = Vehiculos::obtenerParaSelect();
                    while ($v = $vehiculos->fetch()) {
                      echo '<option value="'.$v['id'].'">' .
                        htmlspecialchars($v['patente'].' - '.$v['cliente'].' ('.$v['marca'].' '.$v['modelo'].')') .
                      '</option>';
                    }
                  ?>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Fecha *</label>
                <input type="date" class="form-control" name="fecha_realizado"
                       max="<?= date('Y-m-d') ?>" required>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Servicios *</label>
                <select class="form-control select2" name="servicio_id[]" multiple>
                  <?php
                    $servicios = OrdenServicios::obtenerServicios();
                    while ($s = $servicios->fetch()) {
                      echo '<option value="'.$s['id'].'" data-precio="'.$s['precio_base'].'">' .
                        htmlspecialchars($s['nombre']) .
                        ' ($'.number_format($s['precio_base'],2,',','.').')' .
                      '</option>';
                    }
                  ?>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Repuestos</label>
                <select class="form-control select2" name="repuesto_id[]" multiple>
                  <?php
                    $repuestos = OrdenServicios::obtenerRepuestos();
                    while ($r = $repuestos->fetch()) {
                      echo '<option value="'.$r['id'].'" data-precio="'.$r['precio'].'">' .
                        htmlspecialchars($r['nombre']) .
                        ' ($'.number_format($r['precio'],2,',','.').')' .
                      '</option>';
                    }
                  ?>
                </select>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Costo total *</label>
                <input type="number" class="form-control" name="costo" step="0.01" readonly required>
              </div>
            </div>

            <button type="submit" class="btn btn-warning">Crear Orden</button>
            <a href="listar_orden.php" class="btn btn-secondary">Ver Órdenes</a>

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
<script src="../assets/js/styles.js"></script>

</body>
</html>
