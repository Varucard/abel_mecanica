<?php
/**
 * @var array<string, mixed> $orden
 * @var array<string, mixed> $cliente
 * @var list<array<string, mixed>> $items
 * @var list<array<string, mixed>> $pagos
 * @var float $saldo
 * @var list<string> $formasPago
 * @var \App\Enums\EstadoOrden $estado
 */
use App\Services\OrdenService;

$colores = ['pendiente' => 'warning', 'en_proceso' => 'info', 'finalizado' => 'success', 'cancelado' => 'secondary'];
$pagado = (float) $orden['total'] - $saldo;
?>
<div class="d-flex flex-wrap gap-2 mt-3 mb-3">
  <?php if (OrdenService::editable($estado)): ?>
    <a href="<?= url("ordenes/{$orden['id']}/editar") ?>" class="btn btn-primary">Editar orden</a>
  <?php endif; ?>
  <a href="<?= url("ordenes/{$orden['id']}/presupuesto") ?>" class="btn btn-info">Presupuesto</a>
  <a href="<?= url("ordenes/{$orden['id']}/presupuesto/pdf") ?>" class="btn btn-outline-secondary">Presupuesto PDF</a>
  <?php if ($estado->value === 'finalizado'): ?>
    <a href="<?= url("ordenes/{$orden['id']}/entrega") ?>" class="btn btn-success">Comprobante de entrega</a>
    <a href="<?= url("ordenes/{$orden['id']}/entrega/pdf") ?>" class="btn btn-outline-success">Entrega PDF</a>
  <?php endif; ?>
  <a href="<?= url('ordenes') ?>" class="btn btn-secondary ms-auto">Volver al listado</a>
</div>

<div class="row g-3">
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-header bg-light"><strong>Orden #<?= (int) $orden['id'] ?></strong></div>
      <div class="card-body">
        <p class="mb-1">Estado: <span class="badge bg-<?= $colores[$estado->value] ?>"><?= e($estado->label()) ?></span></p>
        <p class="mb-1">Fecha: <?= format_date($orden['created_at']) ?></p>
        <p class="mb-1">Mecánico: <?= e($orden['mecanico'] ?? 'sin asignar') ?></p>
        <?php if ($orden['fecha_realizado']): ?><p class="mb-1">Finalizada: <?= format_date($orden['fecha_realizado']) ?></p><?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-header bg-light"><strong>Cliente</strong></div>
      <div class="card-body">
        <p class="mb-1"><a href="<?= url("clientes/{$cliente['id']}") ?>"><?= e("{$cliente['apellido']}, {$cliente['nombre']}") ?></a></p>
        <p class="mb-1">Tel: <?= e($cliente['telefono']) ?></p>
        <?php if ($cliente['email']): ?><p class="mb-1"><?= e($cliente['email']) ?></p><?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-header bg-light"><strong>Vehículo</strong></div>
      <div class="card-body">
        <p class="mb-1"><a href="<?= url("vehiculos/{$orden['vehiculo_id']}") ?>"><?= e("{$orden['marca']} {$orden['modelo']} ({$orden['anio']})") ?></a></p>
        <p class="mb-1">Patente: <?= e($orden['patente']) ?></p>
        <?php if ($orden['kilometraje'] !== null): ?><p class="mb-1"><?= number_format((float) $orden['kilometraje'], 0, ',', '.') ?> km</p><?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="card mt-3">
  <div class="card-header bg-light"><strong>Detalle</strong></div>
  <div class="card-body table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead>
        <tr><th>Ítem</th><th class="text-end">Cantidad</th><th class="text-end">Precio unit.</th><th class="text-end">Subtotal</th></tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
          <tr>
            <td><?= e($item['repuesto_id'] !== null ? 'Repuesto: ' . $item['repuesto_nombre'] : $item['servicio_nombre']) ?></td>
            <td class="text-end"><?= qty($item['cantidad']) ?></td>
            <td class="text-end">$ <?= money($item['precio_unitario']) ?></td>
            <td class="text-end">$ <?= money($item['costo']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr><th colspan="3" class="text-end">Total</th><th class="text-end">$ <?= money($orden['total']) ?></th></tr>
        <tr><td colspan="3" class="text-end">Pagado</td><td class="text-end">$ <?= money($pagado) ?></td></tr>
        <tr class="<?= $saldo > 0 ? 'table-warning' : 'table-success' ?>">
          <th colspan="3" class="text-end">Saldo</th><th class="text-end">$ <?= money($saldo) ?></th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<div class="card mt-3">
  <div class="card-header bg-light"><strong>Pagos</strong></div>
  <div class="card-body">
    <?php if ($pagos === []): ?>
      <p class="text-muted">Todavía no se registraron pagos.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr><th>Fecha</th><th>Forma de pago</th><th>Observación</th><th>Registró</th><th class="text-end">Monto</th><th></th></tr>
          </thead>
          <tbody>
            <?php foreach ($pagos as $p): ?>
              <tr>
                <td><?= format_date($p['fecha']) ?></td>
                <td><?= e($p['forma_pago']) ?></td>
                <td><?= e($p['observacion'] ?? '') ?></td>
                <td><?= e($p['usuario'] ?? '—') ?></td>
                <td class="text-end">$ <?= money($p['monto']) ?></td>
                <td class="text-end">
                  <?php if (auth()->esAdministrador()): ?>
                    <?= $view->partial('partials/delete_button', [
                      'action' => "pagos/{$p['id']}/anular",
                      'label' => 'Anular',
                      'class' => 'btn-outline-danger',
                      'confirm' => '¿Anular el pago de $ ' . money($p['monto']) . '?',
                    ]) ?>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <?php if ($saldo > 0 && $estado->value !== 'cancelado'): ?>
      <h6 class="mt-3">Registrar pago</h6>
      <form action="<?= url("ordenes/{$orden['id']}/pagos") ?>" method="POST" class="row g-2 align-items-end">
        <?= csrf_field() ?>
        <div class="col-sm-3">
          <label for="monto" class="form-label">Monto *</label>
          <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" class="form-control" id="monto" name="monto" step="0.01" min="0.01" max="<?= $saldo ?>" required
              value="<?= e(old('monto', number_format($saldo, 2, '.', ''))) ?>">
          </div>
        </div>
        <div class="col-sm-3">
          <label for="forma_pago" class="form-label">Forma de pago *</label>
          <select class="form-select" id="forma_pago" name="forma_pago" required>
            <?php foreach ($formasPago as $forma): ?>
              <option <?= selected($forma === old('forma_pago')) ?>><?= e($forma) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-sm-2">
          <label for="fecha" class="form-label">Fecha *</label>
          <input type="date" class="form-control" id="fecha" name="fecha" max="<?= date('Y-m-d') ?>" required value="<?= e(old('fecha', date('Y-m-d'))) ?>">
        </div>
        <div class="col-sm-4">
          <label for="observacion" class="form-label">Observación</label>
          <input type="text" class="form-control" id="observacion" name="observacion" maxlength="255" value="<?= e(old('observacion')) ?>">
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-success">Registrar pago</button>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>
