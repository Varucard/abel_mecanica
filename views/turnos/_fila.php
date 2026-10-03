<?php
/**
 * Fila de la agenda de turnos.
 *
 * @var array<string, mixed> $t
 * @var list<\App\Enums\EstadoTurno> $estados
 * @var array{canal: bool, whatsapp: bool} $avisos
 */
$opciones = implode('', array_map(
  fn($op) => '<option value="' . e($op->value) . '" ' . selected($op->value === $t['estado']) . '>' . e($op->label()) . '</option>',
  $estados
));
$activo = in_array($t['estado'], ['pendiente', 'confirmado'], true);

return [
  '<strong>' . format_date($t['fecha']) . '</strong><br><small class="text-muted">' . e(substr($t['hora'], 0, 5)) . ' hs</small>',
  e($t['cliente']),
  e($t['vehiculo']),
  '<small>' . e($t['descripcion'] ?? '') . '</small>',
  '<select class="form-select form-select-sm estado-turno-select estado-' . e($t['estado']) . '" data-url="' . e(url("turnos/{$t['id']}/estado")) . '" aria-label="Estado del turno ' . (int) $t['id'] . '">' . $opciones . '</select>',
  ($activo && $t['fecha'] >= date('Y-m-d') ? $view->partial('turnos/_recordatorio', ['turno' => $t, 'avisos' => $avisos, 'volver' => '']) : '')
    . '<div class="text-nowrap">'
    . ($activo ? '<a href="' . url('ordenes/crear?turno_id=' . $t['id']) . '" class="btn btn-sm btn-warning">Crear orden</a> ' : '')
    . '<a href="' . url("turnos/{$t['id']}/editar") . '" class="btn btn-sm btn-primary">Editar</a> '
    . $view->partial('partials/delete_button', [
      'action' => "turnos/{$t['id']}/eliminar",
      'label' => 'Eliminar',
      'confirm' => '¿Eliminar el turno del ' . format_date($t['fecha']) . ' a las ' . substr($t['hora'], 0, 5) . '?',
    ]) . '</div>',
];
