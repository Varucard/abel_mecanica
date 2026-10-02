/**
 * Órdenes: total estimado en el formulario y cambio de estado en el listado.
 */
$(function () {
  const formato = new Intl.NumberFormat('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  function calcularTotal() {
    let total = 0;
    $('.js-item-precio option:selected').each(function () {
      total += parseFloat($(this).data('precio')) || 0;
    });
    $('#total').val(formato.format(total));
  }

  if ($('#form_orden').length) {
    $('.js-item-precio').on('change', calcularTotal);
    calcularTotal();
  }

  window.estadoInline('.estado-orden-select', 'de la orden');
});
