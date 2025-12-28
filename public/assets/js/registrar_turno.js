$(document).ready(function() {
  // Inicializar Select2 en los selects
  $('#cliente_id').select2({
    placeholder: 'Seleccione un cliente...',
    allowClear: true,
    width: '100%'
  });

  $('#vehiculo_id').select2({
    placeholder: 'Seleccione un vehículo...',
    allowClear: true,
    width: '100%'
  });

  // Recargar vehículos cuando cambia el cliente
  $('#cliente_id').on('change', function() {
    const clienteId = $(this).val();
    const $vehiculoSelect = $('#vehiculo_id');

    if (clienteId) {
      $.get('registrar_turno.php', {
        get_vehiculos: clienteId
      }, function(data) {
        // Limpiar y recargar opciones
        $vehiculoSelect.empty();
        $vehiculoSelect.append('<option value="">Seleccione un vehículo...</option>');
        
        data.forEach(v => {
          $vehiculoSelect.append(`<option value="${v.id}">${v.info}</option>`);
        });

        $vehiculoSelect.prop('disabled', false);
        $vehiculoSelect.select2('destroy').select2({
          placeholder: 'Seleccione un vehículo...',
          allowClear: true,
          width: '100%'
        });
      }, 'json');
    } else {
      $vehiculoSelect.empty();
      $vehiculoSelect.append('<option value="">Seleccione primero un cliente...</option>');
      $vehiculoSelect.prop('disabled', true);
      $vehiculoSelect.select2('destroy').select2({
        placeholder: 'Seleccione primero un cliente...',
        allowClear: true,
        width: '100%'
      });
    }
  });

  // Guardar edición / alta por AJAX (mismo archivo)
  $('#formTurnoEdit').submit(function(e) {
    e.preventDefault();
    $.post('registrar_turno.php', $(this).serialize(), function(response) {
      if (response.status === 'success') {
        window.location.href = 'listar_turno.php?success=update';
      } else {
        alert(response.message || 'Error al guardar el turno');
      }
    }, 'json');
  });
});