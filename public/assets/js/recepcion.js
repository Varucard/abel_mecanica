/**
 * "Llegó un auto": busca la patente y muestra solo lo que falta cargar.
 * Ver views/recepcion/form.php (estados del formulario).
 */
$(function () {
  const $form = $('#form_recepcion');
  if (!$form.length) {
    return;
  }

  const $patente = $('#patente');
  const $resultado = $('#resultado_patente');
  const $nuevos = $('#datos_nuevos');
  const normalizar = (texto) => texto.toUpperCase().replace(/[\s.\-]/g, '');
  let buscada = $form.data('estado') === 'sin-buscar' ? null : normalizar($patente.val());

  function alerta(tipo, icono, partes) {
    const $alerta = $('<div>', { class: `alert alert-${tipo} mb-2` }).append($('<i>', { class: `bi bi-${icono} me-1`, 'aria-hidden': 'true' }));
    partes.forEach((parte) => $alerta.append(parte));
    return $alerta;
  }

  // Botón de abajo: "Recibir el auto" o, si ya hay una orden abierta, "Abrir otra orden igual"
  // (secundario). Abrirla se confirma, y recién ahí confirmar_otra lleva el N° de la orden que se
  // vio: el servidor lo compara con las abiertas en ese momento.
  const $recibir = $('.js-recibir');
  let abierta = $recibir.data('abierta') || null;
  function modoOtra(id) {
    abierta = id || null;
    $('#confirmar_otra').val('');
    $recibir.toggleClass('btn-seccion', !abierta).toggleClass('btn-outline-secondary', Boolean(abierta));
    $recibir.find('.js-recibir-texto').text(abierta ? $recibir.data('texto-otra') : $recibir.data('texto-normal'));
  }

  $form.on('submit', (event) => {
    if (!abierta || $('#confirmar_otra').val() === String(abierta)) {
      return;
    }
    event.preventDefault();
    window.confirmar('¿Abrir otra orden para un auto que ya está en el taller? Si viene por lo mismo, seguí con la orden abierta.',
      { aceptar: 'Sí, abrir otra orden', textoCancelar: 'No, volver' }).then((ok) => {
      if (ok) {
        $('#confirmar_otra').val(String(abierta));
        $form[0].requestSubmit();
      }
    });
  });

  function mostrar(estado) {
    const nuevo = estado === 'nuevo';
    $form.attr('data-estado', estado);
    $nuevos.prop('disabled', !nuevo).prop('hidden', !nuevo);
    $('.js-tras-buscar').prop('hidden', estado === 'sin-buscar');
    $('.js-numero-motivo').text(nuevo ? '4' : '2');
  }

  let buscando = null; // patente que se está buscando: el blur y el clic en "Buscar" no la piden dos veces
  function buscar() {
    if (!$patente[0].checkValidity()) {
      $patente[0].reportValidity(); // muestra el error debajo del campo (app.js)
      return;
    }
    const patente = normalizar($patente.val());
    if (buscando === patente) {
      return;
    }
    buscando = patente;

    $.getJSON($form.data('patente-url'), { patente: $patente.val() })
      .always(() => { buscando = null; })
      .done((r) => {
        buscada = r.patente;
        $patente.val(r.patente);
        $resultado.empty();

        if (!r.vehiculo) {
          modoOtra(null);
          $resultado.append(alerta('info', 'info-circle', ['Es la primera vez que viene este auto: completá los datos del dueño y del auto.']));
          mostrar('nuevo');
          $('#dni').trigger('focus');
          return;
        }

        const v = r.vehiculo;
        $resultado.append(alerta('success', 'check-circle-fill', [
          'Ya lo conocemos: ', $('<strong>').text(v.descripcion), ' de ', $('<strong>').text(v.cliente), '. ',
          $('<a>', { href: v.url }).text('Ver ficha'),
        ]));

        if (!v.activo) {
          $resultado.append(alerta('warning', 'exclamation-triangle', ['Este vehículo está dado de baja. Activalo desde su ficha para poder recibirlo.']));
          mostrar('sin-buscar');
          return;
        }

        // Ya está en el taller: lo principal es seguir con esa orden; abrir otra queda como secundario.
        const abiertaVista = r.abiertas.reduce((mayor, o) => (o.id > (mayor?.id ?? 0) ? o : mayor), null);
        if (abiertaVista) {
          $resultado.append(alerta('warning', 'exclamation-triangle', [
            $('<span>', { class: 'me-auto' }).text(` Este auto ya está en el taller: tiene la orden #${abiertaVista.id} abierta.`),
            $('<a>', { href: abiertaVista.url, class: 'btn btn-primary' }).html('<i class="bi bi-arrow-right" aria-hidden="true"></i> ').append(document.createTextNode(`Seguir con la orden #${abiertaVista.id}`)),
          ]).addClass('d-flex flex-wrap gap-2 align-items-center'));
        }
        modoOtra(abiertaVista ? abiertaVista.id : null);

        $('#km_ingreso').attr('placeholder', v.kilometraje !== null ? `La vez anterior: ${v.kilometraje.toLocaleString('es-AR')}` : 'Ej: 125000');
        mostrar('conocido');
        // Si ya está en el taller, el cursor va a "Seguir con la orden", no a cargar una nueva.
        (abiertaVista ? $resultado.find('a.btn-primary') : $('#diagnostico')).trigger('focus');
      })
      .fail(() => window.avisar('No se pudo buscar la patente. Revisá la conexión e intentá de nuevo.'));
  }

  // Borrador recuperado (app.js): si había una patente, se vuelve a buscar para mostrar lo que corresponde.
  $form.on('borrador:restaurado', () => {
    if ($patente.val().trim() !== '' && $form.attr('data-estado') === 'sin-buscar') {
      buscar();
    }
  });

  $('.js-buscar-patente').on('click', buscar);
  $patente.on('keydown', (event) => {
    if (event.key === 'Enter') {
      event.preventDefault(); // Enter busca; no envía el formulario
      buscar();
    }
  });
  // Al salir del campo con una patente completa, se busca sola.
  $patente.on('change', () => {
    if (normalizar($patente.val()) !== buscada && $patente[0].checkValidity()) {
      buscar();
    }
  });
  // Si se corrige la patente, lo de abajo deja de valer hasta volver a buscar.
  $patente.on('input', () => {
    if (normalizar($patente.val()) !== buscada) {
      $resultado.empty();
      modoOtra(null);
      mostrar('sin-buscar');
    }
  });

  // ---------- Dueño: si el DNI ya es de un cliente, no se piden sus datos ----------
  const $dni = $('#dni');
  const $clienteNuevo = $('#cliente_nuevo');
  const $encontrado = $('#cliente_encontrado');
  let dniBuscado = null;

  let demoraDni = null;
  function buscarDni() {
    const dni = $dni.val().replace(/\D/g, '');
    if (dni === dniBuscado) {
      return;
    }
    dniBuscado = dni;
    $encontrado.empty();

    if (!/^\d{6,8}$/.test(dni)) {
      $clienteNuevo.prop('disabled', false).prop('hidden', false);
      return;
    }

    $.getJSON($form.data('cliente-url'), { dni })
      .done((cliente) => {
        // Llegó tarde: mientras tanto se siguió escribiendo (p. ej., la respuesta del DNI de 7
        // dígitos después de completar el de 8). Se ignora para no mostrar a otra persona.
        if (dni !== dniBuscado) {
          return;
        }
        $encontrado.empty();
        const existe = cliente !== null;
        $clienteNuevo.prop('disabled', existe).prop('hidden', existe);
        $encontrado.append(existe
          ? alerta('success', 'check-circle-fill', ['Ya es cliente: ', $('<strong>').text(cliente.nombre),
            cliente.activo ? '' : ' (estaba dado de baja: al recibir el auto se vuelve a activar)']).addClass('w-100 mb-0')
          : $('<span>', { class: 'text-muted' }).text('Cliente nuevo: completá sus datos abajo.'));
      });
  }

  $dni.on('change', buscarDni);
  // Mientras se escribe, busca cuando se deja de tipear un momento (no a cada número).
  $dni.on('input', () => {
    clearTimeout(demoraDni);
    if ($dni.val().replace(/\D/g, '').length >= 7) {
      demoraDni = setTimeout(buscarDni, 400);
    }
  });
});
