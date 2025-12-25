<?php
require_once '../includes/config_database.php';
require_once '../clases/OrdenServicios.php';
require_once '../clases/Vehiculos.php';

/* =========================
   CARGA PARA EDITAR
========================= */
$orden = null;
$serviciosSeleccionados = [];
$repuestosSeleccionados = [];

if (isset($_GET['id'])) {
  $id = (int) $_GET['id'];

  $stmt = $conn->prepare("SELECT * FROM ordenes WHERE id = ?");
  $stmt->execute([$id]);
  $orden = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$orden) {
    header("Location: listar_orden.php?error=orden_no_encontrada");
    exit;
  }

  $stmt = $conn->prepare("
    SELECT servicio_id, repuesto_id
    FROM ordenes_servicios
    WHERE orden_id = ?
  ");
  $stmt->execute([$id]);

  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($row['repuesto_id']) {
      $repuestosSeleccionados[] = (int)$row['repuesto_id'];
    } else {
      $serviciosSeleccionados[] = (int)$row['servicio_id'];
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../assets/img/logo_64.png">
  <title><?= $orden ? 'Editar Orden' : 'Registrar Orden' ?> - Taller Mecánico</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-warning text-dark">
      <h1 class="mb-0">
        <?= $orden ? 'Editar Orden de Servicio' : 'Crear Orden de Servicio' ?>
        <img src="../assets/img/logo.png" style="height:60px;width:60px;border-radius:50%">
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

      <!-- FORMULARIO -->
      <div class="card shadow-sm">
        <div class="card-header bg-light">
          <h4 class="mb-0"><?= $orden ? 'Editar Orden' : 'Nueva Orden' ?></h4>
        </div>

        <div class="card-body">
          <form action="../shields/procesar_orden.php" method="POST" id="form_orden">

            <?php if ($orden): ?>
              <input type="hidden" name="id" value="<?= $orden['id'] ?>">
            <?php endif; ?>

            <!-- Vehículo -->
            <div class="mb-3">
              <label class="form-label">Vehículo *</label>
              <select class="form-control select2" name="vehiculo_id" id="vehiculo_id" required>
                <option value="">Seleccione un vehículo</option>
                <?php
                $vehiculos = Vehiculos::obtenerParaSelect();
                while ($v = $vehiculos->fetch()):
                ?>
                  <option value="<?= $v['id'] ?>"
                    <?= ($orden && $orden['vehiculo_id'] == $v['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($v['patente'].' - '.$v['cliente'].' ('.$v['marca'].' '.$v['modelo'].')') ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>

            <!-- Servicios / Repuestos -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Servicios *</label>
                <select class="form-control select2" name="servicio_id[]" id="servicio_id" multiple>
                  <?php
                  $servicios = OrdenServicios::obtenerServicios();
                  while ($s = $servicios->fetch()):
                  ?>
                    <option value="<?= $s['id'] ?>"
                      data-precio="<?= $s['precio_base'] ?>"
                      <?= in_array($s['id'], $serviciosSeleccionados) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($s['nombre']) ?>
                      ($<?= number_format($s['precio_base'],2,',','.') ?>)
                    </option>
                  <?php endwhile; ?>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Repuestos</label>
                <select class="form-control select2" name="repuesto_id[]" id="repuesto_id" multiple>
                  <?php
                  $repuestos = OrdenServicios::obtenerRepuestos();
                  while ($r = $repuestos->fetch()):
                  ?>
                    <option value="<?= $r['id'] ?>"
                      data-precio="<?= $r['precio'] ?>"
                      <?= in_array($r['id'], $repuestosSeleccionados) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($r['nombre']) ?>
                      ($<?= number_format($r['precio'],2,',','.') ?>)
                    </option>
                  <?php endwhile; ?>
                </select>
              </div>
            </div>

            <!-- Total -->
            <div class="mb-4">
              <label class="form-label">Costo total</label>
              <input type="number"
                     class="form-control"
                     name="costo"
                     step="0.01"
                     readonly
                     required
                     value="<?= $orden ? $orden['total'] : '' ?>">
            </div>

            <button type="submit" class="btn btn-warning">
              <?= $orden ? 'Actualizar Orden' : 'Crear Orden' ?>
            </button>

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
