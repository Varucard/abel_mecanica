<?php
/**
 * @var array<string, mixed>|null $combo
 * @var array{servicio: array<int, array<string, mixed>>, repuesto: array<int, array<string, mixed>>} $detalle
 * @var list<array<string, mixed>> $servicios
 * @var list<array<string, mixed>> $repuestos
 */
if (session()->hasOldInput()) {
  foreach (['servicio', 'repuesto'] as $tipo) {
    $detalle[$tipo] = [];
    $cantidades = (array) old("cantidad_{$tipo}", []);
    foreach ((array) old("{$tipo}_id", []) as $itemId) {
      $detalle[$tipo][(int) $itemId] = ['cantidad' => $cantidades[$itemId] ?? 1];
    }
  }
}
$activo = $combo === null || (session()->hasOldInput() ? old('activo') : $combo['activo']);
$view->script('ordenes.js');
?>
<div class="card mt-3">
  <div class="card-header bg-light"><h4 class="mb-0"><?= $combo ? 'Editar combo' : 'Nuevo combo' ?></h4></div>
  <div class="card-body">
    <form action="<?= url($combo ? "combos/{$combo['id']}" : 'combos') ?>" method="POST" id="form_orden" data-sin-precio="1"
      data-detalle="<?= e(json_encode($detalle, JSON_FORCE_OBJECT)) ?>">
      <?= csrf_field() ?>
      <div class="row">
        <div class="col-md-5 mb-3">
          <label for="nombre" class="form-label">Nombre *</label>
          <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" required placeholder="Ej: Service 10.000 km"
            value="<?= e(old('nombre', $combo['nombre'] ?? '')) ?>">
        </div>
        <div class="col-md-7 mb-3">
          <label for="descripcion" class="form-label">Descripción</label>
          <input type="text" class="form-control" id="descripcion" name="descripcion" maxlength="255"
            value="<?= e(old('descripcion', $combo['descripcion'] ?? '')) ?>">
        </div>
        <div class="col-md-6 mb-3">
          <label for="servicio_id" class="form-label">Servicios</label>
          <select class="form-select js-select2 js-item-precio" name="servicio_id[]" id="servicio_id" multiple data-tipo="servicio" data-placeholder="Elegí servicios">
            <?php foreach ($servicios as $s): ?>
              <option value="<?= (int) $s['id'] ?>" data-precio="<?= (float) $s['precio_base'] ?>" <?= selected(isset($detalle['servicio'][(int) $s['id']])) ?>><?= e($s['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6 mb-3">
          <label for="repuesto_id" class="form-label">Repuestos</label>
          <select class="form-select js-select2 js-item-precio" name="repuesto_id[]" id="repuesto_id" multiple data-tipo="repuesto" data-placeholder="Elegí repuestos">
            <?php foreach ($repuestos as $r): ?>
              <option value="<?= (int) $r['id'] ?>" data-precio="<?= (float) $r['precio'] ?>" <?= selected(isset($detalle['repuesto'][(int) $r['id']])) ?>><?= e($r['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="table-responsive mb-3">
        <table class="table table-sm align-middle" id="detalle_orden">
          <thead><tr><th>Ítem</th><th style="width: 140px;">Cantidad</th><th class="text-end" style="width: 160px;">Subtotal actual</th></tr></thead>
          <tbody><tr class="js-sin-items"><td colspan="3" class="text-muted">Elegí servicios y repuestos.</td></tr></tbody>
        </table>
      </div>
      <p><strong>Total a precios actuales: $ <span id="total_combo">0,00</span></strong></p>

      <?php if ($combo): ?>
        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1" <?= $activo ? 'checked' : '' ?>>
          <label class="form-check-label" for="activo">Combo activo (aparece en las órdenes)</label>
        </div>
      <?php endif; ?>
      <button type="submit" class="btn btn-primary"><?= $combo ? 'Guardar cambios' : 'Crear combo' ?></button>
      <a href="<?= url('combos') ?>" class="btn btn-secondary">Volver</a>
    </form>
  </div>
</div>
