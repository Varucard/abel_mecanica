<?php
/**
 * Firma de la conformidad al recibir, cuando no se hizo en "Llegó un auto".
 *
 * @var array<string, mixed> $orden
 * @var array<int, string> $combustibles
 */
$id = (int) $orden['id'];
?>
<form action="<?= url("ordenes/{$id}/firmar") ?>" method="POST" class="mt-3">
  <?= csrf_field() ?>
  <div class="card mb-3">
    <div class="card-body">
      <h2 class="h5"><?= e("{$orden['marca']} {$orden['modelo']}") ?> · <?= e($orden['patente']) ?></h2>
      <ul class="list-unstyled mb-0">
        <li>Recibido el <?= format_date($orden['created_at'], 'd/m/Y \a \l\a\s H:i') ?></li>
        <?php if ($orden['km_ingreso'] !== null): ?><li>Kilómetros: <?= number_format((float) $orden['km_ingreso'], 0, ',', '.') ?></li><?php endif; ?>
        <?php if ($orden['combustible_ingreso'] !== null): ?><li>Nafta: <?= e($combustibles[(int) $orden['combustible_ingreso']]) ?></li><?php endif; ?>
        <?php if ($orden['estado_ingreso']): ?><li>Golpes, rayones o faltantes: <?= nl2br(e($orden['estado_ingreso'])) ?></li><?php endif; ?>
      </ul>
    </div>
  </div>

  <?= $view->partial('componentes/firma', ['texto' => 'El cliente firma que deja el auto en el estado descripto.', 'requerida' => true]) ?>

  <div class="d-flex flex-wrap gap-2 mt-3">
    <button type="submit" class="btn btn-seccion btn-lg"><?= icono('check-lg') ?> Guardar la firma</button>
    <a href="<?= url("ordenes/{$id}") ?>" class="btn btn-outline-secondary btn-lg">Cancelar</a>
  </div>
</form>
