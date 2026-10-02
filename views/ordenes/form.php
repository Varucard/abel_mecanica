<?php
/**
 * @var array<string, mixed>|null $orden
 * @var list<array<string, mixed>> $vehiculos
 * @var list<array<string, mixed>> $servicios
 * @var list<array<string, mixed>> $repuestos
 * @var array{servicio: array<int, array<string, mixed>>, repuesto: array<int, array<string, mixed>>} $detalle
 */
$vehiculoId = (int) old('vehiculo_id', $orden['vehiculo_id'] ?? $vehiculoSugerido);
$view->script('ordenes.js');
?>
<div class="card mt-3">
  <div class="card-header bg-light">
    <h4 class="mb-0"><?= $orden ? "Editar orden #{$orden['id']}" : 'Nueva orden' ?></h4>
  </div>
  <div class="card-body">
    <form action="<?= url($orden ? "ordenes/{$orden['id']}" : 'ordenes') ?>" method="POST" id="form_orden"
      data-detalle="<?= e(json_encode($detalle, JSON_FORCE_OBJECT)) ?>">
      <?= csrf_field() ?>

      <div class="mb-3">
        <label for="vehiculo_id" class="form-label">Vehículo *</label>
        <select class="form-select js-select2" name="vehiculo_id" id="vehiculo_id" data-placeholder="Seleccione un vehículo" required>
          <option value=""></option>
          <?php foreach ($vehiculos as $v): ?>
            <option value="<?= (int) $v['id'] ?>" <?= selected($vehiculoId === (int) $v['id']) ?>>
              <?= e("{$v['patente']} - {$v['cliente']} ({$v['marca']} {$v['modelo']})") ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label for="servicio_id" class="form-label">Servicios *</label>
          <select class="form-select js-select2 js-item-precio" name="servicio_id[]" id="servicio_id" multiple
            data-tipo="servicio" data-placeholder="Seleccione uno o más servicios">
            <?php foreach ($servicios as $s): ?>
              <option value="<?= (int) $s['id'] ?>" data-precio="<?= (float) $s['precio_base'] ?>" <?= selected(isset($detalle['servicio'][(int) $s['id']])) ?>>
                <?= e($s['nombre']) ?> ($ <?= money($s['precio_base']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label for="repuesto_id" class="form-label">Repuestos</label>
          <select class="form-select js-select2 js-item-precio" name="repuesto_id[]" id="repuesto_id" multiple
            data-tipo="repuesto" data-placeholder="Seleccione uno o más repuestos">
            <?php foreach ($repuestos as $r): ?>
              <option value="<?= (int) $r['id'] ?>" data-precio="<?= (float) $r['precio'] ?>" <?= selected(isset($detalle['repuesto'][(int) $r['id']])) ?>>
                <?= e($r['nombre']) ?> ($ <?= money($r['precio']) ?>) · stock <?= qty($r['stock_actual']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="table-responsive mb-3">
        <table class="table table-sm align-middle" id="detalle_orden">
          <thead>
            <tr>
              <th>Ítem</th>
              <th style="width: 120px;">Cantidad</th>
              <th style="width: 170px;">Precio unitario</th>
              <th class="text-end" style="width: 150px;">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <tr class="js-sin-items"><td colspan="4" class="text-muted">Seleccioná servicios y repuestos para ver el detalle.</td></tr>
          </tbody>
        </table>
      </div>

      <div class="mb-4">
        <label for="total" class="form-label">Total estimado</label>
        <div class="input-group">
          <span class="input-group-text">$</span>
          <input type="text" class="form-control" id="total" readonly value="<?= $orden ? money($orden['total']) : '0,00' ?>">
        </div>
        <small class="form-text text-muted">
          El precio sugerido es el del catálogo; los ítems que ya estaban en la orden conservan el precio con que se cargaron.
        </small>
      </div>

      <button type="submit" class="btn btn-warning"><?= $orden ? 'Actualizar orden' : 'Crear orden' ?></button>
      <a href="<?= url('ordenes') ?>" class="btn btn-secondary">Volver al listado</a>
    </form>
  </div>
</div>
