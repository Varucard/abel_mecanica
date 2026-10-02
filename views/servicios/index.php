<?php
/**
 * @var array<string, mixed>|null $servicio  servicio en edición
 * @var list<array<string, mixed>> $servicios
 */
?>
<div class="card mb-4 mt-3">
  <div class="card-header bg-light">
    <h4 class="mb-0"><?= $servicio ? 'Editar servicio' : 'Nuevo servicio' ?></h4>
  </div>
  <div class="card-body">
    <form action="<?= url($servicio ? "servicios/{$servicio['id']}" : 'servicios') ?>" method="POST">
      <?= csrf_field() ?>
      <div class="row mb-3">
        <div class="col-md-6">
          <label for="nombre" class="form-label">Nombre *</label>
          <input type="text" class="form-control" id="nombre" name="nombre" minlength="2" required
            value="<?= e(old('nombre', $servicio['nombre'] ?? '')) ?>">
        </div>
        <div class="col-md-6">
          <label for="precio_base" class="form-label">Precio base *</label>
          <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" class="form-control" id="precio_base" name="precio_base" step="0.01" min="0" required
              value="<?= e(old('precio_base', $servicio['precio_base'] ?? '')) ?>">
          </div>
        </div>
      </div>
      <div class="mb-3">
        <label for="descripcion" class="form-label">Descripción</label>
        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= e(old('descripcion', $servicio['descripcion'] ?? '')) ?></textarea>
      </div>
      <button type="submit" class="btn btn-success"><?= $servicio ? 'Actualizar servicio' : 'Registrar servicio' ?></button>
      <?php if ($servicio): ?>
        <a href="<?= url('servicios') ?>" class="btn btn-secondary">Cancelar</a>
      <?php endif; ?>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header bg-light">
    <h4 class="mb-0">Servicios registrados</h4>
  </div>
  <div class="card-body">
    <table class="table table-striped table-bordered js-datatable" data-order='[[0, "asc"]]'>
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Precio base</th>
          <th data-orderable="false">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($servicios as $row): ?>
          <tr>
            <td><?= e($row['nombre']) ?></td>
            <td><?= e($row['descripcion'] ?? '') ?></td>
            <td data-order="<?= (float) $row['precio_base'] ?>">$ <?= money($row['precio_base']) ?></td>
            <td class="col-acciones text-nowrap">
              <a href="<?= url("servicios/{$row['id']}/editar") ?>" class="btn btn-sm btn-primary">Editar</a>
              <?= $view->partial('partials/delete_button', [
                'action' => "servicios/{$row['id']}/eliminar",
                'label' => 'Eliminar',
                'confirm' => "¿Eliminar el servicio \"{$row['nombre']}\"?",
              ]) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
