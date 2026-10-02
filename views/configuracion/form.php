<?php
/** @var array{taller: array<string, string>, trabajo: array<string, mixed>} $config */
$taller = $config['taller'];
$trabajo = $config['trabajo'];
?>
<form action="<?= url('configuracion') ?>" method="POST">
  <?= csrf_field() ?>

  <div class="card mb-4 mt-3">
    <div class="card-header bg-light">
      <h4 class="mb-0">Datos del taller</h4>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="nombre" class="form-label">Nombre del taller *</label>
          <input type="text" class="form-control" id="nombre" name="nombre" required
            value="<?= e(old('nombre', $taller['nombre'])) ?>">
        </div>
        <div class="col-md-6 mb-3">
          <label for="cuit" class="form-label">CUIT *</label>
          <input type="text" class="form-control" id="cuit" name="cuit" required
            pattern="[0-9]{2}-?[0-9]{8}-?[0-9]" title="Formato: 20-12345678-9"
            value="<?= e(old('cuit', $taller['cuit'])) ?>">
        </div>
      </div>

      <div class="mb-3">
        <label for="direccion" class="form-label">Dirección *</label>
        <input type="text" class="form-control" id="direccion" name="direccion" required
          value="<?= e(old('direccion', $taller['direccion'])) ?>">
      </div>

      <div class="row">
        <div class="col-md-4 mb-3">
          <label for="telefono" class="form-label">Teléfono *</label>
          <input type="text" class="form-control" id="telefono" name="telefono" required
            value="<?= e(old('telefono', $taller['telefono'])) ?>">
        </div>
        <div class="col-md-4 mb-3">
          <label for="whatsapp" class="form-label">WhatsApp *</label>
          <input type="text" class="form-control" id="whatsapp" name="whatsapp" required
            placeholder="+5491136359867" title="+549 + código de área + número, sin espacios"
            value="<?= e(old('whatsapp', $taller['whatsapp'])) ?>">
        </div>
        <div class="col-md-4 mb-3">
          <label for="email" class="form-label">Email *</label>
          <input type="email" class="form-control" id="email" name="email" required
            value="<?= e(old('email', $taller['email'])) ?>">
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header bg-light">
      <h4 class="mb-0">Condiciones de trabajo</h4>
    </div>
    <div class="card-body">
      <div class="row">
        <?php foreach (['validez' => 'Validez del presupuesto', 'garantia' => 'Garantía', 'tiempo_estimado' => 'Tiempo estimado'] as $campo => $etiqueta): ?>
          <div class="col-md-4 mb-3">
            <label for="<?= $campo ?>" class="form-label"><?= $etiqueta ?> (días) *</label>
            <input type="number" class="form-control" id="<?= $campo ?>" name="<?= $campo ?>" min="1" max="365" required
              value="<?= e(old($campo, $trabajo[$campo])) ?>">
          </div>
        <?php endforeach; ?>
      </div>

      <div class="mb-3">
        <label for="forma_pago" class="form-label">Formas de pago (una por línea) *</label>
        <textarea class="form-control" id="forma_pago" name="forma_pago" rows="5" required><?= e(old('forma_pago', implode("\n", $trabajo['forma_pago']))) ?></textarea>
      </div>

      <div class="mb-3">
        <label for="observaciones" class="form-label">Observaciones del presupuesto (una por línea)</label>
        <textarea class="form-control" id="observaciones" name="observaciones" rows="4"><?= e(old('observaciones', implode("\n", $trabajo['observaciones']))) ?></textarea>
      </div>

      <div class="mb-3">
        <label for="mensaje_legal" class="form-label">Mensaje legal</label>
        <textarea class="form-control" id="mensaje_legal" name="mensaje_legal" rows="2"><?= e(old('mensaje_legal', $trabajo['mensaje_legal'])) ?></textarea>
      </div>
    </div>
  </div>

  <div class="text-end">
    <button type="submit" class="btn btn-success btn-lg">💾 Guardar configuración</button>
  </div>
</form>
