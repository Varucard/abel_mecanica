<?php /** @var list<array<string, mixed>> $clientes */ ?>
<div class="card mt-3">
  <div class="card-header bg-light d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Clientes registrados</h4>
    <a href="<?= url('clientes/crear') ?>" class="btn btn-success">+ Nuevo cliente</a>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped table-bordered js-datatable" data-order='[[1, "asc"]]'>
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>DNI</th>
            <th>Teléfono</th>
            <th>Email</th>
            <th>Dirección</th>
            <th>Estado</th>
            <th data-orderable="false">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($clientes as $c): ?>
            <?php $activo = $c['estado'] === 'activo'; ?>
            <tr>
              <td><?= e($c['nombre']) ?></td>
              <td><?= e($c['apellido']) ?></td>
              <td><?= e($c['dni']) ?></td>
              <td><?= e($c['telefono']) ?></td>
              <td><?= e($c['email'] ?? '') ?></td>
              <td><?= e($c['direccion'] ?? '') ?></td>
              <td><span class="badge bg-<?= $activo ? 'success' : 'secondary' ?>"><?= e($c['estado']) ?></span></td>
              <td class="col-acciones text-nowrap">
                <a href="<?= url("clientes/{$c['id']}/editar") ?>" class="btn btn-sm btn-primary">Editar</a>
                <?= $view->partial('partials/delete_button', [
                  'action' => "clientes/{$c['id']}/estado",
                  'label' => $activo ? 'Desactivar' : 'Activar',
                  'class' => $activo ? 'btn-warning' : 'btn-success',
                  'confirm' => '¿' . ($activo ? 'Desactivar' : 'Activar') . " al cliente {$c['nombre']} {$c['apellido']}?",
                ]) ?>
                <?= $view->partial('partials/delete_button', [
                  'action' => "clientes/{$c['id']}/eliminar",
                  'label' => 'Eliminar',
                  'confirm' => "¿Eliminar al cliente {$c['nombre']} {$c['apellido']}? Esta acción no se puede deshacer.",
                ]) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
