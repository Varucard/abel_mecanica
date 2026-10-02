<?php /** @var list<array<string, mixed>> $vehiculos */ ?>
<div class="card mt-3">
  <div class="card-header bg-light d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Vehículos registrados</h4>
    <a href="<?= url('vehiculos/crear') ?>" class="btn btn-info">+ Registrar vehículo</a>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped table-bordered js-datatable" data-order='[[4, "asc"]]'>
        <thead>
          <tr>
            <th>Cliente</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Año</th>
            <th>Patente</th>
            <th>Kilometraje</th>
            <th>Estado</th>
            <th data-orderable="false">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($vehiculos as $v): ?>
            <?php $activo = $v['estado'] === 'activo'; ?>
            <tr>
              <td><?= e($v['cliente']) ?></td>
              <td><?= e($v['marca']) ?></td>
              <td><?= e($v['modelo']) ?></td>
              <td><?= e($v['anio']) ?></td>
              <td><?= e($v['patente']) ?></td>
              <td data-order="<?= (int) $v['kilometraje'] ?>">
                <?= $v['kilometraje'] !== null ? number_format((float) $v['kilometraje'], 0, ',', '.') . ' km' : '—' ?>
              </td>
              <td><span class="badge bg-<?= $activo ? 'success' : 'secondary' ?>"><?= e($v['estado']) ?></span></td>
              <td class="col-acciones text-nowrap">
                <a href="<?= url("vehiculos/{$v['id']}/editar") ?>" class="btn btn-sm btn-primary">Editar</a>
                <?= $view->partial('partials/delete_button', [
                  'action' => "vehiculos/{$v['id']}/estado",
                  'label' => $activo ? 'Desactivar' : 'Activar',
                  'class' => $activo ? 'btn-warning' : 'btn-success',
                  'confirm' => '¿' . ($activo ? 'Desactivar' : 'Activar') . " el vehículo {$v['patente']}?",
                ]) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
