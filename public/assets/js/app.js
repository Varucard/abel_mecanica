/**
 * Comportamiento común a todas las pantallas.
 */
(function () {
  'use strict';

  // ---------- Modo oscuro ----------
  const btn = document.getElementById('btnDarkMode');

  function actualizarBoton() {
    if (btn) {
      btn.textContent = document.body.classList.contains('dark-mode') ? '☀ Modo claro' : '🌙 Modo oscuro';
    }
  }

  actualizarBoton();
  btn?.addEventListener('click', () => {
    const oscuro = document.body.classList.toggle('dark-mode');
    try { localStorage.setItem('theme', oscuro ? 'dark' : 'light'); } catch (e) { /* almacenamiento no disponible */ }
    actualizarBoton();
  });

  // ---------- Confirmación de formularios destructivos ----------
  document.addEventListener('submit', (event) => {
    const mensaje = event.target.dataset?.confirm;
    if (mensaje && !window.confirm(mensaje)) {
      event.preventDefault();
    }
  });

  if (!window.jQuery) {
    return;
  }

  const $ = window.jQuery;

  // ---------- CSRF en peticiones AJAX (solo al propio servidor) ----------
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  $.ajaxPrefilter((options, original, xhr) => {
    if (!options.crossDomain && csrf) {
      xhr.setRequestHeader('X-CSRF-Token', csrf);
    }
  });

  // ---------- Alertas: se ocultan solas ----------
  setTimeout(() => $('.js-autohide.alert-success').fadeOut('slow'), 6000);

  // ---------- DataTables ----------
  $('.js-datatable').DataTable({
    language: {
      decimal: ',',
      thousands: '.',
      emptyTable: 'No hay registros cargados',
      info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
      infoEmpty: 'Sin registros',
      infoFiltered: '(filtrado de _MAX_ registros)',
      lengthMenu: 'Mostrar _MENU_ registros',
      loadingRecords: 'Cargando...',
      search: 'Buscar:',
      zeroRecords: 'No se encontraron resultados',
      paginate: { first: 'Primero', last: 'Último', next: 'Siguiente', previous: 'Anterior' },
    },
  });

  // ---------- Select2 ----------
  window.initSelect2 = function ($select) {
    $select.select2({
      language: 'es',
      width: '100%',
      allowClear: !$select.prop('multiple'),
      placeholder: $select.data('placeholder') || '',
    });
  };

  $('.js-select2').each(function () {
    window.initSelect2($(this));
  });

  /**
   * Reemplaza las opciones de un <select> creando nodos de texto (sin inyectar HTML).
   * @param {jQuery} $select
   * @param {Array<{id: number, texto: string}>} opciones
   * @param {string} placeholder
   */
  window.cargarOpciones = function ($select, opciones, placeholder) {
    $select.empty().append(new Option('', ''));
    opciones.forEach((o) => $select.append(new Option(o.texto, o.id)));
    $select.prop('disabled', opciones.length === 0).data('placeholder', placeholder);
    $select.select2('destroy');
    window.initSelect2($select);
    $select.val(null).trigger('change');
  };

  /**
   * Cambio de estado inline (órdenes y turnos): POST por AJAX y recarga.
   */
  window.estadoInline = function (selector, entidad) {
    $(document).on('focus', selector, function () {
      $(this).data('anterior', $(this).val());
    });

    $(document).on('change', selector, function () {
      const $select = $(this);
      const etiqueta = $select.find('option:selected').text();

      if (!window.confirm(`¿Cambiar el estado ${entidad} a "${etiqueta}"?`)) {
        $select.val($select.data('anterior'));
        return;
      }

      $.post($select.data('url'), { estado: $select.val() })
        .done(() => window.location.reload())
        .fail((xhr) => {
          window.alert(xhr.responseJSON?.message || 'No se pudo cambiar el estado.');
          $select.val($select.data('anterior'));
        });
    });
  };
})();
