<?php
/** @var list<\App\Enums\EstadoOrden> $estados */
$view->script('ordenes.js');
$filtro = ['' => 'Todas', 'abiertas' => 'Autos en el taller (recibidos y en reparación)', 'con_saldo' => 'Con saldo sin cobrar'];
foreach ($estados as $e) {
  $filtro[$e->value] = $e->label();
}
?>
<div class="card mt-3">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4 class="mb-0">Órdenes registradas</h4>
    <div class="d-flex gap-2 align-items-center flex-wrap">
      <?= $view->partial('partials/filtro_estado', ['id' => 'filtro_ordenes', 'nombre' => 'estado', 'etiqueta' => 'Mostrar', 'opciones' => $filtro,
        'valor' => (string) ($_GET['estado'] ?? '')]) ?>
      <a href="<?= url('recepcion') ?>" class="btn btn-seccion"><?= icono('car-front-fill') ?> Llegó un auto</a>
    </div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table data-vacio="Todavía no hay órdenes." data-vacio-accion="<?= e(url('recepcion')) ?>" data-vacio-boton="Recibir el primer auto" class="table table-striped table-bordered" data-server="<?= e(url('ordenes/datos')) ?>" data-filtros="#filtro_ordenes" data-order='[[0, "desc"]]'>
        <thead>
          <tr>
            <th data-prioridad="1">N°</th>
            <th class="col-cliente" data-prioridad="6">Cliente</th>
            <th class="col-vehiculo" data-prioridad="5">Vehículo</th>
            <th data-orderable="false">Servicio(s) - Repuesto(s)</th>
            <th>Total</th>
            <th data-prioridad="4">Saldo</th>
            <th>Fecha</th>
            <th data-prioridad="3">Estado</th>
            <th data-orderable="false" data-prioridad="2">Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>
