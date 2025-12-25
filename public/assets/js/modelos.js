$(document).ready(function() {
  // Inicializar Select2 con placeholder
  $('#marca_id').select2({
    placeholder: 'Seleccione una marca',
    allowClear: true,
    width: '100%'
  });

  // Inicializar DataTable
  $('#tabla_modelos').DataTable({
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-AR.json' },
    order: [[0, 'asc'], [1, 'asc']]
  });

  // Ocultar alertas después de 4 segundos
  $('#success-alert, #error-alert').delay(4000).fadeOut('slow');
});

function eliminarModelo(id, nombre) {
  if (confirm(`¿Estás seguro de eliminar el modelo "${nombre}"?`)) {
    window.location.href = `../shields/procesar_modelo.php?action=delete&id=${id}`;
  }
}