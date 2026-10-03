<?php /** @var list<array<string, mixed>> $combos */ ?>
<div class="card mt-3">
  <div class="card-header bg-light d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Combos de servicios</h4>
    <a href="<?= url('combos/crear') ?>" class="btn btn-primary">+ Nuevo combo</a>
  </div>
  <div class="card-body">
    <p class="text-muted small">Un combo agrupa servicios y repuestos (por ejemplo "Service 10.000 km") para cargarlos de una vez en una orden.</p>
    <?php if ($combos === []): ?>
      <p class="text-muted mb-0">Todavía no hay combos.</p>
    <?php endif; ?>
    <div class="row g-3">
      <?php foreach ($combos as $c): ?>
        <?php $total = array_sum(array_map(fn($i) => (float) $i['precio'] * (float) $i['cantidad'], $c['items'])); ?>
        <div class="col-md-6">
          <div class="card h-100 <?= $c['activo'] ? '' : 'opacity-50' ?>">
            <div class="card-body">
              <h5 class="card-title"><?= e($c['nombre']) ?> <?= $c['activo'] ? '' : '<span class="badge bg-secondary">inactivo</span>' ?></h5>
              <?php if ($c['descripcion']): ?><p class="small text-muted"><?= e($c['descripcion']) ?></p><?php endif; ?>
              <ul class="small mb-2">
                <?php foreach ($c['items'] as $i): ?>
                  <li><?= qty($i['cantidad']) ?> × <?= e(($i['repuesto_id'] ? 'Repuesto: ' : '') . $i['nombre']) ?></li>
                <?php endforeach; ?>
              </ul>
              <p class="mb-2"><strong>Total a precios actuales: $ <?= money($total) ?></strong></p>
              <a href="<?= url("combos/{$c['id']}/editar") ?>" class="btn btn-sm btn-primary">Editar</a>
              <?= $view->partial('partials/delete_button', ['action' => "combos/{$c['id']}/eliminar", 'label' => 'Eliminar', 'confirm' => "¿Eliminar el combo \"{$c['nombre']}\"?"]) ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
