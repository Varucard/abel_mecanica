<?php
/**
 * @var string $seccion
 * @var array<string, string> $pestanas
 * @var array<string, array<string, mixed>> $config
 * @var array<string, mixed> $extra
 */
?>
<ul class="nav nav-tabs mt-3">
  <?php foreach ($pestanas as $clave => $etiqueta): ?>
    <li class="nav-item">
      <a class="nav-link <?= $clave === $seccion ? 'active' : '' ?>" href="<?= url("configuracion/{$clave}") ?>"><?= e($etiqueta) ?></a>
    </li>
  <?php endforeach; ?>
</ul>

<div class="card border-top-0 rounded-top-0">
  <div class="card-body">
    <form action="<?= url("configuracion/{$seccion}") ?>" method="POST">
      <?= csrf_field() ?>
      <?= $view->partial("configuracion/_{$seccion}", ['valores' => $config[$seccion], 'config' => $config, ...$extra]) ?>
      <div class="text-end mt-3">
        <button type="submit" class="btn btn-success">💾 Guardar</button>
      </div>
    </form>
  </div>
</div>

<?php if ($seccion === 'turnos'): ?>
  <div class="card mt-3">
    <div class="card-body d-flex flex-wrap align-items-end gap-2">
      <form action="<?= url('configuracion/feriados/importar') ?>" method="POST" class="d-flex align-items-end gap-2">
        <?= csrf_field() ?>
        <div>
          <label for="anio" class="form-label">Importar feriados nacionales de Argentina</label>
          <input type="number" class="form-control" id="anio" name="anio" min="2020" max="2100" value="<?= date('Y') ?>" style="width: 120px;">
        </div>
        <button type="submit" class="btn btn-outline-primary">Importar</button>
      </form>
      <small class="text-muted">Fuente: ArgentinaDatos. Se agregan a la lista de feriados sin borrar los que ya cargaste.</small>
    </div>
  </div>
<?php endif; ?>

<?php if ($seccion === 'notificaciones'): ?>
  <div class="card mt-3">
    <div class="card-header bg-light"><strong>Últimos avisos enviados</strong></div>
    <div class="card-body table-responsive">
      <?php if ($extra['registro'] === []): ?>
        <p class="text-muted mb-0">Todavía no se envió ningún aviso.</p>
      <?php else: ?>
        <table class="table table-sm align-middle">
          <thead><tr><th>Fecha</th><th>Cliente</th><th>Turno</th><th>Tipo</th><th>Canal</th><th>Destino</th><th>Resultado</th></tr></thead>
          <tbody>
            <?php foreach ($extra['registro'] as $n): ?>
              <tr>
                <td><?= format_date($n['created_at'], 'd/m/Y H:i') ?></td>
                <td><?= e($n['cliente'] ?? '—') ?></td>
                <td><?= $n['turno_fecha'] ? format_date($n['turno_fecha']) . ' ' . e(substr($n['turno_hora'], 0, 5)) : '—' ?></td>
                <td><?= e($n['tipo']) ?></td>
                <td><?= e($n['canal']) ?></td>
                <td><?= e($n['destino']) ?></td>
                <td>
                  <span class="badge bg-<?= $n['estado'] === 'enviado' ? 'success' : 'danger' ?>"><?= e($n['estado']) ?></span>
                  <?php if ($n['detalle']): ?><div class="small text-muted"><?= e($n['detalle']) ?></div><?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>
