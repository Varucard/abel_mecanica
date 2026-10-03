<?php
/** @var array<string, mixed> $valores */
$conOld = session()->hasOldInput();
$check = fn(string $k) => ($conOld ? old($k) : $valores[$k]) ? 'checked' : '';
?>
<p>
  Página pública para que los clientes consulten el estado de sus trabajos y sus próximos turnos:
  <a href="<?= url('seguimiento') ?>" target="_blank" rel="noopener"><?= e(absolute_url('seguimiento')) ?></a>
</p>
<div class="form-check form-switch mb-2">
  <input class="form-check-input" type="checkbox" role="switch" id="habilitado" name="habilitado" value="1" <?= $check('habilitado') ?>>
  <label class="form-check-label" for="habilitado">Portal habilitado</label>
</div>
<div class="form-check form-switch mb-2">
  <input class="form-check-input" type="checkbox" role="switch" id="requiere_patente" name="requiere_patente" value="1" <?= $check('requiere_patente') ?>>
  <label class="form-check-label" for="requiere_patente">Pedir la patente además del DNI <strong>(recomendado)</strong></label>
  <div class="form-text">Con solo el DNI, cualquiera que conozca el DNI de otra persona podría ver sus trabajos.</div>
</div>
<div class="form-check form-switch mb-3">
  <input class="form-check-input" type="checkbox" role="switch" id="mostrar_montos" name="mostrar_montos" value="1" <?= $check('mostrar_montos') ?>>
  <label class="form-check-label" for="mostrar_montos">Mostrar totales y saldos</label>
</div>
<div class="mb-3">
  <label for="cantidad_ordenes" class="form-label">Cantidad de trabajos a mostrar</label>
  <input type="number" class="form-control" id="cantidad_ordenes" name="cantidad_ordenes" min="1" max="50" style="max-width: 120px;"
    value="<?= e(old('cantidad_ordenes', $valores['cantidad_ordenes'])) ?>">
</div>
