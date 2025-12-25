$(document).ready(function () {

  // ===============================
  // Inicializar Select2
  // ===============================
  // Vehículo (single)
  if ($('#vehiculo_id').length) {
    $('#vehiculo_id').select2({
      placeholder: 'Seleccione un vehículo',
      allowClear: true,
      width: '100%'
    });
  }

  // Servicios (multiple)
  if ($('#servicio_id').length) {
    $('#servicio_id').select2({
      placeholder: 'Seleccione uno o más servicios',
      allowClear: true,
      width: '100%'
    });
  }

  // Repuestos (multiple)
  if ($('#repuesto_id').length) {
    $('#repuesto_id').select2({
      placeholder: 'Seleccione uno o más repuestos',
      allowClear: true,
      width: '100%'
    });
  }

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
  // Ocultar alerta de éxito/error
  // ===============================
  if ($('#success-alert').length) {
    $('#success-alert').delay(10000).fadeOut('slow');
  }
  if ($('#error-alert').length) {
    $('#error-alert').delay(10000).fadeOut('slow');
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
  if ($('input[name="costo"]').length) {
    calcularTotal();
  }

  // ===============================
  // CAMBIO DE ESTADO DESDE EL SELECT
  // ===============================
  $(document).on('change', '.estado-orden-select', function () {
    const id = $(this).data('id');
    const estado = $(this).val();

    if (!id || !estado) return;

    const labels = {
      pendiente: 'pendiente',
      en_proceso: 'en proceso',
      finalizado: 'finalizada',
      cancelada: 'cancelada'
    };

    const label = labels[estado] || estado.replace('_', ' ');

    if (!confirm('¿Desea cambiar el estado de la orden a "' + label + '"?')) {
      // Si cancela, recargamos para volver al valor original
      location.reload();
      return;
    }

    // Redirigir al procesador
    window.location.href =
      '../shields/procesar_orden.php?action=cambiar_estado&id=' +
      encodeURIComponent(id) +
      '&estado=' +
      encodeURIComponent(estado);
  });

});