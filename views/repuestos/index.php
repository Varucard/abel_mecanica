<?php
/**
 * @var array<string, mixed>|null $repuesto  repuesto en edición
 * @var list<array<string, mixed>> $repuestos
 * @var list<array<string, mixed>> $proveedores
 * @var float $margen  margen sugerido (%)
 */
$proveedorId = (int) old('proveedor_id', $repuesto['proveedor_id'] ?? 0);
?>
<div class="card mb-4 mt-3">
  <div class="card-header bg-light">
    <h4 class="mb-0"><?= $repuesto ? 'Editar repuesto' : 'Nuevo repuesto' ?></h4>
  </div>
  <div class="card-body">
    <form action="<?= url($repuesto ? "repuestos/{$repuesto['id']}" : 'repuestos') ?>" method="POST">
      <?= csrf_field() ?>
      <div class="row">
        <div class="col-md-3 mb-3">
          <label for="codigo" class="form-label">Código</label>
          <input type="text" class="form-control text-uppercase" id="codigo" name="codigo" maxlength="50"
            value="<?= e(old('codigo', $repuesto['codigo'] ?? '')) ?>">
        </div>
        <div class="col-md-5 mb-3">
          <label for="nombre" class="form-label">Nombre *</label>
          <input type="text" class="form-control" id="nombre" name="nombre" minlength="2" maxlength="150" required
            value="<?= e(old('nombre', $repuesto['nombre'] ?? '')) ?>">
        </div>
        <div class="col-md-2 mb-3">
          <label for="precio_costo" class="form-label">Costo</label>
          <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" class="form-control" id="precio_costo" name="precio_costo" step="0.01" min="0"
              value="<?= e(old('precio_costo', $repuesto['precio_costo'] ?? '')) ?>">
          </div>
        </div>
        <div class="col-md-2 mb-3">
          <label for="precio" class="form-label">Precio de venta *</label>
          <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0" required
              value="<?= e(old('precio', $repuesto['precio'] ?? '')) ?>">
          </div>
          <button type="button" class="btn btn-link btn-sm p-0" id="sugerir_precio" data-margen="<?= e((string) $margen) ?>">
            Sugerir con <?= qty($margen) ?>% de margen
          </button>
          <div class="small text-muted" id="margen_actual"></div>
        </div>
        <div class="col-md-5 mb-3">
          <label for="proveedor_id" class="form-label">Proveedor habitual</label>
          <select class="form-select js-select2" id="proveedor_id" name="proveedor_id" data-placeholder="Sin proveedor">
            <option value=""></option>
            <?php foreach ($proveedores as $p): ?>
              <option value="<?= (int) $p['id'] ?>" <?= selected($proveedorId === (int) $p['id']) ?>><?= e($p['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3 mb-3">
          <label for="stock_minimo" class="form-label">Stock mínimo</label>
          <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" step="0.01" min="0"
            value="<?= e(old('stock_minimo', isset($repuesto['stock_minimo']) ? (float) $repuesto['stock_minimo'] : '0')) ?>">
          <small class="form-text text-muted">Avisa cuando el stock llega a este valor.</small>
        </div>
        <?php if ($repuesto): ?>
          <div class="col-md-4 mb-3">
            <label class="form-label">Stock actual</label>
            <div class="input-group">
              <input type="text" class="form-control" value="<?= qty($repuesto['stock_actual']) ?>" readonly>
              <a href="<?= url("repuestos/{$repuesto['id']}/stock") ?>" class="btn btn-outline-secondary">Movimientos</a>
            </div>
          </div>
        <?php endif; ?>
      </div>
      <div class="mb-3">
        <label for="descripcion" class="form-label">Descripción</label>
        <textarea class="form-control" id="descripcion" name="descripcion" rows="2"><?= e(old('descripcion', $repuesto['descripcion'] ?? '')) ?></textarea>
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
    <div class="table-responsive">
      <table class="table table-striped table-bordered js-datatable" data-order='[[1, "asc"]]'>
        <thead>
          <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Proveedor</th>
            <th>Costo</th>
            <th>Precio</th>
            <th>Margen</th>
            <th>Stock</th>
            <th data-orderable="false">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($repuestos as $row): ?>
            <?php $bajo = (float) $row['stock_minimo'] > 0 && (float) $row['stock_actual'] <= (float) $row['stock_minimo']; ?>
            <tr>
              <td><?= e($row['codigo'] ?? '') ?></td>
              <td>
                <?= e($row['nombre']) ?>
                <?php if ($row['descripcion']): ?><div class="small text-muted"><?= e($row['descripcion']) ?></div><?php endif; ?>
              </td>
              <td><?= e($row['proveedor'] ?? '—') ?></td>
              <?php $margenFila = (float) $row['precio_costo'] > 0 ? ((float) $row['precio'] / (float) $row['precio_costo'] - 1) * 100 : null; ?>
              <td data-order="<?= (float) $row['precio_costo'] ?>"><?= $row['precio_costo'] !== null ? '$ ' . money($row['precio_costo']) : '—' ?></td>
              <td data-order="<?= (float) $row['precio'] ?>">$ <?= money($row['precio']) ?></td>
              <td data-order="<?= $margenFila ?? -999 ?>" class="<?= $margenFila !== null && $margenFila < 0 ? 'text-danger' : '' ?>"><?= $margenFila !== null ? qty(round($margenFila, 1)) . ' %' : '—' ?></td>
              <td data-order="<?= (float) $row['stock_actual'] ?>">
                <span class="badge bg-<?= (float) $row['stock_actual'] < 0 || $bajo ? 'danger' : 'success' ?>"><?= qty($row['stock_actual']) ?></span>
                <?php if ($bajo): ?><small class="text-danger d-block">mín. <?= qty($row['stock_minimo']) ?></small><?php endif; ?>
              </td>
              <td class="col-acciones text-nowrap">
                <a href="<?= url("repuestos/{$row['id']}/editar") ?>" class="btn btn-sm btn-primary">Editar</a>
                <a href="<?= url("repuestos/{$row['id']}/stock") ?>" class="btn btn-sm btn-info">Stock</a>
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
</div>

<script>
  // Precio sugerido = costo + margen, y margen actual mientras se escribe.
  document.addEventListener('DOMContentLoaded', () => {
    const costo = document.getElementById('precio_costo');
    const precio = document.getElementById('precio');
    const margen = document.getElementById('margen_actual');
    const mostrar = () => {
      const c = parseFloat(costo.value), p = parseFloat(precio.value);
      margen.textContent = c > 0 && p > 0 ? `Margen actual: ${((p / c - 1) * 100).toFixed(1).replace('.', ',')} %` : '';
    };
    document.getElementById('sugerir_precio').addEventListener('click', (e) => {
      const c = parseFloat(costo.value);
      if (c > 0) {
        precio.value = (c * (1 + parseFloat(e.target.dataset.margen) / 100)).toFixed(2);
        mostrar();
      } else {
        costo.focus();
      }
    });
    costo.addEventListener('input', mostrar);
    precio.addEventListener('input', mostrar);
    mostrar();
  });
</script>
