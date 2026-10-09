<?php
/**
 * @var string $texto
 * @var array{clientes: list<array<string, mixed>>, vehiculos: list<array<string, mixed>>, ordenes: list<array<string, mixed>>} $resultados
 * @var int $total
 */
use App\Enums\EstadoOrden;
?>
<h4 class="mt-3">
  <?php if (mb_strlen($texto) < 2 && !ctype_digit($texto)): ?>
    Escribí al menos 2 caracteres para buscar.
  <?php else: ?>
    <?= $total ?> resultado<?= $total === 1 ? '' : 's' ?> para “<?= e($texto) ?>”
  <?php endif; ?>
</h4>

<?php // Sin resultados no es un callejón sin salida: se ofrece lo que probablemente se quería hacer. ?>
<?php if ($total === 0 && mb_strlen($texto) >= 2): ?>
  <?php $patente = \App\Services\VehiculoService::normalizarPatente($texto); ?>
  <div class="d-flex flex-wrap gap-2 mt-2">
    <?php if (\App\Services\VehiculoService::patenteValida($patente)): ?>
      <a href="<?= url('recepcion?patente=' . urlencode($patente)) ?>" class="btn btn-primary"><?= icono('car-front-fill') ?> Recibir el auto <?= e($patente) ?></a>
    <?php endif; ?>
    <a href="<?= url('clientes/crear') ?>" class="btn btn-outline-secondary"><?= icono('person-plus') ?> Nuevo cliente</a>
  </div>
  <p class="small text-muted mt-2">Se busca por patente, DNI, apellido, nombre o número de orden (por ejemplo, #12).</p>
<?php endif; ?>

<?php if ($resultados['ordenes'] !== []): ?>
  <div class="card mt-3">
    <div class="card-header"><strong>Órdenes</strong></div>
    <ul class="list-group list-group-flush">
      <?php foreach ($resultados['ordenes'] as $o): ?>
        <li class="list-group-item">
          <a href="<?= url("ordenes/{$o['id']}") ?>">Orden #<?= (int) $o['id'] ?></a> · <?= e($o['patente']) ?> · <?= e($o['cliente']) ?>
          · <?= e(EstadoOrden::from($o['estado'])->label()) ?> · <?= importe($o['total']) ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if ($resultados['vehiculos'] !== []): ?>
  <div class="card mt-3">
    <div class="card-header"><strong>Vehículos</strong></div>
    <ul class="list-group list-group-flush">
      <?php foreach ($resultados['vehiculos'] as $v): ?>
        <li class="list-group-item">
          <a href="<?= url("vehiculos/{$v['id']}") ?>"><?= e($v['patente']) ?></a> · <?= e("{$v['marca']} {$v['modelo']} ({$v['anio']})") ?> · <?= e($v['cliente']) ?>
          <?= $v['estado'] !== 'activo' ? '<span class="badge bg-secondary">inactivo</span>' : '' ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if ($resultados['clientes'] !== []): ?>
  <div class="card mt-3">
    <div class="card-header"><strong>Clientes</strong></div>
    <ul class="list-group list-group-flush">
      <?php foreach ($resultados['clientes'] as $c): ?>
        <li class="list-group-item">
          <a href="<?= url("clientes/{$c['id']}") ?>"><?= e("{$c['apellido']}, {$c['nombre']}") ?></a> · DNI <?= e($c['dni']) ?> · Tel. <?= e($c['telefono']) ?>
          <?= $c['estado'] !== 'activo' ? '<span class="badge bg-secondary">inactivo</span>' : '' ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>
