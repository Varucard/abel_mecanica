<?php
/**
 * @var string $texto
 * @var array{clientes: list<array<string, mixed>>, vehiculos: list<array<string, mixed>>, ordenes: list<array<string, mixed>>} $resultados
 * @var int $total
 */
use App\Enums\EstadoOrden;
?>
<h4 class="mt-3">
  <?php if (mb_strlen($texto) < 2): ?>
    Escribí al menos 2 caracteres para buscar.
  <?php else: ?>
    <?= $total ?> resultado<?= $total === 1 ? '' : 's' ?> para “<?= e($texto) ?>”
  <?php endif; ?>
</h4>

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
