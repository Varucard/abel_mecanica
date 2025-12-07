$(document).ready(function() {
  // Inicializar Select2 solo si existe
  if ($('.select2').length) {
    $('.select2').select2();
  }

  // Inicializar DataTable solo si existe la tabla
  if ($('#tabla_ordenes').length) {
    $('#tabla_ordenes').DataTable({
      language: {
        decimal: ",",
        thousands: ".",
        lengthMenu: "Mostrar _MENU_ registros",
        zeroRecords: "No se encontraron resultados",
        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
        infoEmpty: "Mostrando 0 a 0 de 0 registros",
        infoFiltered: "(filtrado de _MAX_ registros totales)",
        search: "Buscar:",
        paginate: {
          first: "Primero",
          last: "Último",
          next: "Siguiente",
          previous: "Anterior"
        }
      },
      order: [[5, 'desc']]
    });
  }

  // Ocultar alerta de éxito después de 10 segundos
  if ($('#success-alert').length) {
    $('#success-alert').delay(10000).fadeOut('slow');
  }

  // Calcular costo total automáticamente al seleccionar servicios
  if ($('#servicio_id').length) {
    $('#servicio_id').on('change', function() {
      let total = 0;
      $('#servicio_id option:selected').each(function() {
        let precio = parseFloat($(this).data('precio')) || 0;
        total += precio;
      });
      $('#costo').val(total.toFixed(2));
    });
  }
});

// Función para cambiar estado de orden
function cambiarEstadoOrden(id, estado) {
  if (confirm('¿Desea marcar esta orden como finalizada?')) {
    window.location.href = '../../shields/procesar_orden.php?action=cambiar_estado&id=' + id + '&estado=' + estado;
  }
}
