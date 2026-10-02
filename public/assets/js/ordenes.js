/**
 * Órdenes: detalle con cantidades y precios, total estimado y cambio de estado en el listado.
 */
$(function () {
  const $form = $('#form_orden');

  if ($form.length) {
    const moneda = new Intl.NumberFormat('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const $tbody = $('#detalle_orden tbody');
    // Valores ya cargados (edición o vuelta con errores): { servicio: {id: {cantidad, precio}}, repuesto: {...} }
    const cargados = $form.data('detalle') || { servicio: {}, repuesto: {} };

    const numero = (valor) => parseFloat(String(valor).replace(',', '.')) || 0;

    function input(nombre, valor, extra) {
      return $('<input>', { type: 'number', min: '0', step: '0.01', name: nombre, value: valor, class: 'form-control form-control-sm', required: true, ...extra });
    }

    function filas() {
      // Conserva lo que el usuario ya escribió antes de reconstruir la tabla.
      $tbody.find('tr[data-tipo]').each(function () {
        const $tr = $(this);
        cargados[$tr.data('tipo')][$tr.data('id')] = {
          cantidad: $tr.find('.js-cantidad').val(),
          precio: $tr.find('.js-precio').val(),
        };
      });

      $tbody.find('tr[data-tipo]').remove();

      $('.js-item-precio').each(function () {
        const tipo = $(this).data('tipo');
        $(this).find('option:selected').each(function () {
          const id = $(this).val();
          const previo = cargados[tipo][id] || {};
          const $tr = $('<tr>', { 'data-tipo': tipo, 'data-id': id });

          $tr.append($('<td>').text((tipo === 'repuesto' ? 'Repuesto: ' : '') + $(this).text().replace(/\s*\(\$.*$/s, '').trim()));
          $tr.append($('<td>').append(input(`cantidad_${tipo}[${id}]`, previo.cantidad ?? 1, { class: 'form-control form-control-sm js-cantidad', min: '0.01' })));
          $tr.append($('<td>').append(input(`precio_${tipo}[${id}]`, previo.precio ?? $(this).data('precio'), { class: 'form-control form-control-sm js-precio' })));
          $tr.append($('<td>', { class: 'text-end js-subtotal' }));
          $tbody.append($tr);
        });
      });

      $tbody.find('.js-sin-items').toggle($tbody.find('tr[data-tipo]').length === 0);
      calcularTotal();
    }

    function calcularTotal() {
      let total = 0;
      $tbody.find('tr[data-tipo]').each(function () {
        const subtotal = numero($(this).find('.js-cantidad').val()) * numero($(this).find('.js-precio').val());
        $(this).find('.js-subtotal').text('$ ' + moneda.format(subtotal));
        total += subtotal;
      });
      $('#total').val(moneda.format(total));
    }

    $('.js-item-precio').on('change', filas);
    $tbody.on('input', 'input', calcularTotal);
    filas();
  }

  window.estadoInline('.estado-orden-select', 'de la orden');
});
