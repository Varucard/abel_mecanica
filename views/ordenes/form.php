<?php
/**
 * @var array<string, mixed>|null $orden
 * @var list<array<string, mixed>> $vehiculos
 * @var list<array<string, mixed>> $servicios
 * @var list<array<string, mixed>> $repuestos
 * @var array{servicio: array<int, array<string, mixed>>, repuesto: array<int, array<string, mixed>>} $detalle
 * @var array<string, mixed> $precarga  datos sugeridos al crear (p. ej. desde un turno)
 * @var int|null $kmVehiculo
 * @var array<string, mixed> $service  configuración de intervalos
 */
$valor = fn(string $campo) => old($campo, $orden[$campo] ?? $precarga[$campo] ?? '');
$vehiculoId = (int) old('vehiculo_id', $orden['vehiculo_id'] ?? $vehiculoSugerido);
$mecanicoId = (int) old('mecanico_id', $orden['mecanico_id'] ?? 0);
// Al editar, el mecánico asignado debe figurar aunque hoy esté inactivo.
if ($orden && $orden['mecanico_id'] && !in_array((int) $orden['mecanico_id'], array_map('intval', array_column($mecanicos, 'id')), true)) {
  $mecanicos[] = ['id' => $orden['mecanico_id'], 'apellido' => $orden['mecanico'], 'nombre' => null, 'puesto' => 'inactivo'];
}
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

      <div class="row mb-3">
      <div class="col-md-8">
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
      <div class="col-md-4">
        <label for="mecanico_id" class="form-label">Mecánico asignado</label>
        <select class="form-select js-select2" name="mecanico_id" id="mecanico_id" data-placeholder="Sin asignar">
          <option value=""></option>
          <?php foreach ($mecanicos as $m): ?>
            <option value="<?= (int) $m['id'] ?>" <?= selected($mecanicoId === (int) $m['id']) ?>>
              <?= e(trim("{$m['apellido']}" . ($m['nombre'] ? ", {$m['nombre']}" : '') . " ({$m['puesto']})")) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
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

      <?php if (!empty($precarga['turno_id'])): ?>
        <input type="hidden" name="turno_id" value="<?= (int) $precarga['turno_id'] ?>">
        <div class="alert alert-info py-2">Esta orden se crea desde un turno: al guardarla, el turno queda como <strong>realizado</strong>.</div>
      <?php endif; ?>

      <div class="row mb-3">
        <div class="col-md-3">
          <label for="km_ingreso" class="form-label">Km al ingresar</label>
          <input type="number" class="form-control" id="km_ingreso" name="km_ingreso" min="0" max="9999999"
            placeholder="<?= $kmVehiculo !== null ? 'Último: ' . number_format((float) $kmVehiculo, 0, ',', '.') : 'Ej: 125000' ?>"
            value="<?= e($valor('km_ingreso')) ?>">
        </div>
        <div class="col-md-9">
          <label for="diagnostico" class="form-label">Motivo / diagnóstico</label>
          <textarea class="form-control" id="diagnostico" name="diagnostico" rows="2"
            placeholder="Lo que reporta el cliente y lo que se detectó"><?= e($valor('diagnostico')) ?></textarea>
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

      <div class="mb-3">
        <label for="trabajo_realizado" class="form-label">Trabajo realizado</label>
        <textarea class="form-control" id="trabajo_realizado" name="trabajo_realizado" rows="2"
          placeholder="Se imprime en el comprobante de entrega"><?= e($valor('trabajo_realizado')) ?></textarea>
      </div>

      <div class="row mb-3 align-items-end">
        <div class="col-md-3">
          <label for="proximo_service_km" class="form-label">Próximo service (km)</label>
          <input type="number" class="form-control" id="proximo_service_km" name="proximo_service_km" min="1" max="9999999"
            value="<?= e($valor('proximo_service_km')) ?>">
        </div>
        <div class="col-md-3">
          <label for="proximo_service_fecha" class="form-label">Próximo service (fecha)</label>
          <input type="date" class="form-control" id="proximo_service_fecha" name="proximo_service_fecha" min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
            value="<?= e($valor('proximo_service_fecha')) ?>">
        </div>
        <div class="col-md-6">
          <button type="button" class="btn btn-outline-secondary btn-sm" id="sugerir_service"
            data-km="<?= (int) $service['intervalo_km'] ?>" data-meses="<?= (int) $service['intervalo_meses'] ?>">
            Sugerir (+<?= number_format((float) $service['intervalo_km'], 0, ',', '.') ?> km / <?= (int) $service['intervalo_meses'] ?> meses)
          </button>
          <small class="form-text text-muted d-block">Se le avisa al cliente cuando se acerca la fecha.</small>
        </div>
      </div>

      <div class="mb-3">
        <label for="notas_internas" class="form-label">Notas internas <small class="text-muted">(no se imprimen ni las ve el cliente)</small></label>
        <textarea class="form-control" id="notas_internas" name="notas_internas" rows="2"><?= e($valor('notas_internas')) ?></textarea>
      </div>

      <button type="submit" class="btn btn-warning"><?= $orden ? 'Actualizar orden' : 'Crear orden' ?></button>
      <a href="<?= url('ordenes') ?>" class="btn btn-secondary">Volver al listado</a>
    </form>
  </div>
</div>
