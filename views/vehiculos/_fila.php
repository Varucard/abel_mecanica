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
  '<div class="text-nowrap"><a href="' . url("vehiculos/{$v['id']}") . '" class="btn btn-sm btn-info">Ver</a> '
    . '<a href="' . url("vehiculos/{$v['id']}/editar") . '" class="btn btn-sm btn-primary">Editar</a> '
    . $view->partial('partials/delete_button', [
      'action' => "vehiculos/{$v['id']}/estado",
      'label' => $activo ? 'Desactivar' : 'Activar',
      'class' => $activo ? 'btn-warning' : 'btn-success',
      'confirm' => '¿' . ($activo ? 'Desactivar' : 'Activar') . " el vehículo {$v['patente']}?",
    ]) . '</div>',
];
