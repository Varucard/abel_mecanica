// Detectar si estamos en modo edición por la URL (?id=)
function esEdicionPorURL() {
  return new URLSearchParams(window.location.search).has('id');
}

$(document).ready(function() {
  // Inicializar Select2 para clientes
  if ($('.select2').length) {
    $('.select2').select2();
  }

  // Ocultar alerta de éxito después de 10 segundos
  if ($('#success-alert').length) {
    $('#success-alert').delay(10000).fadeOut('slow');
  }

  // Verificar si cliente ya tiene vehículos al enviar el formulario
  $('#form_vehiculo').on('submit', function(e) {
    var cliente_id  = $('#cliente_id').val();

    // Si estamos en EDICIÓN (URL tiene ?id=...), NO mostramos mensaje
    if (esEdicionPorURL()) {
      // console.log('MODO EDICIÓN: no se verifica cantidad de vehículos.');
      return true; // dejamos que el form se envíe normalmente
    }

    // Solo en ALTA NUEVA hacemos la verificación
    if (cliente_id) {
      e.preventDefault(); // frenamos envío hasta terminar la verificación

      $.ajax({
        url: 'registrar_vehiculo.php',
        type: 'GET',
        data: { action: 'verificar_cliente', cliente_id: cliente_id },
        dataType: 'json',
        success: function(data) {
          if (data.total > 0) {
            var mensaje = 'Este usuario ya tiene ' + data.total + ' vehículo(s) asignado(s). ¿Desea cargarle otro?';
            if (!confirm(mensaje)) {
              return false; // no enviamos el formulario
            }
          }
          // si aceptó o no tenía vehículos, enviamos el formulario
          $('#form_vehiculo')[0].submit();
        },
        error: function(xhr, status, error) {
          console.error('Error en la verificación de cliente:', error);
          console.error('Respuesta del servidor:', xhr.responseText);
          // ante error, dejamos que se envíe igual
          $('#form_vehiculo')[0].submit();
        }
      });
    }
  });

  // Cascada de marcas/modelos
  $('#marca_id').on('change', function() {
    var marca_id = $(this).val();
    
    if (marca_id) {
      // Obtener modelos de la marca seleccionada
      $.ajax({
        url: 'registrar_vehiculo.php',
        type: 'GET',
        data: { action: 'get_modelos', marca_id: marca_id },
        dataType: 'json',
        success: function(data) {
          var modeloSelect = $('#modelo_id');
          modeloSelect.empty().append('<option value="">Seleccione un modelo</option>');
          modeloSelect.prop('disabled', false);
          
          $.each(data, function(index, modelo) {
            modeloSelect.append('<option value="' + modelo.id + '">' + modelo.nombre + '</option>');
          });
        },
        error: function(xhr, status, error) {
          console.error('Error:', error);
          console.error('Response:', xhr.responseText);
          alert('Error al cargar modelos. Revisa la consola.');
        }
      });
    } else {
      $('#modelo_id')
        .empty()
        .append('<option value="">Seleccione una marca primero</option>')
        .prop('disabled', true);
    }
  });
});