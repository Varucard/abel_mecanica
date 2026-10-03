<?php
/** Fila del listado de órdenes (paginado en el servidor). @var array<string, mixed> $o @var list<\App\Enums\EstadoOrden> $estados */
use App\Enums\EstadoOrden;
use App\Services\OrdenService;

$estado = EstadoOrden::from($o['estado']);
$opciones = implode('', array_map(
  fn(EstadoOrden $op) => '<option value="' . e($op->value) . '" ' . selected($op === $estado) . '>' . e($op->label()) . '</option>',
  $estados
));

return [
  (int) $o['id'],
  e($o['cliente']),
  e($o['vehiculo']) . ($o['mecanico'] ? '<div class="small text-muted">🔧 ' . e($o['mecanico']) . '</div>' : ''),
  e($o['servicios'] ?? '') . ($o['repuestos'] ? '<div class="small text-muted mt-1"><strong>Repuestos:</strong> ' . e($o['repuestos']) . '</div>' : '')
    . ($o['presupuesto_respuesta'] ? '<div class="small ' . ($o['presupuesto_respuesta'] === 'aceptado' ? 'text-success">✔ Presupuesto aceptado' : 'text-danger">✖ Presupuesto rechazado') . '</div>' : ''),
  '$ ' . money($o['total']),
  match (true) {
    $estado === EstadoOrden::Cancelado => '—',
    (float) $o['saldo'] > 0 => '<span class="text-danger">$ ' . money($o['saldo']) . '</span>',
    default => '<span class="badge bg-success">Pagada</span>',
  },
  format_date($o['created_at']),
  '<select class="form-select form-select-sm estado-orden-select estado-' . e($estado->value) . '" data-url="' . e(url("ordenes/{$o['id']}/estado")) . '" aria-label="Estado de la orden ' . (int) $o['id'] . '">' . $opciones . '</select>',
  '<a href="' . url("ordenes/{$o['id']}") . '" class="btn btn-sm btn-info">Ver</a> '
    . (OrdenService::editable($estado) ? '<a href="' . url("ordenes/{$o['id']}/editar") . '" class="btn btn-sm btn-primary">Editar</a>' : ''),
];
