<?php
/**
 * Nivel de nafta al recibir el auto: una tira de botones (radios) de reserva a lleno.
 *
 * @var array<int, string> $niveles  Orden::COMBUSTIBLE
 * @var int|string|null $valor
 */
$elegido = (string) old('combustible_ingreso', $valor ?? '');
?>
<fieldset class="mb-3">
  <legend class="form-label fs-6 mb-2">Nafta</legend>
  <div class="btn-group flex-wrap nivel-nafta" role="group">
    <?php foreach ($niveles as $nivel => $texto): ?>
      <input type="radio" class="btn-check" name="combustible_ingreso" id="nafta_<?= (int) $nivel ?>" value="<?= (int) $nivel ?>" autocomplete="off" <?= $elegido === (string) $nivel ? 'checked' : '' ?>>
      <label class="btn btn-outline-secondary" for="nafta_<?= (int) $nivel ?>"><?= e($texto) ?></label>
    <?php endforeach; ?>
  </div>
  <?= $view->partial('componentes/error_campo', ['nombre' => 'combustible_ingreso']) ?>
</fieldset>
