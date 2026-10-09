<?php
/**
 * @var array<string, mixed>|null $turno
 * @var list<array<string, mixed>> $clientes
 * @var list<array<string, mixed>> $vehiculos  vehículos del cliente seleccionado
 * @var list<\App\Enums\EstadoTurno> $estados
 * @var \App\Support\HorarioAtencion $horario
 */
$clienteId = (int) old('cliente_id', $turno['cliente_id'] ?? $clienteSugerido);
$vehiculoId = (int) old('vehiculo_id', $turno['vehiculo_id'] ?? 0);
// Con un solo auto no hay nada que elegir (p. ej., al volver de cargar un cliente nuevo).
if ($vehiculoId === 0 && count($vehiculos) === 1) {
  $vehiculoId = (int) $vehiculos[0]['id'];
}
$view->script('turnos.js');
?>
<div class="card mt-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h4 class="mb-0"><?= $turno ? "Editar turno #{$turno['id']}" : 'Nuevo turno' ?></h4>
  </div>
  <div class="card-body">
    <form action="<?= url($turno ? "turnos/{$turno['id']}" : 'turnos') ?>" method="POST" id="form_turno" data-borrador
      data-vehiculos-url="<?= e(url('clientes/{id}/vehiculos')) ?>" data-horarios-url="<?= e(url('turnos/horarios')) ?>">
      <?= csrf_field() ?>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="cliente_id" class="form-label">Cliente *</label>
          <select name="cliente_id" id="cliente_id" class="form-select js-buscable" data-placeholder="Escribí el apellido o el DNI" required>
            <option value=""></option>
            <?php foreach ($clientes as $c): ?>
              <option value="<?= (int) $c['id'] ?>" <?= selected($clienteId === (int) $c['id']) ?>>
                <?= e("{$c['apellido']}, {$c['nombre']} · DNI {$c['dni']}") ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (!$turno): ?>
            <a href="<?= url('clientes/crear?para=turno') ?>" class="btn btn-sm btn-outline-secondary mt-2"><?= icono('person-plus') ?> Es un cliente nuevo</a>
            <small class="form-text text-muted d-block">Cargás el cliente y su auto, y volvés acá con todo elegido.</small>
          <?php endif; ?>
        </div>

        <div class="col-md-6 mb-3">
          <label for="vehiculo_id" class="form-label">Vehículo *</label>
          <select name="vehiculo_id" id="vehiculo_id" class="form-select js-buscable" data-placeholder="Primero elegí el cliente"
            required <?= $vehiculos === [] ? 'disabled' : '' ?>>
            <option value=""></option>
            <?php foreach ($vehiculos as $v): ?>
              <option value="<?= (int) $v['id'] ?>" <?= selected($vehiculoId === (int) $v['id']) ?>>
                <?= e("{$v['marca']} {$v['modelo']} ({$v['patente']})") ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <?= $view->partial('componentes/campo', [
          'nombre' => 'fecha', 'etiqueta' => 'Día *', 'tipo' => 'date', 'valor' => $turno['fecha'] ?? $sugerido['fecha'] ?? date('Y-m-d'), 'columna' => 'col-md-4 mb-3',
          'atributos' => ['required' => true, 'min' => $turno ? null : date('Y-m-d')],
        ]) ?>

        <?= $view->partial('componentes/campo', [
          'nombre' => 'hora', 'etiqueta' => 'Hora *', 'tipo' => 'time', 'valor' => substr((string) ($turno['hora'] ?? $sugerido['hora'] ?? ''), 0, 5), 'columna' => 'col-md-4 mb-3',
          'atributos' => ['required' => true, 'data-error' => 'Elegí un horario de la lista o escribí la hora.'],
        ]) ?>

        <?php // Al agendar, el turno siempre nace pendiente: el estado solo se toca al editar. ?>
        <?php if ($turno): ?>
          <?= $view->partial('componentes/campo', [
            'nombre' => 'estado', 'etiqueta' => 'Estado', 'tipo' => 'select', 'valor' => $turno['estado'], 'columna' => 'col-md-4 mb-3',
            'opciones' => array_combine(
              array_map(fn($opcion) => $opcion->value, $estados),
              array_map(fn($opcion) => $opcion->label(), $estados),
            ),
          ]) ?>
        <?php endif; ?>

        <div class="col-12 mb-3 horarios-libres" id="horarios_libres" aria-live="polite" hidden>
          <div class="small text-muted mb-1 js-horarios-titulo">Horarios de ese día (tocá uno):</div>
          <div class="d-flex flex-wrap gap-2 js-horarios"></div>
        </div>

        <?= $view->partial('componentes/campo', [
          'nombre' => 'descripcion', 'etiqueta' => 'Descripción / motivo de la visita', 'tipo' => 'textarea', 'valor' => $turno['descripcion'] ?? '', 'columna' => 'col-12 mb-3',
          'atributos' => ['rows' => 3, 'placeholder' => 'Ej: ruido en frenos, service de los 10.000 km...'],
        ]) ?>
      </div>

      <p class="small text-muted">Horario de atención: <?= e($horario->resumen()) ?></p>
      <button type="submit" class="btn btn-seccion"><?= icono('check-lg') ?> <?= $turno ? 'Guardar cambios' : 'Guardar turno' ?></button>
      <a href="<?= url('turnos') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </form>
  </div>
</div>
