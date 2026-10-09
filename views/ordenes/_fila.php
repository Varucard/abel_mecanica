<?php
/** Fila del listado de órdenes (paginado en el servidor). @var array<string, mixed> $o @var list<\App\Enums\EstadoOrden> $estados */
use App\Enums\EstadoOrden;
use App\Services\OrdenService;

$estado = EstadoOrden::from($o['estado']);
$opciones = implode('', array_map(
  fn(EstadoOrden $op) => '<option value="' . e($op->value) . '" ' . selected($op === $estado) . '>' . e($op->label()) . '</option>',
  $estados
));

$sinItems = $o['servicios'] === null && $o['repuestos'] === null;

return [
  // En el celular las columnas de cliente y vehículo se pliegan: la patente va acá, a la vista.
  '<a href="' . e(url("ordenes/{$o['id']}")) . '" class="fw-semibold d-inline-block py-1 pe-2">#' . (int) $o['id'] . '</a>'
    . '<div class="small d-md-none">' . e(strtok((string) $o['vehiculo'], ' ')) . '</div>',
  e($o['cliente']),
  e($o['vehiculo']) . ($o['mecanico'] ? '<div class="small text-muted">' . icono('wrench') . ' ' . e($o['mecanico']) . '</div>' : ''),
  ($sinItems ? '<span class="text-muted small">Sin trabajos cargados todavía</span>' : '')
    . e($o['servicios'] ?? '') . ($o['repuestos'] ? '<div class="small text-muted mt-1"><strong>Repuestos:</strong> ' . e($o['repuestos']) . '</div>' : '')
    . ($o['presupuesto_respuesta'] ? '<div class="small ' . ($o['presupuesto_respuesta'] === 'aceptado' ? 'text-success">' . icono('check-lg') . ' Presupuesto aceptado' : 'text-danger">' . icono('x-lg') . ' Presupuesto rechazado') . '</div>' : ''),
  importe($o['total']),
  match (true) {
    // Sin trabajos cargados no hay nada que pagar todavía: "Pagada" confundía.
    $estado === EstadoOrden::Cancelado, (float) $o['total'] <= 0 => '—',
    (float) $o['saldo'] > 0 => '<span class="text-danger">' . importe($o['saldo']) . '</span>',
    default => '<span class="badge bg-success">Pagada</span>',
  },
  format_date($o['created_at']),
  '<select class="form-select form-select-sm select-estado estado-orden-select estado-' . e($estado->value) . '" data-url="' . e(url("ordenes/{$o['id']}/estado")) . '"'
    . ' data-actual="' . e($estado->value) . '"' . ($sinItems ? ' data-sin-items="1"' : '')
    . ' aria-label="Estado de la orden ' . (int) $o['id'] . '">' . $opciones . '</select>',
  boton_accion("ordenes/{$o['id']}", 'eye', 'Ver') . ' '
    . (OrdenService::editable($estado) ? boton_accion("ordenes/{$o['id']}/editar", 'pencil', 'Editar', 'btn-outline-primary') : ''),
];
