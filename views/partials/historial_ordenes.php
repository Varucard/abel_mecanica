<?php
/** @var list<array<string, mixed>> $ordenes */
use App\Enums\EstadoOrden;

$colores = ['pendiente' => 'warning', 'en_proceso' => 'info', 'finalizado' => 'success', 'cancelado' => 'secondary'];
?>
<?php if ($ordenes === []): ?>
  <p class="text-muted mb-0">Sin órdenes registradas.</p>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead>
        <tr><th>N°</th><th>Fecha</th><th>Vehículo</th><th>Trabajos</th><th>Estado</th><th class="text-end">Total</th><th class="text-end">Saldo</th></tr>
      </thead>
      <tbody>
        <?php foreach ($ordenes as $o): ?>
          <?php $estado = EstadoOrden::from($o['estado']); ?>
          <tr>
            <td><a href="<?= url("ordenes/{$o['id']}") ?>">#<?= (int) $o['id'] ?></a></td>
            <td><?= format_date($o['created_at']) ?></td>
            <td><?= e($o['vehiculo']) ?></td>
            <td class="small"><?= e(trim(($o['servicios'] ?? '') . ($o['repuestos'] ? ' · ' . $o['repuestos'] : ''), ' ·')) ?></td>
            <td><span class="badge bg-<?= $colores[$estado->value] ?>"><?= e($estado->label()) ?></span></td>
            <td class="text-end">$ <?= money($o['total']) ?></td>
            <td class="text-end <?= (float) $o['saldo'] > 0 && $estado !== EstadoOrden::Cancelado ? 'text-danger' : '' ?>">
              <?= $estado === EstadoOrden::Cancelado ? '—' : '$ ' . money($o['saldo']) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
