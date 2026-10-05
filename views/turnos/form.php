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
$view->script('turnos.js');
?>
<div class="card mt-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h4 class="mb-0"><?= $turno ? "Editar turno #{$turno['id']}" : 'Agendar nuevo turno' ?></h4>
    <a href="<?= url('turnos') ?>" class="btn btn-outline-secondary">Volver a la agenda</a>
  </div>
  <div class="card-body">
    <form action="<?= url($turno ? "turnos/{$turno['id']}" : 'turnos') ?>" method="POST" id="form_turno"
      data-vehiculos-url="<?= e(url('clientes/{id}/vehiculos')) ?>">
      <?= csrf_field() ?>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="cliente_id" class="form-label">Cliente *</label>
          <select name="cliente_id" id="cliente_id" class="form-select js-buscable" data-placeholder="Seleccione un cliente" required>
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
          <select name="vehiculo_id" id="vehiculo_id" class="form-select js-buscable" data-placeholder="Seleccione primero un cliente"
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
          'nombre' => 'fecha', 'etiqueta' => 'Fecha *', 'tipo' => 'date', 'valor' => $turno['fecha'] ?? $sugerido['fecha'] ?? date('Y-m-d'), 'columna' => 'col-md-4 mb-3',
          'atributos' => ['required' => true, 'min' => $turno ? null : date('Y-m-d')],
        ]) ?>

        <?= $view->partial('componentes/campo', [
          'nombre' => 'hora', 'etiqueta' => 'Hora *', 'tipo' => 'time', 'valor' => substr((string) ($turno['hora'] ?? $sugerido['hora'] ?? ''), 0, 5), 'columna' => 'col-md-4 mb-3',
          'atributos' => ['required' => true],
        ]) ?>

        <?= $view->partial('componentes/campo', [
          'nombre' => 'estado', 'etiqueta' => 'Estado', 'tipo' => 'select', 'valor' => $turno['estado'] ?? 'pendiente', 'columna' => 'col-md-4 mb-3',
          'opciones' => array_combine(
            array_map(fn($opcion) => $opcion->value, $estados),
            array_map(fn($opcion) => $opcion->label(), $estados),
          ),
        ]) ?>

        <?= $view->partial('componentes/campo', [
          'nombre' => 'descripcion', 'etiqueta' => 'Descripción / motivo de la visita', 'tipo' => 'textarea', 'valor' => $turno['descripcion'] ?? '', 'columna' => 'col-12 mb-3',
          'atributos' => ['rows' => 3, 'placeholder' => 'Ej: ruido en frenos, service de los 10.000 km...'],
        ]) ?>
      </div>

      <p class="small text-muted">Horario de atención: <?= e($horario->resumen()) ?></p>
      <button type="submit" class="btn btn-seccion"><?= icono('check-lg') ?> <?= $turno ? 'Guardar cambios' : 'Confirmar turno' ?></button>
      <a href="<?= url('turnos') ?>" class="btn btn-outline-secondary ms-2">Cancelar</a>
    </form>
  </div>
</div>
