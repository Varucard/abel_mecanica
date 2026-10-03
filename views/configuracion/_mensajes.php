<?php
/** @var array<string, mixed> $valores */
use App\Services\ConfiguracionService;

$campos = [
  'Email de confirmación (se envía al agendar)' => [
    'email_confirmacion_asunto' => ['Asunto', 1],
    'email_confirmacion' => ['Texto (debe incluir {link_turno})', 8],
  ],
  'Email de recordatorio (día hábil anterior)' => [
    'email_recordatorio_asunto' => ['Asunto', 1],
    'email_recordatorio' => ['Texto', 8],
  ],
  'WhatsApp' => [
    'whatsapp_recordatorio' => ['Recordatorio (botón manual)', 3],
    'whatsapp_confirmacion' => ['Confirmación (para cuando se active WhatsApp Business)', 3],
  ],
];
?>
<p class="text-muted">
  Variables disponibles:
  <?php foreach (ConfiguracionService::VARIABLES_MENSAJES as $variable): ?>
    <code>{<?= e($variable) ?>}</code>
  <?php endforeach; ?>
</p>
<?php foreach ($campos as $titulo => $grupo): ?>
  <h5 class="mt-3"><?= e($titulo) ?></h5>
  <?php foreach ($grupo as $campo => [$etiqueta, $filas]): ?>
    <div class="mb-3">
      <label for="<?= $campo ?>" class="form-label"><?= e($etiqueta) ?></label>
      <?php if ($filas === 1): ?>
        <input type="text" class="form-control" id="<?= $campo ?>" name="<?= $campo ?>" required value="<?= e(old($campo, $valores[$campo])) ?>">
      <?php else: ?>
        <textarea class="form-control" id="<?= $campo ?>" name="<?= $campo ?>" rows="<?= $filas ?>" required><?= e(old($campo, $valores[$campo])) ?></textarea>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
<?php endforeach; ?>
