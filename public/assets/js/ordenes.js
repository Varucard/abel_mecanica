$(document).ready(function() {
  $('#tabla_ordenes').DataTable({
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-AR.json'
    },
    order: [[5, 'desc']]
  });

  // Ocultar alerta de éxito después de 10 segundos
  $('#success-alert').delay(10000).fadeOut('slow');
});

function cambiarEstadoOrden(id, estado) {
  if (confirm('¿Desea marcar esta orden como finalizada?')) {
    window.location.href = 'procesar_orden.php?action=cambiar_estado&id=' + id + '&estado=' + estado;
  }
}
