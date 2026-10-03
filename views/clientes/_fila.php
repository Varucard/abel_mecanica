<?php
/** Fila del listado de clientes. @var array<string, mixed> $c @var array<int, float> $saldos */
$activo = $c['estado'] === 'activo';
$saldo = $saldos[(int) $c['id']] ?? null;

return [
  e($c['nombre']),
  e($c['apellido']),
  e($c['dni']),
  e($c['telefono']),
  e($c['email'] ?? ''),
  e($c['direccion'] ?? ''),
  $saldo ? '<span class="text-danger">$ ' . money($saldo) . '</span>' : '—',
  '<span class="badge bg-' . ($activo ? 'success' : 'secondary') . '">' . e($c['estado']) . '</span>',
  '<div class="text-nowrap"><a href="' . url("clientes/{$c['id']}") . '" class="btn btn-sm btn-info">Ver</a> '
    . '<a href="' . url("clientes/{$c['id']}/editar") . '" class="btn btn-sm btn-primary">Editar</a> '
    . $view->partial('partials/delete_button', [
      'action' => "clientes/{$c['id']}/estado",
      'label' => $activo ? 'Desactivar' : 'Activar',
      'class' => $activo ? 'btn-warning' : 'btn-success',
      'confirm' => '¿' . ($activo ? 'Desactivar' : 'Activar') . " al cliente {$c['nombre']} {$c['apellido']}?",
    ])
    . $view->partial('partials/delete_button', [
      'action' => "clientes/{$c['id']}/eliminar",
      'label' => 'Eliminar',
      'confirm' => "¿Eliminar al cliente {$c['nombre']} {$c['apellido']}? Esta acción no se puede deshacer.",
    ]) . '</div>',
];
