<?php /** @var array<string, mixed> $valores */ ?>
<div class="form-check form-switch mb-2">
  <input class="form-check-input" type="checkbox" role="switch" id="permitir_negativo" name="permitir_negativo" value="1"
    <?= (session()->hasOldInput() ? old('permitir_negativo') : $valores['permitir_negativo']) ? 'checked' : '' ?>>
  <label class="form-check-label" for="permitir_negativo">Permitir finalizar órdenes aunque el stock de un repuesto quede negativo</label>
</div>
<p class="text-muted small">
  Si se desactiva, una orden no se puede pasar a <em>Finalizada</em> hasta registrar el ingreso de los repuestos que faltan.
</p>
<div class="mb-3">
  <label for="margen_sugerido" class="form-label">Margen sugerido sobre el costo (%)</label>
  <input type="number" class="form-control" id="margen_sugerido" name="margen_sugerido" min="0" max="1000" step="0.5" style="max-width: 140px;"
    value="<?= e(old('margen_sugerido', $valores['margen_sugerido'])) ?>">
  <small class="form-text text-muted">Se usa para sugerir el precio de venta al cargar el costo de un repuesto.</small>
</div>
