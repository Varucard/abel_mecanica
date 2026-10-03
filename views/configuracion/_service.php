<?php
/** @var array<string, mixed> $valores */
$conOld = session()->hasOldInput();
?>
<p class="text-muted">
  Al finalizar una orden se puede cargar el próximo service (km y fecha). Estos valores se usan para sugerirlo
  y para avisarle al cliente por email cuando se acerca la fecha.
</p>
<div class="row">
  <div class="col-md-4 mb-3">
    <label for="intervalo_km" class="form-label">Intervalo sugerido (km)</label>
    <input type="number" class="form-control" id="intervalo_km" name="intervalo_km" min="500" max="100000" step="500"
      value="<?= e(old('intervalo_km', $valores['intervalo_km'])) ?>">
  </div>
  <div class="col-md-4 mb-3">
    <label for="intervalo_meses" class="form-label">Intervalo sugerido (meses)</label>
    <input type="number" class="form-control" id="intervalo_meses" name="intervalo_meses" min="1" max="36"
      value="<?= e(old('intervalo_meses', $valores['intervalo_meses'])) ?>">
  </div>
</div>
<div class="form-check form-switch mb-2">
  <input class="form-check-input" type="checkbox" role="switch" id="aviso_automatico" name="aviso_automatico" value="1"
    <?= ($conOld ? old('aviso_automatico') : $valores['aviso_automatico']) ? 'checked' : '' ?>>
  <label class="form-check-label" for="aviso_automatico">Avisar automáticamente al cliente que se acerca su próximo service</label>
</div>
<div class="mb-3">
  <label for="aviso_dias_antes" class="form-label">Días de anticipación</label>
  <input type="number" class="form-control" id="aviso_dias_antes" name="aviso_dias_antes" min="0" max="60" style="max-width: 120px;"
    value="<?= e(old('aviso_dias_antes', $valores['aviso_dias_antes'])) ?>">
  <small class="form-text text-muted">El aviso sale en horario de atención, como los recordatorios de turnos.</small>
</div>
