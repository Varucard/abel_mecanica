<?php
/**
 * @var array<string, mixed> $opciones
 * @var array<string, mixed>|null $resultado
 * @var string|null $volver link de regreso (si se entró desde un presupuesto)
 */
use App\Enums\EstadoOrden;
use App\Enums\EstadoTurno;

$pasos = ['pendiente' => 'Recibido', 'en_proceso' => 'En reparación', 'finalizado' => 'Listo'];
$orden = array_keys($pasos);
?>
<?php if ($resultado === null): ?>
  <div class="card">
    <div class="card-body p-4">
      <p>Consultá el estado de los trabajos de tu vehículo y tus próximos turnos.</p>
      <form action="<?= url('seguimiento') ?>" method="POST" class="row g-3" autocomplete="off">
        <?= csrf_field() ?>
        <div class="col-sm-<?= $opciones['requiere_patente'] ? '6' : '12' ?>">
          <label for="dni" class="form-label">DNI</label>
          <input type="text" class="form-control form-control-lg" id="dni" name="dni" inputmode="numeric" maxlength="10" required
            placeholder="Sin puntos" value="<?= e(old('dni')) ?>">
        </div>
        <?php if ($opciones['requiere_patente']): ?>
          <div class="col-sm-6">
            <label for="patente" class="form-label">Patente</label>
            <input type="text" class="form-control form-control-lg text-uppercase" id="patente" name="patente" maxlength="9" required
              placeholder="AB123CD" value="<?= e(old('patente')) ?>">
          </div>
        <?php endif; ?>
        <div class="col-12">
          <button type="submit" class="btn btn-primary btn-lg w-100">Consultar</button>
        </div>
      </form>
    </div>
  </div>
<?php else: ?>
  <h2 class="h4 mb-3">Hola <?= e($resultado['nombre']) ?></h2>

  <?php if ($resultado['turnos'] !== []): ?>
    <div class="card mb-3">
      <div class="card-header"><strong>Próximos turnos</strong></div>
      <ul class="list-group list-group-flush">
        <?php foreach ($resultado['turnos'] as $t): ?>
          <li class="list-group-item d-flex justify-content-between flex-wrap gap-2">
            <span><strong><?= format_date($t['fecha']) ?> · <?= e(substr($t['hora'], 0, 5)) ?> hs</strong> — <?= e($t['vehiculo']) ?></span>
            <span class="badge bg-<?= $t['estado'] === 'confirmado' ? 'success' : 'warning' ?> align-self-center"><?= e(EstadoTurno::from($t['estado'])->label()) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="card mb-3">
    <div class="card-header"><strong>Tus trabajos</strong></div>
    <div class="card-body">
      <?php if ($resultado['ordenes'] === []): ?>
        <?= $view->partial('componentes/vacio', ['icono' => 'tools', 'texto' => 'Todavía no hay trabajos registrados.']) ?>
      <?php endif; ?>
      <?php foreach ($resultado['ordenes'] as $o): ?>
        <?php $estado = EstadoOrden::from($o['estado']); ?>
        <div class="border rounded p-3 mb-3">
          <div class="d-flex justify-content-between flex-wrap gap-2">
            <strong>Orden #<?= (int) $o['id'] ?> · <?= e($o['vehiculo']) ?></strong>
            <span class="text-muted small"><?= format_date($o['created_at']) ?></span>
          </div>
          <div class="small my-2">
            <?= e(trim(($o['servicios'] ?? '') . ($o['repuestos'] ? ' · Repuestos: ' . $o['repuestos'] : ''), ' ·')) ?>
          </div>

          <?php if ($estado === EstadoOrden::Cancelado): ?>
            <span class="badge bg-secondary">Cancelada</span>
          <?php else: ?>
            <div class="seguimiento-pasos" aria-label="Estado: <?= e($estado->label()) ?>">
              <?php foreach ($pasos as $clave => $etiqueta): ?>
                <span class="<?= array_search($clave, $orden, true) <= array_search($estado->value, $orden, true) ? 'hecho' : '' ?>"><?= e($etiqueta) ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if ($resultado['mostrarMontos'] && $estado !== EstadoOrden::Cancelado): ?>
            <div class="small mt-2">
              Total: <?= importe($o['total']) ?>
              <?php if ((float) $o['saldo'] > 0): ?> · <span class="text-danger">Saldo pendiente: <?= importe($o['saldo']) ?></span>
              <?php else: ?> · <span class="text-success">Pagado</span><?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <?php if (isset($volver)): ?>
    <a href="<?= e($volver) ?>" class="btn btn-outline-secondary">Volver al presupuesto</a>
  <?php else: ?>
    <a href="<?= url('seguimiento') ?>" class="btn btn-outline-secondary">Nueva consulta</a>
  <?php endif; ?>
<?php endif; ?>
