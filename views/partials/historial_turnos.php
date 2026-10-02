<?php
/** @var list<array<string, mixed>> $turnos */
use App\Enums\EstadoTurno;
?>
<?php if ($turnos === []): ?>
  <p class="text-muted mb-0">Sin turnos registrados.</p>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead>
        <tr><th>Fecha</th><th>Hora</th><th>Vehículo</th><th>Motivo</th><th>Estado</th></tr>
      </thead>
      <tbody>
        <?php foreach ($turnos as $t): ?>
          <tr>
            <td><a href="<?= url("turnos/{$t['id']}/editar") ?>"><?= format_date($t['fecha']) ?></a></td>
            <td><?= e(substr($t['hora'], 0, 5)) ?></td>
            <td><?= e($t['vehiculo']) ?></td>
            <td class="small"><?= e($t['descripcion'] ?? '') ?></td>
            <td><?= e(EstadoTurno::from($t['estado'])->label()) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
