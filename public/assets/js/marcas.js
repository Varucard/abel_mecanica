$(document).ready(function() {
  $('#tabla_marcas').DataTable({
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-AR.json' },
    order: [[0, 'asc']]
  });

  $('#success-alert, #error-alert').delay(4000).fadeOut('slow');
});

function eliminarMarca(id, nombre) {
  if (confirm(`¿Estás seguro de eliminar la marca "${nombre}"?`)) {
    window.location.href = `../shields/procesar_marca.php?action=delete&id=${id}`;
  }
}
