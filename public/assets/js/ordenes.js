/**
 * Órdenes y combos: detalle con cantidades (y precios en las órdenes), total,
 * combos, próximo service sugerido y cambio de estado en el listado.
 */
$(function () {
  const $form = $('#form_orden');

  if ($form.length) {
    const conPrecio = $form.data('sin-precio') !== 1;
    const moneda = new Intl.NumberFormat('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const $tbody = $('#detalle_orden tbody');
    // Valores ya cargados (edición o vuelta con errores): { servicio: {id: {cantidad, precio}}, repuesto: {...} }
    const cargados = $form.data('detalle') || { servicio: {}, repuesto: {} };

    // Montos y cantidades se escriben a la argentina ("1.200,50"): ver window.leerImporte (app.js).
    const numero = (valor) => {
      const n = typeof valor === 'number' ? valor : window.leerImporte(valor);
      return Number.isNaN(n) ? 0 : n;
    };

    // Lo que viene del servidor es un número (38000) y se muestra prolijo ("38.000,00");
    // lo que ya escribió el usuario se deja como está.
    function input(nombre, valor, extra, cantidad) {
      const n = typeof valor === 'number' ? valor : window.leerImporte(valor);
      const texto = Number.isNaN(n) ? (valor ?? '') : window.formatoImporte(n, cantidad);
      return $('<input>', {
        type: 'text', inputmode: 'decimal', autocomplete: 'off', name: nombre, value: texto, required: true,
        'data-min': cantidad ? '0.01' : '0', 'data-formato': cantidad ? 'cantidad' : null, ...extra,
      });
    }

    // Quitar un ítem desde su fila: lo saca también del buscador de arriba.
    $tbody.on('click', '.js-quitar-item', function () {
      const $tr = $(this).closest('tr');
      const select = document.querySelector(`.js-item-precio[data-tipo="${$tr.data('tipo')}"]`);
      if (select.tomselect) {
        select.tomselect.removeItem(String($tr.data('id')));
      } else {
        $(select).find(`option[value="${$tr.data('id')}"]`).prop('selected', false);
        $(select).trigger('change');
      }
    });

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
          const $tr = $('<tr>', { 'data-tipo': tipo, 'data-id': id, 'data-precio-catalogo': $(this).data('precio') });

          const nombre = $(this).text().replace(/\s*\(\$.*$/s, '').trim();
          $tr.append($('<td>').text((tipo === 'repuesto' ? 'Repuesto: ' : '') + nombre));
          $tr.append($('<td>').append(input(`cantidad_${tipo}[${id}]`, previo.cantidad ?? 1,
            { class: 'form-control form-control-sm js-importe js-cantidad', 'aria-label': `Cantidad de ${nombre}` }, true)));
          if (conPrecio) {
            $tr.append($('<td>').append(input(`precio_${tipo}[${id}]`, previo.precio ?? $(this).data('precio'),
              { class: 'form-control form-control-sm js-importe js-precio', 'aria-label': `Precio unitario de ${nombre}` }, false)));
          }
          $tr.append($('<td>', { class: 'text-end importe js-subtotal' }));
          $tr.append($('<td>', { class: 'text-end' }).append($('<button>', {
            type: 'button', class: 'btn btn-sm btn-accion btn-outline-danger js-quitar-item', title: `Quitar ${nombre}`, 'aria-label': `Quitar ${nombre}`,
          }).html('<i class="bi bi-x-lg" aria-hidden="true"></i>')));
          $tbody.append($tr);
        });
      });

      $tbody.find('.js-sin-items').toggle($tbody.find('tr[data-tipo]').length === 0);
      calcularTotal();
    }

    function calcularTotal() {
      let total = 0;
      $tbody.find('tr[data-tipo]').each(function () {
        const precio = conPrecio ? numero($(this).find('.js-precio').val()) : numero($(this).data('precio-catalogo'));
        const subtotal = numero($(this).find('.js-cantidad').val()) * precio;
        $(this).find('.js-subtotal').text('$\u00a0' + moneda.format(subtotal));
        total += subtotal;
      });
      $('#total').val(moneda.format(total));
      $('#total_combo').text(moneda.format(total));
    }

    // Combo: suma sus ítems a la orden (sin quitar los ya elegidos) con sus cantidades.
    $('#agregar_combo').on('change', function () {
      const combo = $(this).find('option:selected').data('items');
      if (!combo) {
        return;
      }
      filas(); // guarda lo escrito hasta ahora
      combo.forEach((item) => {
        const select = document.querySelector(`.js-item-precio[data-tipo="${item.tipo}"]`);
        const actuales = $(select).val() || [];
        if (!actuales.includes(String(item.id))) {
          // Sin disparar "change": la tabla se rearma una sola vez, al final.
          select.tomselect
            ? select.tomselect.setValue([...actuales, String(item.id)], true)
            : $(select).val([...actuales, String(item.id)]);
        }
        cargados[item.tipo][item.id] = { ...(cargados[item.tipo][item.id] || {}), cantidad: item.cantidad };
      });
      filas();
      $(this).val('');
    });

    // Próximo service sugerido: km de ingreso + intervalo y hoy + N meses.
    $('#sugerir_service').on('click', function () {
      const km = parseInt($('#km_ingreso').val(), 10);
      if (!isNaN(km)) {
        $('#proximo_service_km').val(km + parseInt($(this).data('km'), 10));
      }
      const fecha = new Date();
      fecha.setMonth(fecha.getMonth() + parseInt($(this).data('meses'), 10));
      $('#proximo_service_fecha').val(fecha.toLocaleDateString('en-CA'));
    });

    $('.js-item-precio').on('change', filas);
    $tbody.on('input', 'input', calcularTotal);
    filas();
  }

  if (window.estadoInline) {
    window.estadoInline('.estado-orden-select', 'de la orden');
  }
});
