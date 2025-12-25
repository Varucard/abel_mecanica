$(document).ready(function () {

  // ===============================
  // Inicializar Select2
  // ===============================
  // Vehículo (single)
  $('#vehiculo_id').select2({
    placeholder: 'Seleccione un vehículo',
    allowClear: true,
    width: '100%'
  });

  // Servicios (multiple)
  $('#servicio_id').select2({
    placeholder: 'Seleccione uno o más servicios',
    allowClear: true,
    width: '100%'
  });

  // Repuestos (multiple)
  $('#repuesto_id').select2({
    placeholder: 'Seleccione uno o más repuestos',
    allowClear: true,
    width: '100%'
  });

  // ===============================
  // Inicializar DataTable
  // ===============================
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

  // ===============================
  // Ocultar alerta de éxito
  // ===============================
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
      const precio = parseFloat($(this).data('precio'));
      if (!isNaN(precio)) {
        total += precio;
      }
    });

    // Repuestos (opcionales)
    $('select[name="repuesto_id[]"] option:selected').each(function () {
      const precio = parseFloat($(this).data('precio'));
      if (!isNaN(precio)) {
        total += precio;
      }
    });

    $('input[name="costo"]').val(total.toFixed(2));
  }

  // Escuchar cambios (compatible Select2)
  $(document).on(
    'change',
    'select[name="servicio_id[]"], select[name="repuesto_id[]"]',
    calcularTotal
  );

  // Calcular al cargar (por seguridad)
  calcularTotal();

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
