$(document).ready(function () {

  // Inicializar Select2
  if ($('.select2').length) {
    $('.select2').select2({
      width: '100%'
    });
  }

  // Inicializar DataTable si existe
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
      }
    });
  }

  // Ocultar alerta de éxito
  if ($('#success-alert').length) {
    $('#success-alert').delay(10000).fadeOut('slow');
  }

  // ===============================
  // CÁLCULO AUTOMÁTICO DEL TOTAL
  // ===============================
  function calcularTotal() {
    let total = 0;

    // Servicios
    $('select[name="servicio_id[]"] option:selected').each(function () {
      let precio = parseFloat($(this).data('precio'));
      if (!isNaN(precio)) {
        total += precio;
      }
    });

    // Repuestos (opcionales)
    $('select[name="repuesto_id[]"] option:selected').each(function () {
      let precio = parseFloat($(this).data('precio'));
      if (!isNaN(precio)) {
        total += precio;
      }
    });

    $('input[name="costo"]').val(total.toFixed(2));
  }

  // Escuchar cambios (Select2 compatible)
  $(document).on(
    'change',
    'select[name="servicio_id[]"], select[name="repuesto_id[]"]',
    calcularTotal
  );

});

// ===============================
// CAMBIAR ESTADO DE ORDEN
// ===============================
function cambiarEstadoOrden(id, estado) {
  if (confirm('¿Desea marcar esta orden como finalizada?')) {
    window.location.href =
      '../../shields/procesar_orden.php?action=cambiar_estado&id=' +
      id +
      '&estado=' +
      estado;
  }
}
