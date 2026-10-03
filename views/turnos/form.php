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
$estadoActual = old('estado', $turno['estado'] ?? 'pendiente');
$view->script('turnos.js');
?>
<div class="card mt-3">
  <div class="card-header bg-light d-flex justify-content-between align-items-center">
    <h4 class="mb-0"><?= $turno ? "Editar turno #{$turno['id']}" : 'Agendar nuevo turno' ?></h4>
    <a href="<?= url('turnos') ?>" class="btn btn-secondary">Volver a la agenda</a>
  </div>
  <div class="card-body">
    <form action="<?= url($turno ? "turnos/{$turno['id']}" : 'turnos') ?>" method="POST" id="form_turno"
      data-vehiculos-url="<?= e(url('clientes/{id}/vehiculos')) ?>">
      <?= csrf_field() ?>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="cliente_id" class="form-label">Cliente *</label>
          <select name="cliente_id" id="cliente_id" class="form-select js-select2" data-placeholder="Seleccione un cliente" required>
            <option value=""></option>
            <?php foreach ($clientes as $c): ?>
              <option value="<?= (int) $c['id'] ?>" <?= selected($clienteId === (int) $c['id']) ?>>
                <?= e("{$c['apellido']}, {$c['nombre']}") ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-6 mb-3">
          <label for="vehiculo_id" class="form-label">Vehículo *</label>
          <select name="vehiculo_id" id="vehiculo_id" class="form-select js-select2" data-placeholder="Seleccione primero un cliente"
            required <?= $vehiculos === [] ? 'disabled' : '' ?>>
            <option value=""></option>
            <?php foreach ($vehiculos as $v): ?>
              <option value="<?= (int) $v['id'] ?>" <?= selected($vehiculoId === (int) $v['id']) ?>>
                <?= e("{$v['marca']} {$v['modelo']} ({$v['patente']})") ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-4 mb-3">
          <label for="fecha" class="form-label">Fecha *</label>
          <input type="date" id="fecha" name="fecha" class="form-control" required
            <?= $turno ? '' : 'min="' . date('Y-m-d') . '"' ?>
            value="<?= e(old('fecha', $turno['fecha'] ?? $sugerido['fecha'] ?? date('Y-m-d'))) ?>">
        </div>

        <div class="col-md-4 mb-3">
          <label for="hora" class="form-label">Hora *</label>
          <input type="time" id="hora" name="hora" class="form-control" required
            value="<?= e(substr((string) old('hora', $turno['hora'] ?? $sugerido['hora'] ?? ''), 0, 5)) ?>">
        </div>

        <div class="col-md-4 mb-3">
          <label for="estado" class="form-label">Estado</label>
          <select id="estado" name="estado" class="form-select">
            <?php foreach ($estados as $opcion): ?>
              <option value="<?= e($opcion->value) ?>" <?= selected($opcion->value === $estadoActual) ?>><?= e($opcion->label()) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-12 mb-3">
          <label for="descripcion" class="form-label">Descripción / motivo de la visita</label>
          <textarea id="descripcion" name="descripcion" class="form-control" rows="3"
            placeholder="Ej: ruido en frenos, service de los 10.000 km..."><?= e(old('descripcion', $turno['descripcion'] ?? '')) ?></textarea>
        </div>
      </div>

      <p class="small text-muted">Horario de atención: <?= e($horario->resumen()) ?></p>
      <button type="submit" class="btn btn-warning"><?= $turno ? 'Guardar cambios' : 'Confirmar turno' ?></button>
      <a href="<?= url('turnos') ?>" class="btn btn-secondary ms-2">Cancelar</a>
    </form>
  </div>
</div>
