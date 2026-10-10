<?php
/**
 * @var array<string, mixed>|null $orden
 * @var list<array<string, mixed>> $vehiculos
 * @var list<array<string, mixed>> $servicios
 * @var list<array<string, mixed>> $repuestos
 * @var array{servicio: array<int, array<string, mixed>>, repuesto: array<int, array<string, mixed>>, pieza: list<array<string, mixed>>} $detalle
 * @var array<string, mixed> $precarga  datos sugeridos al crear (p. ej. desde un turno)
 * @var int|null $kmVehiculo
 * @var array<string, mixed> $service  configuración de intervalos
 * @var list<array<string, mixed>> $combos
 */
// Al editar, el mecánico asignado debe figurar aunque hoy esté inactivo.
if ($orden && $orden['mecanico_id'] && !in_array((int) $orden['mecanico_id'], array_map('intval', array_column($mecanicos, 'id')), true)) {
  $mecanicos[] = ['id' => $orden['mecanico_id'], 'apellido' => $orden['mecanico'], 'nombre' => null, 'puesto' => 'inactivo'];
}
$view->script('ordenes.js');
// "Al terminar el trabajo" se abre solo si ya tiene algo cargado.
$cierre = ['trabajo_realizado', 'proximo_service_km', 'proximo_service_fecha', 'notas_internas'];
$abrirCierre = array_filter($cierre, fn($campo) => (string) old($campo, $orden[$campo] ?? $precarga[$campo] ?? '') !== '') !== [];
?>
<div class="card mt-3">
  <div class="card-header">
    <h4 class="mb-0"><?= $orden ? "Editar orden #{$orden['id']}" : 'Nueva orden' ?></h4>
  </div>
  <div class="card-body">
    <form action="<?= url($orden ? "ordenes/{$orden['id']}" : 'ordenes') ?>" method="POST" id="form_orden" data-borrador
      data-detalle="<?= e(json_encode($detalle, JSON_FORCE_OBJECT)) ?>"
      data-modos="<?= e(json_encode(\App\Services\OrdenService::MODOS_REPUESTO)) ?>">
      <?= csrf_field() ?>

      <div class="row mb-3">
        <?php if ($orden): ?>
          <?php // Al editar, el vehículo no se cambia: la orden, sus pagos y su historial son de ese auto. ?>
          <div class="col-md-8">
            <span class="form-label d-block">Vehículo</span>
            <p class="form-control-plaintext fw-semibold mb-0"><?= e("{$orden['patente']} - {$orden['marca']} {$orden['modelo']}") ?></p>
            <input type="hidden" name="vehiculo_id" value="<?= (int) $orden['vehiculo_id'] ?>">
          </div>
        <?php else: ?>
        <?= $view->partial('componentes/campo', [
          'nombre' => 'vehiculo_id', 'etiqueta' => 'Vehículo *', 'tipo' => 'select', 'buscable' => true, 'columna' => 'col-md-8',
          'placeholder' => 'Escribí la patente o el apellido', 'valor' => $orden['vehiculo_id'] ?? $vehiculoSugerido, 'atributos' => ['required' => true],
          'opciones' => array_column(array_map(fn($v) => [(int) $v['id'], "{$v['patente']} - {$v['cliente']} ({$v['marca']} {$v['modelo']})"], $vehiculos), 1, 0),
          'ayuda' => '¿El auto no está en la lista? Usá «Llegó un auto» y lo cargás ahí mismo.',
        ]) ?>
        <?php endif; ?>
        <?= $view->partial('componentes/campo', [
          'nombre' => 'mecanico_id', 'etiqueta' => 'Mecánico asignado', 'tipo' => 'select', 'buscable' => true, 'columna' => 'col-md-4',
          'placeholder' => 'Sin asignar', 'valor' => $orden['mecanico_id'] ?? '',
          'opciones' => array_column(array_map(fn($m) => [
            (int) $m['id'], trim("{$m['apellido']}" . ($m['nombre'] ? ", {$m['nombre']}" : '') . " ({$m['puesto']})"),
          ], $mecanicos), 1, 0),
        ]) ?>
      </div>

      <?php if (!empty($precarga['turno_id'])): ?>
        <input type="hidden" name="turno_id" value="<?= (int) $precarga['turno_id'] ?>">
        <div class="alert alert-info py-2">Esta orden se crea desde un turno: al guardarla, el turno queda como <strong>realizado</strong>.</div>
      <?php endif; ?>

      <div class="row mb-3">
        <?= $view->partial('componentes/campo', [
          'nombre' => 'km_ingreso', 'etiqueta' => 'Kilómetros al ingresar', 'tipo' => 'number', 'columna' => 'col-md-3',
          'valor' => $orden['km_ingreso'] ?? $precarga['km_ingreso'] ?? '',
          'atributos' => ['min' => 0, 'max' => 9999999, 'placeholder' => $kmVehiculo !== null ? 'Último: ' . number_format((float) $kmVehiculo, 0, ',', '.') : 'Ej: 125000'],
        ]) ?>
        <?= $view->partial('componentes/campo', [
          'nombre' => 'diagnostico', 'etiqueta' => '¿Qué le pasa? (motivo / diagnóstico)', 'tipo' => 'textarea', 'columna' => 'col-md-9',
          'valor' => $orden['diagnostico'] ?? $precarga['diagnostico'] ?? '',
          'atributos' => ['rows' => 2, 'placeholder' => 'Lo que cuenta el cliente y lo que se detectó'],
        ]) ?>
      </div>

      <h5 class="mt-4 mb-1">Trabajos y repuestos</h5>
      <p class="small text-muted mb-3">Si todavía no sabés qué hay que hacer, podés guardar la orden sin cargarlos y completarlos después.</p>

      <div class="row mb-3">
        <?= $view->partial('componentes/campo', [
          'nombre' => 'servicio_id[]', 'id' => 'servicio_id', 'etiqueta' => 'Servicios', 'tipo' => 'select', 'buscable' => true,
          'columna' => 'col-md-6', 'clase' => 'js-item-precio', 'placeholder' => 'Escribí para buscar (ej: aceite)',
          'atributos' => ['multiple' => true, 'data-tipo' => 'servicio', 'data-chip-corto' => true],
          'valor' => array_keys($detalle['servicio']), 'usarAnterior' => false,
          'opciones' => array_column(array_map(fn($s) => [(int) $s['id'], [
            'texto' => "{$s['nombre']} ($ " . money($s['precio_base']) . ')', 'atributos' => ['data-precio' => (float) $s['precio_base']],
          ]], $servicios), 1, 0),
        ]) ?>
        <?= $view->partial('componentes/campo', [
          'nombre' => 'repuesto_id[]', 'id' => 'repuesto_id', 'etiqueta' => 'Repuestos', 'tipo' => 'select', 'buscable' => true,
          'columna' => 'col-md-6', 'clase' => 'js-item-precio', 'placeholder' => 'Escribí para buscar (ej: filtro)',
          'atributos' => ['multiple' => true, 'data-tipo' => 'repuesto', 'data-chip-corto' => true],
          'valor' => array_keys($detalle['repuesto']), 'usarAnterior' => false,
          'opciones' => array_column(array_map(fn($r) => [(int) $r['id'], [
            'texto' => "{$r['nombre']} ($ " . money($r['precio']) . ') · stock ' . qty($r['stock_actual']),
            'atributos' => ['data-precio' => (float) $r['precio'], 'data-costo' => $r['precio_costo'] !== null ? (float) $r['precio_costo'] : null],
          ]], $repuestos), 1, 0),
        ]) ?>
      </div>

      <?php if ($combos !== []): ?>
        <div class="mb-3 ancho-max-420">
          <label for="agregar_combo" class="form-label">O sumá un combo armado</label>
          <select class="form-select" id="agregar_combo">
            <option value="">Elegí un combo (ej: service completo)…</option>
            <?php foreach ($combos as $c): ?>
              <option value="<?= (int) $c['id'] ?>" data-items="<?= e(json_encode(array_map(fn($i) => [
                'tipo' => $i['repuesto_id'] !== null ? 'repuesto' : 'servicio',
                'id' => (int) ($i['repuesto_id'] ?? $i['servicio_id']),
                'cantidad' => (float) $i['cantidad'],
              ], $c['items']))) ?>"><?= e($c['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>

      <div class="table-responsive mb-3">
        <table class="table table-sm align-middle" id="detalle_orden">
          <thead>
            <tr>
              <th>Ítem</th>
              <th class="ancho-120">Cantidad</th>
              <th class="ancho-170">Precio unitario</th>
              <th class="text-end ancho-150">Subtotal</th>
              <th><span class="visually-hidden">Quitar</span></th>
            </tr>
          </thead>
          <tbody>
            <tr class="js-sin-items"><td colspan="5" class="text-muted">Todavía no hay trabajos ni repuestos. Elegilos arriba y acá aparecen con su precio.</td></tr>
          </tbody>
        </table>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="agregar_pieza">
          <?= icono('plus-lg') ?> Repuesto que trae el cliente y no está en la lista
        </button>
        <small class="form-text text-muted d-block">
          En cada repuesto podés elegir si es <strong>del taller</strong>, <strong>a costo</strong> (se cobra lo que te salió, sin ganancia)
          o si <strong>lo trae el cliente</strong> (figura sin precio y no se descuenta del stock).
        </small>
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

      <details class="mas-datos mb-4" <?= $abrirCierre ? 'open' : '' ?>>
        <summary>Al terminar el trabajo <span class="small text-muted">(trabajo realizado, próximo service, notas internas)</span></summary>
        <div class="pt-3">
      <?= $view->partial('componentes/campo', [
        'nombre' => 'trabajo_realizado', 'etiqueta' => 'Trabajo realizado', 'tipo' => 'textarea', 'columna' => 'mb-3',
        'valor' => $orden['trabajo_realizado'] ?? $precarga['trabajo_realizado'] ?? '',
        'atributos' => ['rows' => 2, 'placeholder' => 'Se imprime en el comprobante de entrega'],
      ]) ?>

      <div class="row mb-3 align-items-end">
        <?= $view->partial('componentes/campo', [
          'nombre' => 'proximo_service_km', 'etiqueta' => 'Próximo service (km)', 'tipo' => 'number', 'columna' => 'col-md-3',
          'valor' => $orden['proximo_service_km'] ?? $precarga['proximo_service_km'] ?? '',
          'atributos' => ['min' => 1, 'max' => 9999999],
        ]) ?>
        <?= $view->partial('componentes/campo', [
          'nombre' => 'proximo_service_fecha', 'etiqueta' => 'Próximo service (fecha)', 'tipo' => 'date', 'columna' => 'col-md-3',
          'valor' => $orden['proximo_service_fecha'] ?? $precarga['proximo_service_fecha'] ?? '',
          'atributos' => ['min' => date('Y-m-d', strtotime('+1 day'))],
        ]) ?>
        <div class="col-md-6">
          <button type="button" class="btn btn-outline-secondary btn-sm" id="sugerir_service"
            data-km="<?= (int) $service['intervalo_km'] ?>" data-meses="<?= (int) $service['intervalo_meses'] ?>">
            Sugerir (+<?= number_format((float) $service['intervalo_km'], 0, ',', '.') ?> km / <?= (int) $service['intervalo_meses'] ?> meses)
          </button>
          <small class="form-text text-muted d-block">Se le avisa al cliente cuando se acerca la fecha.</small>
        </div>
      </div>

      <?= $view->partial('componentes/campo', [
        'nombre' => 'notas_internas', 'etiqueta' => 'Notas internas', 'tipo' => 'textarea', 'columna' => 'mb-3',
        'etiquetaHtml' => 'Notas internas <small class="text-muted">(no se imprimen ni las ve el cliente)</small>',
        'valor' => $orden['notas_internas'] ?? $precarga['notas_internas'] ?? '', 'atributos' => ['rows' => 2],
      ]) ?>
        </div>
      </details>

      <button type="submit" class="btn btn-seccion"><?= icono('check-lg') ?> <?= $orden ? 'Guardar cambios' : 'Guardar orden' ?></button>
      <a href="<?= url($orden ? "ordenes/{$orden['id']}" : 'ordenes') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </form>
  </div>
</div>
