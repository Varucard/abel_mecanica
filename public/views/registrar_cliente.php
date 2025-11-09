<?php
  require_once '../includes/config_database.php';
  require_once '../clases/Clientes.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrar Cliente - Taller Mecánico</title>
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="card-header bg-success text-white">
        <h1 class="mb-0">Registrar Cliente</h1>
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

          <!-- Mensajes de éxito/error -->
          <?php if (isset($_GET['success']) && $_GET['success'] == 'create'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
              <strong>¡Registro completado!</strong> El cliente ha sido registrado exitosamente.
            </div>
          <?php endif; ?>
          
          <?php if (isset($_GET['success']) && $_GET['success'] == 'delete'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
              <strong>¡Cliente eliminado!</strong> El cliente ha sido eliminado exitosamente.
            </div>
          <?php endif; ?>
          
          <?php if (isset($_GET['success']) && $_GET['success'] == 'estado'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
              <strong>¡Cambio de estado exitoso!</strong> El estado del cliente ha sido modificado.
            </div>
          <?php endif; ?>
          
          <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger" role="alert" id="error-alert">
              <strong>Error al registrar:</strong> <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
          <?php endif; ?>

          <!-- Formulario de Registro -->
          <div class="card mb-4">
            <div class="card-header bg-light">
              <h4>Nuevo Cliente</h4>
            </div>
              <div class="card-body">
                <form action="procesar_cliente.php" method="POST" novalidate>
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label for="nombre" class="form-label">Nombre *</label>
                      <input type="text" class="form-control" id="nombre" name="nombre" 
                              pattern="[A-Za-zÁÉÍÓÚáéíóúñÑ\s]{2,50}" 
                              title="Solo letras, de 2 a 50 caracteres" required>
                    </div>
                    <div class="col-md-6">
                      <label for="apellido" class="form-label">Apellido *</label>
                      <input type="text" class="form-control" id="apellido" name="apellido" 
                        pattern="[A-Za-zÁÉÍÓÚáéíóúñÑ\s]{2,50}" 
                        title="Solo letras, de 2 a 50 caracteres" required>
                    </div>
                  </div>
                    <div class="row mb-3">
                      <div class="col-md-6">
                        <label for="dni" class="form-label">DNI * </label>
                        <input type="text" class="form-control" id="dni" name="dni" 
                          pattern="[0-9]{6,8}" 
                          title="Debe tener entre 6 y 8 dígitos numéricos" 
                          maxlength="8" required>
                      </div>
                      <div class="col-md-6">
                        <label for="telefono" class="form-label">Teléfono * (10 dígitos)</label>
                        <input type="text" class="form-control" id="telefono" name="telefono" 
                          pattern="[0-9]{10}" 
                          title="Debe tener exactamente 10 dígitos numéricos" 
                          maxlength="10" required>
                      </div>
                    </div>
                    <div class="mb-3">
                      <label for="direccion" class="form-label">Dirección *</label>
                      <input type="text" class="form-control" id="direccion" name="direccion" 
                        pattern=".{5,200}" 
                        title="Debe tener entre 5 y 200 caracteres" required>
                    </div>
                    <button type="submit" class="btn btn-success">Registrar Cliente</button>
                </form>
              </div>
          </div>

          <!-- Tabla de Clientes -->
          <div class="card">
            <div class="card-header bg-light">
              <h4>Clientes Registrados</h4>
            </div>
            <div class="card-body">
              <table id="tabla_clientes" class="table table-striped table-bordered">
                <thead>
                  <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>DNI</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                  <tbody>
                    <?php
                    $clientes = Clientes::obtenerTodos($conn);
                    while ($row = $clientes->fetch()) {
                      $estado_color = $row['estado'] == 'activo' ? 'success' : 'secondary';
                      ?>
                      <tr>
                        <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($row['apellido']); ?></td>
                        <td><?php echo htmlspecialchars($row['dni']); ?></td>
                        <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                        <td><?php echo htmlspecialchars($row['direccion']); ?></td>
                        <td><span class="badge bg-<?php echo $estado_color; ?>"><?php echo htmlspecialchars($row['estado']); ?></span></td>
                        <td>
                          <button class="btn btn-sm <?php echo $row['estado'] == 'activo' ? 'btn-warning' : 'btn-success'; ?>" onclick="cambiarEstadoCliente(<?php echo $row['id']; ?>, '<?php echo $row['estado']; ?>')">
                              <?php echo $row['estado'] == 'activo' ? 'Desactivar' : 'Activar'; ?>
                          </button>
                          <button class="btn btn-sm btn-danger" onclick="eliminarCliente(<?php echo $row['id']; ?>, '<?php echo $row['nombre'] . ' ' . $row['apellido']; ?>')">Eliminar</button>
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
  
  <script src="../assets/js/clientes.js"></script>
</body>
</html>

