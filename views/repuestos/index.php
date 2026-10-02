<?php
/**
 * @var array<string, mixed>|null $repuesto  repuesto en edición
 * @var list<array<string, mixed>> $repuestos
 */
?>
<div class="card mb-4 mt-3">
  <div class="card-header bg-light">
    <h4 class="mb-0"><?= $repuesto ? 'Editar repuesto' : 'Nuevo repuesto' ?></h4>
  </div>
  <div class="card-body">
    <form action="<?= url($repuesto ? "repuestos/{$repuesto['id']}" : 'repuestos') ?>" method="POST">
      <?= csrf_field() ?>
      <div class="row mb-3">
        <div class="col-md-6">
          <label for="nombre" class="form-label">Nombre *</label>
          <input type="text" class="form-control" id="nombre" name="nombre" minlength="2" required
            value="<?= e(old('nombre', $repuesto['nombre'] ?? '')) ?>">
        </div>
        <div class="col-md-6">
          <label for="precio" class="form-label">Precio *</label>
          <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0" required
              value="<?= e(old('precio', $repuesto['precio'] ?? '')) ?>">
          </div>
        </div>
      </div>
      <div class="mb-3">
        <label for="descripcion" class="form-label">Descripción</label>
        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= e(old('descripcion', $repuesto['descripcion'] ?? '')) ?></textarea>
      </div>
      <button type="submit" class="btn btn-success"><?= $repuesto ? 'Actualizar repuesto' : 'Registrar repuesto' ?></button>
      <?php if ($repuesto): ?>
        <a href="<?= url('repuestos') ?>" class="btn btn-secondary">Cancelar</a>
      <?php endif; ?>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header bg-light">
    <h4 class="mb-0">Repuestos registrados</h4>
  </div>
  <div class="card-body">
    <table class="table table-striped table-bordered js-datatable" data-order='[[0, "asc"]]'>
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Precio</th>
          <th data-orderable="false">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($repuestos as $row): ?>
          <tr>
            <td><?= e($row['nombre']) ?></td>
            <td><?= e($row['descripcion'] ?? '') ?></td>
            <td data-order="<?= (float) $row['precio'] ?>">$ <?= money($row['precio']) ?></td>
            <td class="col-acciones text-nowrap">
              <a href="<?= url("repuestos/{$row['id']}/editar") ?>" class="btn btn-sm btn-primary">Editar</a>
              <?= $view->partial('partials/delete_button', [
                'action' => "repuestos/{$row['id']}/eliminar",
                'label' => 'Eliminar',
                'confirm' => "¿Eliminar el repuesto \"{$row['nombre']}\"?",
              ]) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
