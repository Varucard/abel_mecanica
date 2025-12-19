<?php
  require_once '../includes/config_database.php';
  require_once '../clases/Repuestos.php';

  $serv = null;
  if (isset($_GET['id'])) {
    $serv = Repuestos::obtenerPorId($conn, $_GET['id']);
  }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../assets/img/logo_64.png">
  <title>Registrar Repuesto - Taller Mecánico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="card-header bg-primary text-white">
        <h1 class="mb-0">Registrar Repuesto
          <img src="../assets/img/logo.png" alt="Logo" style="height:80px; width:80px; border-radius: 50%;">
          <button id="btnDarkMode"
                  class="btn btn-sm btn-outline-light"
                  type="button">
            🌙 Modo oscuro
          </button>
        </h1>
      </div>
      <div class="card-body">

        <!-- === Menú (idéntico al de clientes) === -->
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
        <!-- === Fin menú === -->

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

        <!-- === Formulario Alta/Edición === -->
        <div class="card mb-4">
          <div class="card-header bg-light">
            <h4><?php echo isset($serv) ? 'Editar Repuesto' : 'Nuevo Repuesto'; ?></h4>
          </div>
          <div class="card-body">
            <form action="../shields/procesar_repuesto.php" method="POST" novalidate>
              <input type="hidden" name="action" value="<?php echo isset($serv) ? 'actualizar' : 'guardar'; ?>">
              <input type="hidden" name="id" value="<?php echo $serv['id'] ?? ''; ?>">

              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="nombre" class="form-label">Nombre *</label>
                  <input type="text" class="form-control" id="nombre" name="nombre" required
                         value="<?php echo htmlspecialchars($serv['nombre'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                  <label for="precio" class="form-label">Precio *</label>
                  <input type="number" class="form-control" id="precio" name="precio" step="0.01" required
                         value="<?php echo htmlspecialchars($serv['precio'] ?? ''); ?>">
                </div>
              </div>

              <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php
                  echo htmlspecialchars($serv['descripcion'] ?? '');
                ?></textarea>
              </div>

              <button type="submit" class="btn btn-success">
                <?php echo isset($serv) ? 'Actualizar Repuesto' : 'Registrar Repuesto'; ?>
              </button>
            </form>
          </div>
        </div>

        <!-- === Tabla de Repuestos === -->
        <div class="card">
          <div class="card-header bg-light">
            <h4>Repuestos Registrados</h4>
          </div>
          <div class="card-body">
            <table id="tabla_repuestos" class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Descripción</th>
                  <th>Precio</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $repuestos = Repuestos::obtenerTodos($conn);
                  foreach ($repuestos as $row):
                ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                    <td>$<?php echo number_format($row['precio'], 2, ',', '.'); ?></td>
                    <td>
                      <a href="registrar_repuesto.php?id=<?php echo $row['id']; ?>"
                        class="btn btn-sm btn-primary">Editar</a>

                      <button class="btn btn-sm btn-danger"
                              onclick="eliminarRepuesto(
                                <?php echo (int)$row['id']; ?>,
                                '<?php echo htmlspecialchars(addslashes($row['nombre'])); ?>'
                              )">
                        Eliminar
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
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
  <script src="../assets/js/repuestos.js"></script>
  <script src="../assets/js/styles.js"></script>

</body>
</html>
