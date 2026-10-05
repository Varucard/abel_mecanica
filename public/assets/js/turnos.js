/**
 * Turnos: vehículos según el cliente elegido y cambio de estado en la agenda.
 */
$(function () {
  const $form = $('#form_turno');

  if ($form.length) {
    const $vehiculo = $('#vehiculo_id');

    $('#cliente_id').on('change', function () {
      const clienteId = $(this).val();

      if (!clienteId) {
        window.cargarOpciones($vehiculo, [], 'Seleccione primero un cliente');
        return;
      }

      $.getJSON($form.data('vehiculos-url').replace('{id}', clienteId))
        .done((vehiculos) => {
          window.cargarOpciones($vehiculo, vehiculos, vehiculos.length ? 'Seleccione un vehículo' : 'El cliente no tiene vehículos activos');
        })
        .fail(() => window.avisar('No se pudieron cargar los vehículos del cliente.'));
    });
  }

  window.estadoInline('.estado-turno-select', 'del turno');
});
