<?php
/**
 * @var array<string, mixed> $valores
 * @var \App\Support\HorarioAtencion $horario
 */
use App\Support\HorarioAtencion;

$conOld = session()->hasOldInput();
$check = fn(string $k) => ($conOld ? old($k) : $valores[$k]) ? 'checked' : '';
?>
<h5>Horario de atención</h5>
<p class="text-muted small">Actual: <?= e($horario->resumen() ?: 'sin días de atención') ?></p>
<div class="table-responsive">
  <table class="table table-sm align-middle" style="max-width: 560px;">
    <thead><tr><th>Día</th><th>Atiende</th><th>Desde</th><th>Hasta</th></tr></thead>
    <tbody>
      <?php foreach (HorarioAtencion::DIAS as $n => $dia): ?>
        <?php
        $franja = $valores['horario'][(string) $n] ?? null;
        $old = $conOld ? (old('horario')[$n] ?? []) : null;
        $abierto = $old !== null ? !empty($old['abierto']) : $franja !== null;
        ?>
        <tr>
          <td><?= e($dia) ?></td>
          <td><input class="form-check-input" type="checkbox" name="horario[<?= $n ?>][abierto]" value="1" <?= $abierto ? 'checked' : '' ?> aria-label="Atiende el <?= e($dia) ?>"></td>
          <td><input type="time" class="form-control form-control-sm" name="horario[<?= $n ?>][desde]" value="<?= e($old['desde'] ?? $franja['desde'] ?? '08:00') ?>" aria-label="Apertura <?= e($dia) ?>"></td>
          <td><input type="time" class="form-control form-control-sm" name="horario[<?= $n ?>][hasta]" value="<?= e($old['hasta'] ?? $franja['hasta'] ?? '18:00') ?>" aria-label="Cierre <?= e($dia) ?>"></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="row">
  <div class="col-md-6 mb-3">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" role="switch" id="validar_horario" name="validar_horario" value="1" <?= $check('validar_horario') ?>>
      <label class="form-check-label" for="validar_horario">No permitir turnos fuera del horario ni en feriados</label>
    </div>
  </div>
  <div class="col-md-6 mb-3">
    <label for="cupos_por_horario" class="form-label">Turnos simultáneos por horario</label>
    <input type="number" class="form-control" id="cupos_por_horario" name="cupos_por_horario" min="1" max="20" style="max-width: 120px;"
      value="<?= e(old('cupos_por_horario', $valores['cupos_por_horario'])) ?>">
    <small class="form-text text-muted">Por ejemplo, la cantidad de elevadores o mecánicos disponibles.</small>
  </div>
</div>

<div class="mb-3">
  <label for="feriados" class="form-label">Feriados y días no laborables (uno por línea, AAAA-MM-DD)</label>
  <textarea class="form-control font-monospace" id="feriados" name="feriados" rows="6"><?= e(old('feriados', implode("\n", $valores['feriados']))) ?></textarea>
</div>

<h5 class="mt-4">Confirmación y recordatorios</h5>
<div class="form-check form-switch mb-2">
  <input class="form-check-input" type="checkbox" role="switch" id="enviar_confirmacion" name="enviar_confirmacion" value="1" <?= $check('enviar_confirmacion') ?>>
  <label class="form-check-label" for="enviar_confirmacion">Al agendar o reprogramar un turno, pedirle al cliente que lo confirme</label>
</div>
<div class="form-check form-switch mb-2">
  <input class="form-check-input" type="checkbox" role="switch" id="recordatorio_automatico" name="recordatorio_automatico" value="1" <?= $check('recordatorio_automatico') ?>>
  <label class="form-check-label" for="recordatorio_automatico">Enviar recordatorio automático el día hábil anterior al turno</label>
</div>
<div class="mb-3">
  <label for="recordatorio_hora" class="form-label">A partir de las</label>
  <input type="time" class="form-control" id="recordatorio_hora" name="recordatorio_hora" style="max-width: 140px;"
    value="<?= e(old('recordatorio_hora', $valores['recordatorio_hora'])) ?>">
  <small class="form-text text-muted">Los recordatorios solo salen en días hábiles y dentro del horario de atención.</small>
</div>
