<?php
require_once '../includes/config_database.php';
require_once '../clases/Clientes.php';

$clientes = Clientes::obtenerTodos($conn);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && !isset($_GET['action'])) {
  $id = intval($_GET['id']);
  header("Location: registrar_cliente.php?id=" . $id);
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../assets/img/logo_64.png">
  <title>Listado de Clientes - Taller Mecánico</title>

  <!-- CSS externos -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

  <!-- Tus estilos (modo claro/oscuro, cards, etc.) -->
  <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
  <div class="container">
    <div class="card">
      <div class="card-header bg-success text-white">
        <h1 class="mb-0">Clientes Registrados
          <img src="../assets/img/logo.png" alt="Logo" style="height:80px; width:80px; border-radius:50%;">
          <button id="btnDarkMode"
            class="btn btn-sm btn-outline-light"
            type="button">
            🌙 Modo oscuro
          </button>
        </h1>
      </div>
      <div class="card-body">

        <!-- Menú (idéntico al de listar_vehiculos) -->
        <?php require_once '../views/navbar.php'; ?>
        <!-- Fin menú -->

        <!-- Mensajes -->
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
            <strong>Error:</strong> <?php echo htmlspecialchars($_GET['error']); ?>
          </div>
        <?php endif; ?>

        <!-- Tabla de Clientes (con mismo estilo que vehículos) -->
        <div class="card mt-4">
          <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Clientes Registrados</h4>
            <a href="registrar_cliente.php" class="btn btn-success">+ Nuevo Cliente</a>
          </div>
          <div class="card-body">
            <div class="table-responsive">
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
                  <?php while ($row = $clientes->fetch()): ?>
                    <?php $estado_color = $row['estado'] == 'activo' ? 'success' : 'secondary'; ?>
                    <tr>
                      <td><?= htmlspecialchars($row['nombre']) ?></td>
                      <td><?= htmlspecialchars($row['apellido']) ?></td>
                      <td><?= htmlspecialchars($row['dni']) ?></td>
                      <td><?= htmlspecialchars($row['telefono']) ?></td>
                      <td><?= htmlspecialchars(isset($row['direccion']) ? $row['direccion'] : '')  ?></td>
                      <td><span class="badge bg-<?= $estado_color ?>"><?= htmlspecialchars($row['estado']) ?></span></td>
                      <td>
                        <a href="../shields/procesar_cliente.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                        <button class="btn btn-sm <?= $row['estado'] == 'activo' ? 'btn-warning' : 'btn-success'; ?>"
                          onclick="cambiarEstadoCliente(<?= $row['id']; ?>, '<?= $row['estado']; ?>')">
                          <?= $row['estado'] == 'activo' ? 'Desactivar' : 'Activar'; ?>
                        </button>
                        <button class="btn btn-sm btn-danger"
                          onclick="eliminarCliente(<?= $row['id']; ?>, '<?= $row['nombre'] . ' ' . $row['apellido']; ?>')">
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

      </div> <!-- /.card-body -->
    </div> <!-- /.card -->
  </div> <!-- /.container -->

  <!-- JS externos -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

  <!-- JS propio -->
  <script src="../assets/js/clientes.js"></script>
  <script src="../assets/js/styles.js"></script> <!-- aquí va el script de modo oscuro -->
</body>

</html>