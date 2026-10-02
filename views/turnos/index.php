<?php
/**
 * @var list<array<string, mixed>> $turnos
 * @var list<\App\Enums\EstadoTurno> $estados
 */
$view->script('turnos.js');
?>
<div class="card mt-3">
  <div class="card-header bg-light d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Turnos registrados</h4>
    <a href="<?= url('turnos/crear') ?>" class="btn btn-warning">+ Nuevo turno</a>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped table-bordered js-datatable" data-order='[[0, "desc"]]' data-page-length="25">
        <thead>
          <tr>
            <th>Fecha / Hora</th>
            <th class="col-cliente">Cliente</th>
            <th class="col-vehiculo">Vehículo</th>
            <th>Descripción</th>
            <th>Estado</th>
            <th data-orderable="false">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($turnos as $t): ?>
            <tr>
              <td data-order="<?= e($t['fecha'] . ' ' . $t['hora']) ?>">
                <strong><?= format_date($t['fecha']) ?></strong><br>
                <small class="text-muted"><?= e(substr($t['hora'], 0, 5)) ?> hs</small>
              </td>
              <td class="col-achicada"><?= e($t['cliente']) ?></td>
              <td class="col-achicada"><?= e($t['vehiculo']) ?></td>
              <td><small><?= e($t['descripcion'] ?? '') ?></small></td>
              <td data-order="<?= e($t['estado']) ?>">
                <select class="form-select form-select-sm estado-turno-select estado-<?= e($t['estado']) ?>"
                  data-url="<?= e(url("turnos/{$t['id']}/estado")) ?>" aria-label="Estado del turno <?= (int) $t['id'] ?>">
                  <?php foreach ($estados as $opcion): ?>
                    <option value="<?= e($opcion->value) ?>" <?= selected($opcion->value === $t['estado']) ?>><?= e($opcion->label()) ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td class="col-acciones text-nowrap">
                <?php if (in_array($t['estado'], ['pendiente', 'confirmado'], true) && $t['fecha'] >= date('Y-m-d')): ?>
                  <?= $view->partial('turnos/_recordatorio', ['turno' => $t, 'emailHabilitado' => $emailHabilitado, 'volver' => '']) ?>
                <?php endif; ?>
                <a href="<?= url("turnos/{$t['id']}/editar") ?>" class="btn btn-sm btn-primary">Editar</a>
                <?= $view->partial('partials/delete_button', [
                  'action' => "turnos/{$t['id']}/eliminar",
                  'label' => 'Eliminar',
                  'confirm' => '¿Eliminar el turno del ' . format_date($t['fecha']) . ' a las ' . substr($t['hora'], 0, 5) . '?',
                ]) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
