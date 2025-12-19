$(document).ready(function() {
  $('#tabla_clientes').DataTable({
    language: {
      url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-AR.json'
    },
    order: [[1, 'asc']]
  });

  // Ocultar alerta de éxito después de 10 segundos
  $('#success-alert').delay(10000).fadeOut('slow');
});

function eliminarCliente(id, nombre) {
  if (confirm('¿Está seguro de eliminar al cliente ' + nombre + '?')) {
    window.location.href = '../shields/procesar_cliente.php?action=delete&id=' + id;
  }
}

function cambiarEstadoCliente(id, estado) {
  var accion = estado === 'activo' ? 'desactivar' : 'activar';
  if (confirm('¿Está seguro de ' + accion + ' este cliente?')) {
    window.location.href = '../shields/procesar_cliente.php?action=cambiar_estado&id=' + id + '&estado=' + estado;
  }
}
