$(document).ready(function() {
  $('#tabla_modelos').DataTable({
    language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-AR.json' },
    order: [[0, 'asc'], [1, 'asc']]
  });

  $('#success-alert, #error-alert').delay(4000).fadeOut('slow');
});

function eliminarModelo(id, nombre) {
  if (confirm(`¿Estás seguro de eliminar el modelo "${nombre}"?`)) {
    window.location.href = `../shields/procesar_modelo.php?action=delete&id=${id}`;
  }
}
