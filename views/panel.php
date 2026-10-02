<?php
/**
 * @var list<array<string, mixed>> $turnosHoy
 * @var list<array<string, mixed>> $turnosManana
 * @var array{pendiente: int, en_proceso: int} $abiertas
 * @var list<array<string, mixed>> $ordenesAbiertas
 * @var float $cobradoMes
 * @var int $finalizadasMes
 * @var list<array<string, mixed>> $deudores
 * @var float $totalAdeudado
 * @var list<array<string, mixed>> $stockBajo
 * @var bool $emailHabilitado
 */
use App\Enums\EstadoTurno;

$meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$tarjetas = [
  ['Turnos de hoy', count($turnosHoy), 'turnos', 'warning'],
  ['Órdenes abiertas', $abiertas['pendiente'] + $abiertas['en_proceso'], 'ordenes', 'primary'],
  ['Cobrado en ' . $meses[(int) date('n')], '$ ' . money($cobradoMes), 'ordenes', 'success'],
  ['Saldo adeudado', '$ ' . money($totalAdeudado), 'deudores', 'danger'],
];
?>
<div class="row g-3 mt-1">
  <?php foreach ($tarjetas as [$etiqueta, $valor, $link, $color]): ?>
    <div class="col-6 col-lg-3">
      <a href="<?= url($link) ?>" class="card text-decoration-none border-<?= $color ?> h-100">
        <div class="card-body">
          <div class="small text-muted"><?= e($etiqueta) ?></div>
          <div class="fs-3 fw-semibold text-<?= $color ?>"><?= e((string) $valor) ?></div>
        </div>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<div class="row g-3 mt-1">
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <strong>Turnos de hoy</strong>
        <a href="<?= url('turnos/crear') ?>" class="btn btn-sm btn-warning">+ Agendar</a>
      </div>
      <div class="card-body">
        <?php if ($turnosHoy === []): ?>
          <p class="text-muted mb-0">No hay turnos para hoy.</p>
        <?php else: ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($turnosHoy as $t): ?>
              <li class="list-group-item px-0 d-flex justify-content-between align-items-start gap-2">
                <div>
                  <strong><?= e(substr($t['hora'], 0, 5)) ?></strong> · <a href="<?= url("clientes/{$t['cliente_id']}") ?>"><?= e($t['cliente']) ?></a>
                  <div class="small text-muted"><?= e($t['vehiculo']) ?><?= $t['descripcion'] ? ' · ' . e($t['descripcion']) : '' ?></div>
                </div>
                <span class="badge bg-secondary"><?= e(EstadoTurno::from($t['estado'])->label()) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <?php if ($turnosManana !== []): ?>
          <h6 class="mt-3">Mañana · enviar recordatorios</h6>
          <ul class="list-group list-group-flush">
            <?php foreach ($turnosManana as $t): ?>
              <li class="list-group-item px-0 d-flex justify-content-between align-items-start gap-2 flex-wrap">
                <div>
                  <strong><?= e(substr($t['hora'], 0, 5)) ?></strong> · <?= e($t['cliente']) ?>
                  <div class="small text-muted"><?= e($t['vehiculo']) ?></div>
                </div>
                <div class="text-end">
                  <?= $view->partial('turnos/_recordatorio', ['turno' => $t, 'emailHabilitado' => $emailHabilitado, 'volver' => 'inicio']) ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <strong>Órdenes abiertas</strong>
        <span class="small text-muted"><?= $abiertas['pendiente'] ?> pendientes · <?= $abiertas['en_proceso'] ?> en proceso</span>
      </div>
      <div class="card-body">
        <?php if ($ordenesAbiertas === []): ?>
          <p class="text-muted mb-0">No hay órdenes abiertas.</p>
        <?php else: ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($ordenesAbiertas as $o): ?>
              <li class="list-group-item px-0">
                <a href="<?= url("ordenes/{$o['id']}") ?>">#<?= (int) $o['id'] ?></a> · <?= e($o['vehiculo']) ?>
                <div class="small text-muted"><?= e($o['cliente']) ?><?= $o['mecanico'] ? ' · 🔧 ' . e($o['mecanico']) : '' ?></div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
        <p class="small text-muted mt-2 mb-0">Finalizadas este mes: <?= $finalizadasMes ?></p>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mt-1">
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <strong>Mayores saldos pendientes</strong>
        <a href="<?= url('deudores') ?>" class="small">Ver todos</a>
      </div>
      <div class="card-body">
        <?php if ($deudores === []): ?>
          <p class="text-muted mb-0">No hay clientes con deuda. 🎉</p>
        <?php else: ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($deudores as $d): ?>
              <li class="list-group-item px-0 d-flex justify-content-between">
                <a href="<?= url("clientes/{$d['id']}") ?>"><?= e($d['cliente']) ?></a>
                <span class="text-danger">$ <?= money($d['saldo']) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <strong>Stock bajo mínimo</strong>
        <a href="<?= url('repuestos') ?>" class="small">Ver repuestos</a>
      </div>
      <div class="card-body">
        <?php if ($stockBajo === []): ?>
          <p class="text-muted mb-0">Todo el stock está por encima del mínimo.</p>
        <?php else: ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($stockBajo as $r): ?>
              <li class="list-group-item px-0 d-flex justify-content-between">
                <a href="<?= url("repuestos/{$r['id']}/stock") ?>"><?= e($r['nombre']) ?></a>
                <span><span class="text-danger"><?= qty($r['stock_actual']) ?></span> <small class="text-muted">/ mín. <?= qty($r['stock_minimo']) ?><?= $r['proveedor'] ? ' · ' . e($r['proveedor']) : '' ?></small></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
