<?php
/**
 * @var array<string, mixed>|null $modelo  modelo en edición
 * @var list<array<string, mixed>> $modelos
 * @var list<array<string, mixed>> $marcas
 */
$marcaId = (int) old('marca_id', $modelo['marca_id'] ?? 0);
?>
<div class="card mb-4 mt-3">
  <div class="card-header bg-light">
    <h4 class="mb-0"><?= $modelo ? 'Editar modelo' : 'Nuevo modelo' ?></h4>
  </div>
  <div class="card-body">
    <form action="<?= url($modelo ? "modelos/{$modelo['id']}" : 'modelos') ?>" method="POST">
      <?= csrf_field() ?>
      <div class="row mb-3">
        <div class="col-md-6">
          <label for="marca_id" class="form-label">Marca *</label>
          <select class="form-select js-select2" id="marca_id" name="marca_id" data-placeholder="Seleccione una marca" required>
            <option value=""></option>
            <?php foreach ($marcas as $m): ?>
              <option value="<?= (int) $m['id'] ?>" <?= selected($marcaId === (int) $m['id']) ?>><?= e($m['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label for="nombre" class="form-label">Nombre del modelo *</label>
          <input type="text" class="form-control" id="nombre" name="nombre" maxlength="50" required
            value="<?= e(old('nombre', $modelo['nombre'] ?? '')) ?>">
        </div>
      </div>
      <button type="submit" class="btn btn-success"><?= $modelo ? 'Actualizar modelo' : 'Registrar modelo' ?></button>
      <?php if ($modelo): ?>
        <a href="<?= url('modelos') ?>" class="btn btn-secondary">Cancelar</a>
      <?php endif; ?>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header bg-light">
    <h4 class="mb-0">Modelos registrados</h4>
  </div>
  <div class="card-body">
    <table class="table table-striped table-bordered js-datatable" data-order='[[0, "asc"], [1, "asc"]]'>
      <thead>
        <tr>
          <th>Marca</th>
          <th>Modelo</th>
          <th data-orderable="false">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($modelos as $mo): ?>
          <tr>
            <td><?= e($mo['marca']) ?></td>
            <td><?= e($mo['nombre']) ?></td>
            <td class="col-acciones text-nowrap">
              <a href="<?= url("modelos/{$mo['id']}/editar") ?>" class="btn btn-sm btn-primary">Editar</a>
              <?= $view->partial('partials/delete_button', [
                'action' => "modelos/{$mo['id']}/eliminar",
                'label' => 'Eliminar',
                'confirm' => "¿Eliminar el modelo \"{$mo['marca']} {$mo['nombre']}\"?",
              ]) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
