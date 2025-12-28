<?php
require_once '../includes/config_database.php';
require_once '../clases/Marcas.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../assets/img/logo_64.png">
  <title>Registrar Marca - Taller Mecánico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
  <div class="container">
    <div class="card">
      <div class="card-header bg-primary text-white">
        <h1 class="mb-0">Registrar Marca
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

        <!-- ✅ Mensajes -->
        <?php if (isset($_GET['success'])): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
            <?= htmlspecialchars($_GET['success']); ?>
          </div>
        <?php elseif (isset($_GET['error'])): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
            <?= htmlspecialchars($_GET['error']); ?>
          </div>
        <?php endif; ?>

        <!-- 🧾 Formulario Alta/Edición -->
        <div class="card mb-4 mt-3">
          <div class="card-header bg-light">
            <h4>Nueva Marca</h4>
          </div>
          <div class="card-body">
            <?php
            $marca = null;
            if (isset($_GET['id'])) {
              $marca = Marcas::obtenerPorId($conn, $_GET['id']);
            }
            ?>
            <form action="../shields/procesar_marca.php" method="POST" novalidate>
              <input type="hidden" name="action" value="<?= $marca ? 'actualizar' : 'guardar'; ?>">
              <?php if ($marca): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($marca['id']); ?>">
              <?php endif; ?>

              <div class="mb-3">
                <label for="nombre" class="form-label">Nombre de la Marca *</label>
                <input type="text" class="form-control" id="nombre" name="nombre"
                  value="<?= htmlspecialchars($marca['nombre'] ?? ''); ?>"
                  pattern="[A-Za-zÁÉÍÓÚáéíóúñÑ\s]{2,50}"
                  title="Solo letras, entre 2 y 50 caracteres" required>
              </div>
              <button type="submit" class="btn btn-success">
                <?= $marca ? 'Actualizar Marca' : 'Registrar Marca'; ?>
              </button>
              <?php if ($marca): ?>
                <a href="registrar_marcas.php" class="btn btn-secondary">Cancelar</a>
              <?php endif; ?>
            </form>
          </div>
        </div>

        <!-- 📋 Tabla de Marcas -->
        <div class="card">
          <div class="card-header bg-light">
            <h4>Marcas Registradas</h4>
          </div>
          <div class="card-body">
            <table id="tabla_marcas" class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $marcas = Marcas::obtenerTodos($conn);
                while ($row = $marcas->fetch()) { ?>
                  <tr>
                    <td><?= htmlspecialchars($row['nombre']); ?></td>
                    <td>
                      <a href="registrar_marcas.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                      <button type="button" class="btn btn-sm btn-danger" onclick="eliminarMarca(<?= $row['id']; ?>, '<?= htmlspecialchars($row['nombre']); ?>')">
                        Eliminar
                      </button>
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

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script src="../assets/js/marcas.js"></script>
  <script src="../assets/js/styles.js"></script>

</body>

</html>