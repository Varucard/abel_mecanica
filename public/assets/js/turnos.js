$(document).ready(function() {
  // Inicializar DataTable
  $('#tabla_turnos').DataTable({
    language: { 
      url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' 
    },
    order: [[0, 'desc']], // Ordenar por fecha descendente
    pageLength: 25
  });

  // Cambiar estado del turno
  $('.estado-turno-select').on('change', function() {
  const id = $(this).data('id');
  const nuevo_estado = $(this).val();

  if (confirm('¿Confirmar cambio de estado?')) {
    $.ajax({
      url: 'listar_turno.php',
      method: 'POST',
      data: { 
        cambiar_estado: true, 
        id: id, 
        nuevo_estado: nuevo_estado 
      },
      dataType: 'json',
      success: function(response) {
        if (response.status === 'success') {
          // redirecciona con success=estado
          window.location.href = 'listar_turno.php?success=estado';
        } else {
          window.location.href = 'listar_turno.php?error=estado';
        }
      },
      error: function() {
        window.location.href = 'listar_turno.php?error=estado';
      }
    });
  } else {
    // Si cancela, recargo para restaurar el valor original del select
    location.reload();
  }
});

  // Auto-ocultar alertas existentes
  setTimeout(function() {
    $('#success-alert, #error-alert').fadeOut('slow');
  }, 3000);
});