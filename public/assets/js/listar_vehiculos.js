$(document).ready(function() {
  // Inicializar DataTable
  if ($('#tabla_vehiculos').length) {
    $('#tabla_vehiculos').DataTable({
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-AR.json'
      },
      order: [[4, 'asc']]
    });
  }
});
