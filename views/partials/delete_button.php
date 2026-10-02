<?php
/**
 * Botón que envía un POST protegido por CSRF, con confirmación.
 *
 * @var string $action   ruta destino
 * @var string $label    texto del botón
 * @var string $confirm  mensaje de confirmación
 * @var string $class    clases del botón (opcional)
 */
?>
<form action="<?= url($action) ?>" method="POST" class="d-inline" data-confirm="<?= e($confirm) ?>">
  <?= csrf_field() ?>
  <button type="submit" class="btn btn-sm <?= e($class ?? 'btn-danger') ?>"><?= e($label) ?></button>
</form>
