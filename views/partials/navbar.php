<nav class="btn-group w-100 mb-3" role="navigation" style="gap: 5px;">
  <a href="<?= url('/') ?>" class="btn btn-outline-secondary flex-grow-0" title="Inicio">🏠</a>
  <div class="dropdown flex-fill">
    <a href="#" class="btn btn-primary w-100">⚙️ Configuración</a>
    <div class="dropdown-menu">
      <a href="<?= url('servicios') ?>">Servicios</a>
      <a href="<?= url('marcas') ?>">Marcas</a>
      <a href="<?= url('modelos') ?>">Modelos</a>
      <?php if (auth()->esAdministrador()): ?>
        <a href="<?= url('configuracion') ?>">Sistema</a>
        <a href="<?= url('empleados') ?>">Empleados</a>
        <a href="<?= url('usuarios') ?>">Usuarios</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="dropdown flex-fill">
    <a href="<?= url('clientes') ?>" class="btn btn-success w-100">👤 Clientes</a>
    <div class="dropdown-menu">
      <a href="<?= url('clientes/crear') ?>">Registrar cliente</a>
      <a href="<?= url('clientes') ?>">Ver clientes</a>
      <a href="<?= url('deudores') ?>">Deudores</a>
    </div>
  </div>
  <div class="dropdown flex-fill">
    <a href="<?= url('vehiculos') ?>" class="btn btn-info w-100">🚗 Vehículos</a>
    <div class="dropdown-menu">
      <a href="<?= url('vehiculos/crear') ?>">Registrar vehículo</a>
      <a href="<?= url('vehiculos') ?>">Ver vehículos</a>
    </div>
  </div>
  <div class="dropdown flex-fill">
    <a href="<?= url('ordenes') ?>" class="btn btn-warning w-100">📝 Órdenes</a>
    <div class="dropdown-menu">
      <a href="<?= url('ordenes/crear') ?>">Registrar orden</a>
      <a href="<?= url('ordenes') ?>">Ver órdenes</a>
    </div>
  </div>
  <div class="dropdown flex-fill">
    <a href="<?= url('repuestos') ?>" class="btn btn-dark w-100">📦 Stock</a>
    <div class="dropdown-menu">
      <a href="<?= url('repuestos') ?>">Repuestos</a>
      <a href="<?= url('proveedores') ?>">Proveedores</a>
    </div>
  </div>
  <div class="dropdown flex-fill">
    <a href="<?= url('turnos') ?>" class="btn btn-secondary w-100">📂 Turnos</a>
    <div class="dropdown-menu">
      <a href="<?= url('turnos/crear') ?>">Registrar turno</a>
      <a href="<?= url('turnos') ?>">Ver turnos</a>
    </div>
  </div>
</nav>
