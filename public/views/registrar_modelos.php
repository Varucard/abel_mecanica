<?php
require_once '../includes/config_database.php';
require_once '../clases/Modelos.php';
require_once '../clases/Marcas.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../assets/img/logo_64.png">
  <title>Registrar Modelos</title>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-primary text-white">
      <h3 class="mb-0">🚗 Registrar Modelos
        <img src="../assets/img/logo.png" alt="Logo" style="height:80px; width:80px; border-radius: 50%;">
        <button id="btnDarkMode"
                  class="btn btn-sm btn-outline-light"
                  type="button">
            🌙 Modo oscuro
          </button>
      </h3>
    </div>
    <div class="card-body">

      <!-- ✅ NAVBAR (idéntica a las demás vistas, no se toca) -->
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
      <!-- 🔚 FIN NAVBAR -->

      <!-- ✅ Mensajes -->
      <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert" id="success-alert">
          <?= htmlspecialchars($_GET['success']); ?>
        </div>
      <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert" id="error-alert">
          <?= htmlspecialchars($_GET['error']); ?>
        </div>
      <?php endif; ?>

      <!-- 🧾 Formulario Alta/Edición -->
      <div class="card mt-3 mb-4">
        <div class="card-header bg-light">
          <h4><?= isset($_GET['id']) ? 'Editar Modelo' : 'Registrar Nuevo Modelo'; ?></h4>
        </div>
        <div class="card-body">
          <?php
            $modelo = null;
            if (isset($_GET['id'])) {
              $modelo = Modelos::obtenerPorId($conn, $_GET['id']);
            }
            $marcas = Marcas::obtenerTodos($conn);
          ?>
          <form action="../shields/procesar_modelo.php" method="POST" novalidate>
            <input type="hidden" name="action" value="<?= $modelo ? 'actualizar' : 'guardar'; ?>">
            <?php if ($modelo): ?>
              <input type="hidden" name="id" value="<?= htmlspecialchars($modelo['id']); ?>">
            <?php endif; ?>

            <div class="mb-3">
              <label for="marca_id" class="form-label">Marca *</label>
              <select class="form-select" id="marca_id" name="marca_id" required>
                <option value="">Seleccione una marca</option>
                <?php while ($m = $marcas->fetch()): ?>
                  <option value="<?= $m['id']; ?>" 
                    <?= isset($modelo['marca_id']) && $modelo['marca_id'] == $m['id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($m['nombre']); ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="nombre" class="form-label">Nombre del Modelo *</label>
              <input type="text" class="form-control" id="nombre" name="nombre" 
                     value="<?= htmlspecialchars($modelo['nombre'] ?? ''); ?>" 
                     pattern="[A-Za-zÁÉÍÓÚáéíóúñÑ0-9\s-]{2,50}" 
                     title="Entre 2 y 50 caracteres alfanuméricos" required>
            </div>

            <button type="submit" class="btn btn-success">
              <?= $modelo ? 'Actualizar Modelo' : 'Registrar Modelo'; ?>
            </button>
            <?php if ($modelo): ?>
              <a href="registrar_modelos.php" class="btn btn-secondary">Cancelar</a>
            <?php endif; ?>
          </form>
        </div>
      </div>

      <!-- 📋 Tabla de Modelos -->
      <div class="card">
        <div class="card-header bg-light">
          <h4>Modelos Registrados</h4>
        </div>
        <div class="card-body">
          <table id="tabla_modelos" class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $modelos = Modelos::obtenerTodos($conn);
              while ($row = $modelos->fetch()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['marca_nombre']); ?></td>
                  <td><?= htmlspecialchars($row['nombre']); ?></td>
                  <td>
                    <a href="registrar_modelos.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
                    <button type="button" class="btn btn-sm btn-danger" 
                            onclick="eliminarModelo(<?= $row['id']; ?>, '<?= htmlspecialchars($row['nombre']); ?>')">
                      Eliminar
                    </button>
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
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/modelos.js"></script>
  <script src="../assets/js/styles.js"></script>

</body>
</html>
