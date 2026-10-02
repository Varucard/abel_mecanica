<?php
/**
 * @var array<string, mixed> $repuesto
 * @var list<array<string, mixed>> $movimientos
 * @var list<array<string, mixed>> $proveedores
 */
$tipos = ['ingreso' => ['Ingreso', 'success'], 'egreso' => ['Egreso', 'warning'], 'ajuste' => ['Ajuste', 'secondary']];
?>
<div class="card mt-3">
  <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4 class="mb-0"><?= e($repuesto['nombre']) ?> <?= $repuesto['codigo'] ? '<small class="text-muted">(' . e($repuesto['codigo']) . ')</small>' : '' ?></h4>
    <span class="fs-5">Stock actual: <strong><?= qty($repuesto['stock_actual']) ?></strong>
      <?php if ((float) $repuesto['stock_minimo'] > 0): ?><small class="text-muted">· mínimo <?= qty($repuesto['stock_minimo']) ?></small><?php endif; ?>
    </span>
  </div>
  <div class="card-body">
    <div class="row g-4">
      <div class="col-lg-6">
        <h5>Registrar ingreso</h5>
        <form action="<?= url("repuestos/{$repuesto['id']}/ingresos") ?>" method="POST">
          <?= csrf_field() ?>
          <div class="row">
            <div class="col-sm-4 mb-2">
              <label for="cantidad" class="form-label">Cantidad *</label>
              <input type="number" class="form-control" id="cantidad" name="cantidad" step="0.01" min="0.01" required>
            </div>
            <div class="col-sm-8 mb-2">
              <label for="proveedor_id" class="form-label">Proveedor</label>
              <select class="form-select" id="proveedor_id" name="proveedor_id">
                <option value="">—</option>
                <?php foreach ($proveedores as $p): ?>
                  <option value="<?= (int) $p['id'] ?>" <?= selected((int) $p['id'] === (int) $repuesto['proveedor_id']) ?>><?= e($p['nombre']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="mb-2">
            <label for="motivo_ingreso" class="form-label">Comprobante / observación</label>
            <input type="text" class="form-control" id="motivo_ingreso" name="motivo" maxlength="255" placeholder="Ej: Factura A 0001-00001234">
          </div>
          <button type="submit" class="btn btn-success">Registrar ingreso</button>
        </form>
      </div>
      <div class="col-lg-6">
        <h5>Ajustar por conteo</h5>
        <form action="<?= url("repuestos/{$repuesto['id']}/ajustes") ?>" method="POST">
          <?= csrf_field() ?>
          <div class="row">
            <div class="col-sm-4 mb-2">
              <label for="stock_real" class="form-label">Stock real *</label>
              <input type="number" class="form-control" id="stock_real" name="stock_real" step="0.01" min="0" required>
            </div>
            <div class="col-sm-8 mb-2">
              <label for="motivo_ajuste" class="form-label">Motivo *</label>
              <input type="text" class="form-control" id="motivo_ajuste" name="motivo" maxlength="255" required placeholder="Ej: Inventario mensual">
            </div>
          </div>
          <button type="submit" class="btn btn-secondary">Ajustar stock</button>
        </form>
      </div>
    </div>

    <h5 class="mt-4">Movimientos</h5>
    <div class="table-responsive">
      <table class="table table-striped table-bordered js-datatable" data-order='[[0, "desc"]]'>
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Cantidad</th>
            <th>Stock resultante</th>
            <th>Detalle</th>
            <th>Usuario</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($movimientos as $m): ?>
            <?php [$etiqueta, $color] = $tipos[$m['tipo']]; ?>
            <tr>
              <td data-order="<?= (int) $m['id'] ?>"><?= format_date($m['created_at'], 'd/m/Y H:i') ?></td>
              <td><span class="badge bg-<?= $color ?>"><?= $etiqueta ?></span></td>
              <td class="<?= (float) $m['cantidad'] < 0 ? 'text-danger' : 'text-success' ?>"><?= ((float) $m['cantidad'] > 0 ? '+' : '') . qty($m['cantidad']) ?></td>
              <td><?= qty($m['stock_resultante']) ?></td>
              <td>
                <?= e($m['motivo'] ?? '') ?>
                <?php if ($m['proveedor']): ?><div class="small text-muted">Proveedor: <?= e($m['proveedor']) ?></div><?php endif; ?>
                <?php if ($m['orden_id']): ?><a class="small" href="<?= url("ordenes/{$m['orden_id']}/presupuesto") ?>">Ver orden #<?= (int) $m['orden_id'] ?></a><?php endif; ?>
              </td>
              <td><?= e($m['usuario'] ?? '—') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <a href="<?= url('repuestos') ?>" class="btn btn-secondary mt-2">Volver a repuestos</a>
  </div>
</div>
