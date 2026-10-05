<div class="card mt-3">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4 class="mb-0">Vehículos registrados</h4>
    <div class="d-flex gap-2 align-items-center flex-wrap">
      <?= $view->partial('partials/filtro_estado', ['id' => 'filtro_vehiculos', 'nombre' => 'estado', 'etiqueta' => 'Mostrar', 'opciones' => ['' => 'Todos', 'activo' => 'Activos', 'inactivo' => 'Inactivos']]) ?>
      <a href="<?= url('vehiculos/crear') ?>" class="btn btn-seccion"><?= icono('plus-lg') ?> Registrar vehículo</a>
    </div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table data-vacio="Todavía no hay vehículos registrados." class="table table-striped table-bordered" data-server="<?= e(url('vehiculos/datos')) ?>" data-filtros="#filtro_vehiculos" data-order='[[4, "asc"]]'>
        <thead>
          <tr>
            <th>Cliente</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Año</th>
            <th>Patente</th>
            <th>Kilometraje</th>
            <th>Estado</th>
            <th data-orderable="false">Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>
