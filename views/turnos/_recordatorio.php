<?php
/**
 * Botones de recordatorio de un turno.
 *
 * @var array<string, mixed> $turno
 * @var bool $emailHabilitado
 * @var string $volver  "inicio" o "" (agenda)
 */
?>
<form action="<?= url("turnos/{$turno['id']}/whatsapp") ?>" method="POST" target="_blank" class="d-inline">
  <?= csrf_field() ?>
  <button type="submit" class="btn btn-sm btn-success" title="Enviar recordatorio por WhatsApp">WhatsApp</button>
</form>
<?php if ($emailHabilitado && !empty($turno['cliente_email'])): ?>
  <form action="<?= url("turnos/{$turno['id']}/email") ?>" method="POST" class="d-inline" data-confirm="¿Enviar el recordatorio por email a <?= e($turno['cliente_email']) ?>?">
    <?= csrf_field() ?>
    <input type="hidden" name="volver" value="<?= e($volver ?? '') ?>">
    <button type="submit" class="btn btn-sm btn-outline-success">Email</button>
  </form>
<?php endif; ?>
<?php if ($turno['recordatorio_enviado']): ?>
  <div class="small text-muted">Avisado por <?= e($turno['recordatorio_canal']) ?> el <?= format_date($turno['recordatorio_enviado'], 'd/m H:i') ?></div>
<?php endif; ?>
