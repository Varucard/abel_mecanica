<?php /** @var array<string, mixed> $valores */ ?>
<div class="row">
  <?php foreach (['validez' => 'Validez del presupuesto', 'garantia' => 'Garantía', 'tiempo_estimado' => 'Tiempo estimado'] as $campo => $etiqueta): ?>
    <div class="col-md-4 mb-3">
      <label for="<?= $campo ?>" class="form-label"><?= $etiqueta ?> (días) *</label>
      <input type="number" class="form-control" id="<?= $campo ?>" name="<?= $campo ?>" min="1" max="365" required value="<?= e(old($campo, $valores[$campo])) ?>">
    </div>
  <?php endforeach; ?>
</div>
<div class="mb-3">
  <label for="forma_pago" class="form-label">Formas de pago (una por línea) *</label>
  <textarea class="form-control" id="forma_pago" name="forma_pago" rows="5" required><?= e(old('forma_pago', implode("\n", $valores['forma_pago']))) ?></textarea>
</div>
<div class="mb-3">
  <label for="observaciones" class="form-label">Observaciones del presupuesto (una por línea)</label>
  <textarea class="form-control" id="observaciones" name="observaciones" rows="4"><?= e(old('observaciones', implode("\n", $valores['observaciones']))) ?></textarea>
</div>
<div class="mb-3">
  <label for="mensaje_legal" class="form-label">Mensaje legal</label>
  <textarea class="form-control" id="mensaje_legal" name="mensaje_legal" rows="2"><?= e(old('mensaje_legal', $valores['mensaje_legal'])) ?></textarea>
</div>
