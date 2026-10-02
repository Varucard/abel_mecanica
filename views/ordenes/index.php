<?php
/**
 * @var list<array<string, mixed>> $ordenes
 * @var list<\App\Enums\EstadoOrden> $estados
 */
use App\Enums\EstadoOrden;
use App\Services\OrdenService;

$view->script('ordenes.js');
?>
<div class="card mt-3">
  <div class="card-header bg-light d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Órdenes registradas</h4>
    <a href="<?= url('ordenes/crear') ?>" class="btn btn-warning">+ Crear orden</a>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped table-bordered js-datatable" data-order='[[0, "desc"]]'>
        <thead>
          <tr>
            <th>N°</th>
            <th class="col-cliente">Cliente</th>
            <th class="col-vehiculo">Vehículo</th>
            <th>Servicio(s) - Repuesto(s)</th>
            <th>Total</th>
            <th>Saldo</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th data-orderable="false">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ordenes as $o): ?>
            <?php $estado = EstadoOrden::from($o['estado']); ?>
            <tr>
              <td><?= (int) $o['id'] ?></td>
              <td class="col-achicada"><?= e($o['cliente']) ?></td>
              <td class="col-achicada">
                <?= e($o['vehiculo']) ?>
                <?php if ($o['mecanico']): ?><div class="small text-muted">🔧 <?= e($o['mecanico']) ?></div><?php endif; ?>
              </td>
              <td>
                <?= e($o['servicios'] ?? '') ?>
                <?php if (!empty($o['repuestos'])): ?>
                  <div class="small text-muted mt-1"><strong>Repuestos:</strong> <?= e($o['repuestos']) ?></div>
                <?php endif; ?>
              </td>
              <td data-order="<?= (float) $o['total'] ?>">$ <?= money($o['total']) ?></td>
              <td data-order="<?= (float) $o['saldo'] ?>">
                <?php if ($estado->value === 'cancelado'): ?>—
                <?php elseif ((float) $o['saldo'] > 0): ?><span class="text-danger">$ <?= money($o['saldo']) ?></span>
                <?php else: ?><span class="badge bg-success">Pagada</span><?php endif; ?>
              </td>
              <td data-order="<?= e($o['created_at']) ?>"><?= format_date($o['created_at']) ?></td>
              <td data-order="<?= e($estado->value) ?>">
                <select class="form-select form-select-sm estado-orden-select estado-<?= e($estado->value) ?>"
                  data-url="<?= e(url("ordenes/{$o['id']}/estado")) ?>" aria-label="Estado de la orden <?= (int) $o['id'] ?>">
                  <?php foreach ($estados as $opcion): ?>
                    <option value="<?= e($opcion->value) ?>" <?= selected($opcion === $estado) ?>><?= e($opcion->label()) ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td class="col-acciones text-nowrap">
                <a href="<?= url("ordenes/{$o['id']}") ?>" class="btn btn-sm btn-info">Ver</a>
                <?php if (OrdenService::editable($estado)): ?>
                  <a href="<?= url("ordenes/{$o['id']}/editar") ?>" class="btn btn-sm btn-primary">Editar</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
