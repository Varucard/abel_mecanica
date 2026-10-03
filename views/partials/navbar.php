<?php
/**
 * Menú principal. En celulares y tablets los submenús se abren tocando el botón;
 * en computadoras también al pasar el mouse.
 */
$menus = [
  ['⚙️ Configuración', 'primary', array_filter([
    'servicios' => 'Servicios',
    'combos' => 'Combos de servicios',
    'marcas' => 'Marcas',
    'modelos' => 'Modelos',
    'configuracion' => auth()->esAdministrador() ? 'Sistema' : null,
    'empleados' => auth()->esAdministrador() ? 'Empleados' : null,
    'usuarios' => auth()->esAdministrador() ? 'Usuarios' : null,
    'auditoria' => auth()->esAdministrador() ? 'Auditoría' : null,
    'logs' => auth()->esAdministrador() ? 'Registro del sistema' : null,
  ])],
  ['👤 Clientes', 'success', ['clientes/crear' => 'Registrar cliente', 'clientes' => 'Ver clientes', 'deudores' => 'Deudores']],
  ['🚗 Vehículos', 'info', ['vehiculos/crear' => 'Registrar vehículo', 'vehiculos' => 'Ver vehículos']],
  ['📝 Órdenes', 'warning', array_filter([
    'ordenes/crear' => 'Registrar orden',
    'ordenes' => 'Ver órdenes',
    'reportes' => auth()->esAdministrador() ? 'Reportes' : null,
  ])],
  ['📦 Stock', 'dark', array_filter([
    'repuestos' => 'Repuestos',
    'proveedores' => 'Proveedores',
    'precios' => auth()->esAdministrador() ? 'Actualizar precios' : null,
  ])],
  ['📂 Turnos', 'secondary', ['turnos/crear' => 'Registrar turno', 'turnos/semana' => 'Agenda semanal', 'turnos' => 'Ver turnos']],
];
?>
<nav class="menu-principal mb-3" aria-label="Menú principal">
  <a href="<?= url('/') ?>" class="btn btn-outline-secondary menu-inicio" title="Inicio" aria-label="Inicio">🏠</a>
  <?php foreach ($menus as [$titulo, $color, $items]): ?>
    <div class="dropdown">
      <button class="btn btn-<?= $color ?> w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><?= e($titulo) ?></button>
      <ul class="dropdown-menu w-100">
        <?php foreach ($items as $ruta => $etiqueta): ?>
          <li><a class="dropdown-item" href="<?= url($ruta) ?>"><?= e($etiqueta) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endforeach; ?>
  <form class="menu-buscador" action="<?= url('buscar') ?>" method="GET" role="search">
    <input type="search" name="q" class="form-control" placeholder="Buscar patente, DNI, apellido u orden…" aria-label="Buscar"
      value="<?= e(current_section() === 'buscar' ? (string) ($_GET['q'] ?? '') : '') ?>">
  </form>
</nav>
