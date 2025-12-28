<?php
require_once '../includes/config_database.php';
require_once '../includes/config_workshop.php';

// Cargar valores actuales
$taller = $config_taller;
$trabajo = $config_trabajo;
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../assets/img/logo_64.png">
  <title>Configuración del Taller - Taller Mecánico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
  <div class="container">
    <div class="card">
      <div class="card-header bg-primary text-white">
        <h1 class="mb-0">Configuración del Taller
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
          <div class="alert alert-success alert-dismissible fade show mt-3" role="alert" id="success-alert">
            Configuración actualizada correctamente
          </div>
        <?php elseif (isset($_GET['error'])): ?>
          <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert" id="error-alert">
            <?= htmlspecialchars($_GET['error']); ?>
          </div>
        <?php endif; ?>

        <!-- 🧾 Formulario de Configuración -->
        <form action="../shields/procesar_configuracion.php" method="POST" novalidate>

          <!-- Datos del Taller -->
          <div class="card mb-4 mt-3">
            <div class="card-header bg-light">
              <h4>Datos del Taller</h4>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="nombre" class="form-label">Nombre del Taller *</label>
                  <input type="text" class="form-control" id="nombre" name="nombre"
                    value="<?= htmlspecialchars($taller['nombre']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="cuit" class="form-label">CUIT *</label>
                  <input type="text" class="form-control" id="cuit" name="cuit"
                    value="<?= htmlspecialchars($taller['cuit']); ?>"
                    pattern="[0-9\-]{11,13}"
                    title="Formato: 20-12345678-9" required>
                </div>
              </div>

              <div class="mb-3">
                <label for="direccion" class="form-label">Dirección *</label>
                <input type="text" class="form-control" id="direccion" name="direccion"
                  value="<?= htmlspecialchars($taller['direccion']); ?>" required>
              </div>

              <div class="row">
                <div class="col-md-4 mb-3">
                  <label for="telefono" class="form-label">Teléfono *</label>
                  <input type="text" class="form-control" id="telefono" name="telefono"
                    value="<?= htmlspecialchars($taller['telefono']); ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                  <label for="whatsapp" class="form-label">WhatsApp *</label>
                  <input type="text" class="form-control" id="whatsapp" name="whatsapp"
                    value="<?= htmlspecialchars($taller['whatsapp']); ?>"
                    placeholder="+5491136359867"
                    title="Formato: +549 + código de área + número (sin espacios)" required>
                </div>
                <div class="col-md-4 mb-3">
                  <label for="email" class="form-label">Email *</label>
                  <input type="email" class="form-control" id="email" name="email"
                    value="<?= htmlspecialchars($taller['email']); ?>" required>
                </div>
              </div>
            </div>
          </div>

          <!-- Configuración de Trabajo -->
          <div class="card mb-4">
            <div class="card-header bg-light">
              <h4>Configuración de Trabajo</h4>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-4 mb-3">
                  <label for="validez" class="form-label">Validez del Presupuesto (días) *</label>
                  <input type="number" class="form-control" id="validez" name="validez"
                    value="<?= htmlspecialchars($trabajo['validez']); ?>"
                    min="1" max="365" required>
                </div>
                <div class="col-md-4 mb-3">
                  <label for="garantia" class="form-label">Garantía (días) *</label>
                  <input type="number" class="form-control" id="garantia" name="garantia"
                    value="<?= htmlspecialchars($trabajo['garantia']); ?>"
                    min="1" max="365" required>
                </div>
                <div class="col-md-4 mb-3">
                  <label for="tiempo_estimado" class="form-label">Tiempo Estimado (días) *</label>
                  <input type="number" class="form-control" id="tiempo_estimado" name="tiempo_estimado"
                    value="<?= htmlspecialchars($trabajo['tiempo_estimado']); ?>"
                    min="1" max="365" required>
                </div>
              </div>

              <div class="mb-3">
                <label for="forma_pago" class="form-label">Formas de Pago (una por línea) *</label>
                <textarea class="form-control" id="forma_pago" name="forma_pago" rows="5" required><?= htmlspecialchars(implode("\n", $trabajo['forma_pago'])); ?></textarea>
                <small class="text-muted">Escribí cada forma de pago en una línea separada</small>
              </div>

              <div class="mb-3">
                <label for="observaciones" class="form-label">Observaciones (una por línea)</label>
                <textarea class="form-control" id="observaciones" name="observaciones" rows="4"><?= htmlspecialchars(implode("\n", $trabajo['observaciones'])); ?></textarea>
                <small class="text-muted">Escribí cada observación en una línea separada</small>
              </div>

              <div class="mb-3">
                <label for="mensaje_legal" class="form-label">Mensaje Legal</label>
                <textarea class="form-control" id="mensaje_legal" name="mensaje_legal" rows="2"><?= htmlspecialchars($trabajo['mensaje_legal']); ?></textarea>
              </div>
            </div>
          </div>

          <div class="text-end">
            <button type="submit" class="btn btn-success btn-lg">
              💾 Guardar Configuración
            </button>
          </div>

        </form>

      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/styles.js"></script>
  <script>
    // Ocultar alertas después de 5 segundos
    $(document).ready(function() {
      if ($('#success-alert').length) {
        $('#success-alert').delay(5000).fadeOut('slow');
      }
      if ($('#error-alert').length) {
        $('#error-alert').delay(5000).fadeOut('slow');
      }
    });
  </script>

</body>

</html>