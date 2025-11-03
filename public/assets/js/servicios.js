$(document).ready(function() {
  // Inicializar Select2
  $('.select2').select2();

  // Ocultar alerta de éxito después de 10 segundos
  $('#success-alert').delay(10000).fadeOut('slow');

  // Autocompletar costo cuando se seleccionan uno o varios servicios
  $('#servicio_id').on('change', function() {
    var total = 0.0;
    $('#servicio_id option:selected').each(function() {
      var p = $(this).data('precio');
      // p puede venir como string o number
      if (typeof p !== 'undefined' && p !== null && p !== '') {
        var num = parseFloat(p);
        if (!isNaN(num)) total += num;
      }
    });
    if (total > 0) {
      // fijar con dos decimales
      $('#costo').val(total.toFixed(2));
    } else {
      $('#costo').val('');
    }
  });
});
