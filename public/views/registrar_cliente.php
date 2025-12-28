<?php
require_once '../includes/config_database.php';
require_once '../clases/Clientes.php';

// Si viene un id por GET, cargamos el cliente para editar
$cliente = null;
if (isset($_GET['id'])) {
  $cliente = Clientes::obtenerPorId($conn, $_GET['id']);
  if (!$cliente) {
    header("Location: listar_clientes.php?error=Cliente no encontrado");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../assets/img/logo_64.png">
  <title><?php echo $cliente ? "Editar Cliente" : "Registrar Cliente"; ?> - Taller Mecánico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
  <div class="container">
    <div class="card">
      <div class="card-header bg-success text-white">
        <h1 class="mb-0"><?php echo $cliente ? "Editar Cliente" : "Registrar Cliente"; ?>
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

        <!-- Mensajes -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 'create'): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
            <strong>¡Registro completado!</strong> El cliente ha sido registrado exitosamente.
          </div>
        <?php endif; ?>

        <?php if (isset($_GET['success']) && $_GET['success'] == 'update'): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
            <strong>¡Cliente actualizado!</strong> Los datos se guardaron correctamente.
          </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
          <div class="alert alert-danger" role="alert" id="error-alert">
            <strong>Error:</strong> <?php echo htmlspecialchars($_GET['error']); ?>
          </div>
        <?php endif; ?>

        <!-- Formulario -->
        <div class="card mb-4">
          <div class="card-header bg-light">
            <h4><?php echo $cliente ? 'Editar Cliente' : 'Nuevo Cliente'; ?></h4>
          </div>
          <div class="card-body">
            <form action="../shields/procesar_cliente.php" method="POST" novalidate>
              <?php if ($cliente): ?>
                <input type="hidden" name="id" value="<?php echo $cliente['id']; ?>">
              <?php endif; ?>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="nombre" class="form-label">Nombre *</label>
                  <input type="text" class="form-control" id="nombre" name="nombre"
                    value="<?php echo htmlspecialchars($cliente['nombre'] ?? ''); ?>"
                    pattern="[A-Za-zÁÉÍÓÚáéíóúñÑ\s]{2,50}" required>
                </div>
                <div class="col-md-6">
                  <label for="apellido" class="form-label">Apellido *</label>
                  <input type="text" class="form-control" id="apellido" name="apellido"
                    value="<?php echo htmlspecialchars($cliente['apellido'] ?? ''); ?>"
                    pattern="[A-Za-zÁÉÍÓÚáéíóúñÑ\s]{2,50}" required>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="dni" class="form-label">DNI *</label>
                  <input type="text" class="form-control" id="dni" name="dni"
                    value="<?php echo htmlspecialchars($cliente['dni'] ?? ''); ?>"
                    pattern="[0-9]{6,8}" maxlength="8" <?php echo $cliente ? 'readonly' : ''; ?> required>
                </div>
                <div class="col-md-6">
                  <label for="telefono" class="form-label">Teléfono *</label>
                  <input type="text" class="form-control" id="telefono" name="telefono"
                    value="<?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>"
                    pattern="[0-9]{10}" maxlength="10" required>
                </div>
              </div>

              <div class="mb-3">
                <div class="col-md-6">
                  <label for="email" class="form-label">Email (opcional)</label>
                  <input type="email" class="form-control" id="email" name="email"
                    value="<?= isset($cliente['email']) ? htmlspecialchars($cliente['email']) : ''; ?>"
                    placeholder="ejemplo@correo.com">
                </div>
              </div>

              <div class="mb-3">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" id="direccion" name="direccion"
                  value="<?php echo htmlspecialchars($cliente['direccion'] ?? ''); ?>"
                  pattern=".{5,200}">
              </div>

              <button type="submit" class="btn btn-success">
                <?php echo $cliente ? "Actualizar Cliente" : "Registrar Cliente"; ?>
              </button>
              <a href="listar_clientes.php" class="btn btn-secondary">Ver Clientes</a>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/styles.js"></script>

</body>

</html>