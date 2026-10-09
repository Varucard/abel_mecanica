<?php
/** Fila del listado de vehículos. @var array<string, mixed> $v */
$activo = $v['estado'] === 'activo';

return [
  e($v['cliente']),
  e($v['marca']),
  e($v['modelo']),
  (int) $v['anio'],
  e($v['patente']),
  $v['kilometraje'] !== null ? number_format((float) $v['kilometraje'], 0, ',', '.') . ' km' : '—',
  '<span class="badge bg-' . ($activo ? 'success' : 'secondary') . '">' . e($v['estado']) . '</span>',
  '<div class="acciones-fila">' . boton_accion("vehiculos/{$v['id']}", 'eye', 'Ver') . ' '
    . boton_accion("vehiculos/{$v['id']}/editar", 'pencil', 'Editar', 'btn-outline-primary') . ' '
    . $view->partial('partials/delete_button', [
      'action' => "vehiculos/{$v['id']}/estado",
      'label' => $activo ? 'Desactivar' : 'Activar',
      'class' => $activo ? 'btn-outline-warning' : 'btn-outline-success',
      'icono' => $activo ? 'slash-circle' : 'check-circle',
      'confirm' => null, // se hace al toque y se ofrece "Deshacer"
    ]) . '</div>',
];
