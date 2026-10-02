<?php
/**
 * @var array<string, mixed>|null $orden
 * @var list<array<string, mixed>> $vehiculos
 * @var list<array<string, mixed>> $servicios
 * @var list<array<string, mixed>> $repuestos
 * @var list<int> $servicioIds
 * @var list<int> $repuestoIds
 */
$vehiculoId = (int) old('vehiculo_id', $orden['vehiculo_id'] ?? 0);
$view->script('ordenes.js');
?>
<div class="card mt-3">
  <div class="card-header bg-light">
    <h4 class="mb-0"><?= $orden ? "Editar orden #{$orden['id']}" : 'Nueva orden' ?></h4>
  </div>
  <div class="card-body">
    <form action="<?= url($orden ? "ordenes/{$orden['id']}" : 'ordenes') ?>" method="POST" id="form_orden">
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
            data-placeholder="Seleccione uno o más servicios">
            <?php foreach ($servicios as $s): ?>
              <option value="<?= (int) $s['id'] ?>" data-precio="<?= (float) $s['precio_base'] ?>" <?= selected(in_array((int) $s['id'], $servicioIds, true)) ?>>
                <?= e($s['nombre']) ?> ($ <?= money($s['precio_base']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label for="repuesto_id" class="form-label">Repuestos</label>
          <select class="form-select js-select2 js-item-precio" name="repuesto_id[]" id="repuesto_id" multiple
            data-placeholder="Seleccione uno o más repuestos">
            <?php foreach ($repuestos as $r): ?>
              <option value="<?= (int) $r['id'] ?>" data-precio="<?= (float) $r['precio'] ?>" <?= selected(in_array((int) $r['id'], $repuestoIds, true)) ?>>
                <?= e($r['nombre']) ?> ($ <?= money($r['precio']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="mb-4">
        <label for="total" class="form-label">Total estimado</label>
        <div class="input-group">
          <span class="input-group-text">$</span>
          <input type="text" class="form-control" id="total" readonly value="<?= $orden ? money($orden['total']) : '0,00' ?>">
        </div>
        <?php if ($orden): ?>
          <small class="form-text text-muted">
            Los ítems que ya estaban en la orden conservan su precio original; el total final lo calcula el sistema al guardar.
          </small>
        <?php endif; ?>
      </div>

      <button type="submit" class="btn btn-warning"><?= $orden ? 'Actualizar orden' : 'Crear orden' ?></button>
      <a href="<?= url('ordenes') ?>" class="btn btn-secondary">Volver al listado</a>
    </form>
  </div>
</div>
