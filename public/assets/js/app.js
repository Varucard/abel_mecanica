/**
 * Comportamiento común a todas las pantallas.
 */
(function () {
  'use strict';

  const raiz = document.documentElement;

  // ---------- Modo oscuro (Bootstrap: data-bs-theme en <html>) ----------
  const COLOR_BARRA = { light: '#f8f9fa', dark: '#111827' };

  function actualizarTema() {
    const tema = raiz.dataset.bsTheme === 'dark' ? 'dark' : 'light';
    document.querySelectorAll('.js-tema').forEach((boton) => {
      boton.querySelector('.bi')?.classList.replace(tema === 'dark' ? 'bi-moon-stars' : 'bi-sun', tema === 'dark' ? 'bi-sun' : 'bi-moon-stars');
      boton.querySelector('.js-tema-texto').textContent = tema === 'dark' ? 'Modo claro' : 'Modo oscuro';
    });
    // La barra del celular sigue al tema elegido en la app, no solo al del sistema.
    document.querySelectorAll('meta[name="theme-color"]').forEach((meta) => {
      meta.removeAttribute('media');
      meta.content = COLOR_BARRA[tema];
    });
  }

  actualizarTema();
  document.addEventListener('click', (event) => {
    if (!event.target.closest('.js-tema')) {
      return;
    }
    const tema = raiz.dataset.bsTheme === 'dark' ? 'light' : 'dark';
    raiz.dataset.bsTheme = tema;
    try { localStorage.setItem('theme', tema); } catch (e) { /* almacenamiento no disponible */ }
    actualizarTema();
  });

  // ---------- Tamaño de letra (A− / A+): de -1 a 2 pasos sobre el normal, ver base.css ----------
  document.addEventListener('click', (event) => {
    const boton = event.target.closest('.js-letra');
    if (!boton) {
      return;
    }
    const actual = parseInt(raiz.dataset.letra || '0', 10);
    const nuevo = Math.min(2, Math.max(-1, actual + parseInt(boton.dataset.paso, 10)));
    if (nuevo === 0) {
      delete raiz.dataset.letra;
    } else {
      raiz.dataset.letra = String(nuevo);
    }
    try { nuevo === 0 ? localStorage.removeItem('letra') : localStorage.setItem('letra', String(nuevo)); } catch (e) { /* sin almacenamiento */ }
  });

  // ---------- App instalable: el service worker vive junto al manifiesto ----------
  const manifiesto = document.querySelector('link[rel="manifest"]');
  if ('serviceWorker' in navigator && manifiesto) {
    navigator.serviceWorker.register(new URL('sw.js', manifiesto.href)).catch(() => { /* sin modo sin conexión */ });
  }

  // ---------- Confirmaciones y avisos (modal de Bootstrap en vez de confirm/alert) ----------
  let modal = null;

  function crearModal() {
    const el = document.createElement('div');
    el.className = 'modal fade';
    el.tabIndex = -1;
    el.setAttribute('aria-hidden', 'true');
    el.innerHTML = `
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-body d-flex gap-3 align-items-start">
            <i class="bi fs-3 js-modal-icono" aria-hidden="true"></i>
            <p class="mb-0 pt-1 js-modal-mensaje"></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary js-modal-cancelar" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn js-modal-aceptar">Aceptar</button>
          </div>
        </div>
      </div>`;
    document.body.appendChild(el);
    return el;
  }

  /**
   * Muestra un mensaje en el modal. Con cancelar = false es un aviso de un solo botón.
   * Los dos botones dicen qué hacen ("Sí, cancelar la orden" / "No, volver"): un "Cancelar"
   * suelto, debajo de "¿Cancelar la orden?", no se sabe si confirma o se arrepiente.
   * @returns {Promise<boolean>} true si se aceptó
   */
  function mostrarModal(mensaje, { aceptar = 'Sí, confirmar', cancelar = true, textoCancelar = 'No, volver', peligro = false } = {}) {
    if (!window.bootstrap) {
      return Promise.resolve(cancelar ? window.confirm(mensaje) : (window.alert(mensaje), true));
    }
    modal ??= crearModal();
    modal.querySelector('.js-modal-mensaje').textContent = mensaje;
    modal.querySelector('.js-modal-icono').className = `bi fs-3 js-modal-icono ${peligro ? 'bi-exclamation-triangle text-danger' : (cancelar ? 'bi-question-circle text-primary' : 'bi-info-circle text-primary')}`;
    modal.querySelector('.js-modal-cancelar').hidden = !cancelar;
    modal.querySelector('.js-modal-cancelar').textContent = textoCancelar;
    const boton = modal.querySelector('.js-modal-aceptar');
    boton.textContent = aceptar;
    boton.className = `btn js-modal-aceptar ${peligro ? 'btn-danger' : 'btn-primary'}`;

    return new Promise((resolve) => {
      let aceptado = false;
      const instancia = window.bootstrap.Modal.getOrCreateInstance(modal);
      boton.onclick = () => { aceptado = true; instancia.hide(); };
      modal.addEventListener('hidden.bs.modal', () => resolve(aceptado), { once: true });
      modal.addEventListener('shown.bs.modal', () => boton.focus(), { once: true });
      instancia.show();
    });
  }

  // Lo que borra, anula, cancela o da de baja se muestra en rojo.
  const PELIGRO = /eliminar|anular|cancelar|desactivar|deshacer|no acept/i;

  window.confirmar = (mensaje, opciones = {}) => mostrarModal(mensaje, { peligro: PELIGRO.test(mensaje), ...opciones });
  window.avisar = (mensaje) => mostrarModal(mensaje, { cancelar: false, aceptar: 'Entendido' });

  // Formularios con data-confirm="¿…?": se envían recién al aceptar el modal.
  // data-confirm-aceptar="Sí, eliminar" pone el verbo en el botón de confirmar.
  document.addEventListener('submit', (event) => {
    const form = event.target;
    const mensaje = form.dataset?.confirm;
    if (!mensaje) {
      return;
    }
    if (form.dataset.confirmado === '1') {
      delete form.dataset.confirmado;
      return;
    }
    event.preventDefault();
    const boton = event.submitter;
    window.confirmar(mensaje, { aceptar: form.dataset.confirmAceptar }).then((ok) => {
      if (ok) {
        form.dataset.confirmado = '1';
        boton ? form.requestSubmit(boton) : form.requestSubmit();
      }
    });
  });

  // ---------- Importes y cantidades escritos "a la argentina" ----------
  /**
   * Lee un número como lo escribe la gente acá: punto para los miles y coma para los decimales
   * ("10.000" = diez mil, "1.234,50"). Acepta también "46000.00" (punto decimal, cuando no
   * puede ser de miles). Es la misma regla que Validator::importe() en el servidor.
   * @returns {number} NaN si no es un número válido
   */
  window.leerImporte = function (texto) {
    let t = String(texto ?? '').replace(/[$\s ]/g, '');
    if (t === '') {
      return NaN;
    }
    if (t.includes(',')) {
      if (!/^[1-9]\d{0,2}(\.\d{3})*,\d+$|^\d+,\d+$/.test(t)) {
        return NaN;
      }
      t = t.replace(/\./g, '').replace(',', '.');
    } else if (/^[1-9]\d{0,2}(\.\d{3})+$/.test(t)) {
      t = t.replace(/\./g, '');
    }
    return /^\d+(\.\d+)?$/.test(t) ? parseFloat(t) : NaN;
  };

  const formatoPlata = new Intl.NumberFormat('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  // Las cantidades van sin punto de miles: "1.250" en una cantidad es ambiguo (ver Validator::cantidad).
  const formatoCantidad = new Intl.NumberFormat('es-AR', { maximumFractionDigits: 2, useGrouping: false });
  /** 46000 → "46.000,00" (plata) o "46000" (cantidad, sin decimales de más ni punto de miles). */
  window.formatoImporte = (n, cantidad = false) => (cantidad ? formatoCantidad : formatoPlata).format(n);

  /**
   * Campos .js-importe (componentes/campo con tipo "importe"): al salir se muestran prolijos
   * ("10000" → "10.000,00") y, si no son un número o se pasan de data-min / data-max, quedan
   * marcados con un mensaje claro debajo (ver "Errores de formulario").
   */
  function validarImporte(campo) {
    const valor = window.leerImporte(campo.value);
    const cantidad = campo.dataset.formato === 'cantidad';
    const min = campo.dataset.min !== undefined ? parseFloat(campo.dataset.min) : null;
    const max = campo.dataset.max !== undefined ? parseFloat(campo.dataset.max) : null;
    let error = '';
    if (cantidad && /^\s*\d{1,3}\.\d{3}\s*$/.test(campo.value)) {
      const entero = campo.value.trim().replace('.', '');
      const decimal = campo.value.trim().replace('.', ',').replace(/,?0+$/, '');
      error = `¿Son ${entero} o ${decimal}? Escribilo sin punto (${entero}) o con coma (${decimal}).`;
    } else if (campo.value.trim() !== '' && Number.isNaN(valor)) {
      error = cantidad ? 'Escribí la cantidad con números (por ejemplo 2 o 1,5).' : 'Escribí el importe con números, por ejemplo 15.000 o 15.000,50.';
    } else if (!Number.isNaN(valor) && min !== null && valor < min) {
      error = campo.dataset.errorMin || `Tiene que ser al menos ${window.formatoImporte(min, cantidad)}.`;
    } else if (!Number.isNaN(valor) && max !== null && valor > max) {
      error = campo.dataset.errorMax || `No puede pasar de ${cantidad ? '' : '$ '}${window.formatoImporte(max, cantidad)}.`;
    }
    campo.setCustomValidity(error);
    return valor;
  }

  document.addEventListener('focusout', (event) => {
    const campo = event.target.closest?.('.js-importe');
    if (!campo) {
      return;
    }
    const valor = validarImporte(campo);
    if (!Number.isNaN(valor) && campo.validity.valid) {
      campo.value = window.formatoImporte(valor, campo.dataset.formato === 'cantidad');
    }
  });
  document.addEventListener('input', (event) => {
    if (event.target.classList?.contains('js-importe')) {
      validarImporte(event.target);
    }
  }, true);
  document.querySelectorAll('.js-importe').forEach(validarImporte);

  // ---------- Teléfonos (input[data-telefono]): la misma regla que ClienteService::normalizarTelefono ----------
  function normalizarTelefono(texto) {
    let d = String(texto).replace(/\D/g, '');
    if (d.length > 10 && d.startsWith('54')) {
      d = d.slice(2);
      if (d.length > 10 && d.startsWith('9')) {
        d = d.slice(1);
      }
    }
    if (d.length > 10 && d.startsWith('0')) {
      d = d.slice(1);
    }
    if (d.length === 12) {
      for (const area of [2, 3, 4]) {
        if (d.slice(area, area + 2) === '15') {
          return d.slice(0, area) + d.slice(area + 2);
        }
      }
    }
    return d;
  }

  function validarTelefono(campo) {
    const valor = campo.value.trim();
    // Misma regla que ClienteService::telefonoValido: 10 números y un código de área que exista.
    const normalizado = normalizarTelefono(valor);
    let error = '';
    if (valor !== '' && normalizado.startsWith('15')) {
      error = 'Falta el código de área: si es de Buenos Aires, agregale 11 adelante (11 15 2345-6789).';
    } else if (valor !== '' && !/^(11\d{8}|[23]\d{9})$/.test(normalizado)) {
      error = 'Falta el código de área o sobran números: tiene que quedar como 11 2345-6789 (10 números, sin contar 0 ni 15).';
    }
    campo.setCustomValidity(error);
  }
  document.addEventListener('input', (event) => {
    if (event.target.matches?.('input[data-telefono]')) {
      validarTelefono(event.target);
    }
  }, true);
  document.querySelectorAll('input[data-telefono]').forEach(validarTelefono);

  // ---------- Errores de formulario: debajo de cada campo, en vez del globito del navegador ----------
  /**
   * El navegador valida antes de enviar (required, pattern, min…). Su aviso es un globito
   * que desaparece solo y no siempre se entiende; acá el campo queda marcado en rojo con el
   * mensaje abajo hasta que se corrige. El texto sale de data-error si el campo lo trae.
   */
  function mensajeDeError(campo) {
    const v = campo.validity;
    if (v.valueMissing) {
      return campo.tagName === 'SELECT' ? 'Elegí una opción.' : 'Este dato es obligatorio.';
    }
    if (v.customError) {
      return campo.validationMessage; // ya viene en castellano (p. ej., los importes)
    }
    if (campo.dataset.error) {
      return campo.dataset.error;
    }
    // Textos propios en vez de los del navegador (que dependen del idioma de la PC).
    if (v.rangeUnderflow) {
      return `Tiene que ser ${campo.min} o más.`;
    }
    if (v.rangeOverflow) {
      return `Tiene que ser ${campo.max} o menos.`;
    }
    if (v.typeMismatch && campo.type === 'email') {
      return 'Revisá el email: tiene que ser como nombre@correo.com.';
    }
    if (v.tooShort) {
      return `Escribí al menos ${campo.minLength} caracteres.`;
    }
    if (v.badInput || v.stepMismatch) {
      return 'Escribí solo números.';
    }
    if (v.patternMismatch) {
      return 'El formato no es válido.';
    }
    return campo.validationMessage;
  }

  function lugarDelError(campo) {
    // Tom Select esconde el <select>: el mensaje va después de su caja. Con prefijo ($), después del grupo.
    return campo.tomselect?.wrapper || campo.closest('.input-group') || campo;
  }

  function limpiarError(campo) {
    campo.classList.remove('is-invalid');
    campo.tomselect?.wrapper.classList.remove('is-invalid');
    const lugar = lugarDelError(campo);
    if (lugar.nextElementSibling?.classList.contains('js-error-campo')) {
      lugar.nextElementSibling.remove();
    }
  }

  let primerInvalido = null;
  document.addEventListener('invalid', (event) => {
    const campo = event.target;
    event.preventDefault();
    // Un campo con error dentro de una sección plegada (<details>) tiene que quedar a la vista.
    campo.closest('details:not([open])')?.setAttribute('open', '');
    limpiarError(campo);
    campo.classList.add('is-invalid');
    campo.tomselect?.wrapper.classList.add('is-invalid');
    const error = document.createElement('div');
    error.className = 'invalid-feedback d-block js-error-campo';
    error.textContent = mensajeDeError(campo);
    lugarDelError(campo).after(error);

    // Al primero con error se lo lleva a la vista (el navegador ya no lo hace al cancelar el globito).
    if (!primerInvalido) {
      primerInvalido = campo;
      setTimeout(() => {
        (primerInvalido.tomselect?.control_input || primerInvalido).focus({ preventScroll: true });
        lugarDelError(primerInvalido).scrollIntoView({ block: 'center', behavior: 'smooth' });
        primerInvalido = null;
      });
    }
  }, true);

  ['input', 'change'].forEach((tipo) => document.addEventListener(tipo, (event) => {
    if (event.target.classList?.contains('is-invalid') && event.target.checkValidity()) {
      limpiarError(event.target);
    }
  }, true));

  // Los errores del servidor (arriba de la página) se ven aunque se haya bajado en el formulario.
  document.querySelector('.alert-danger')?.scrollIntoView({ block: 'nearest' });

  // ---------- Links a otra parte de la página que además ponen el cursor en un campo ----------
  // <a href="#seccion" data-enfocar="#campo">: p. ej., "Cobrar" baja al formulario de pago.
  document.addEventListener('click', (event) => {
    const link = event.target.closest('a[data-enfocar]');
    const campo = link && document.querySelector(link.dataset.enfocar);
    if (campo) {
      event.preventDefault();
      campo.scrollIntoView({ block: 'center', behavior: 'smooth' });
      campo.focus({ preventScroll: true });
    }
  });

  // ---------- Filas que se tocan enteras (<tr class="fila-link" data-href="…">) ----------
  document.addEventListener('click', (event) => {
    const fila = event.target.closest('tr.fila-link[data-href]');
    if (fila && !event.target.closest('a, button, input, select, label, form')) {
      window.location.href = fila.dataset.href;
    }
  });

  // Al llegar con un #ancla que pide un campo (data-enfocar en el destino), el cursor queda ahí:
  // p. ej., "Cobrar" desde Deudores abre la orden con el monto listo para escribir.
  const destino = window.location.hash ? document.getElementById(window.location.hash.slice(1)) : null;
  if (destino && destino.dataset.enfocar) {
    // Después de la carga: el salto al #ancla del navegador, si no, le saca el foco al campo.
    window.addEventListener('load', () => setTimeout(() => {
      const campo = document.querySelector(destino.dataset.enfocar);
      campo?.focus({ preventScroll: true });
      campo?.scrollIntoView({ block: 'center' });
    }, 50));
  }

  // ---------- Buscador con sugerencias mientras se escribe ----------
  /**
   * Patrón combobox (WAI-ARIA): debajo del buscador aparecen las primeras coincidencias.
   * Flechas para moverse, Enter para abrir la elegida (sin elegir, Enter busca como siempre),
   * Escape para cerrar. Las respuestas que llegan tarde (se siguió escribiendo) se descartan.
   */
  const buscador = document.getElementById('busqueda_rapida');
  const lista = document.getElementById('busqueda_sugerencias');
  if (buscador && lista && buscador.dataset.sugerenciasUrl) {
    let pedido = 0;
    let demora = null;
    let activa = -1;

    const opciones = () => [...lista.querySelectorAll('[role="option"]')];
    const cerrar = () => {
      lista.hidden = true;
      buscador.setAttribute('aria-expanded', 'false');
      buscador.removeAttribute('aria-activedescendant');
      activa = -1;
    };
    const marcar = (indice) => {
      const todas = opciones();
      activa = (indice + todas.length) % todas.length;
      todas.forEach((o, i) => { o.classList.toggle('active', i === activa); o.setAttribute('aria-selected', i === activa ? 'true' : 'false'); });
      buscador.setAttribute('aria-activedescendant', todas[activa].id);
      todas[activa].scrollIntoView({ block: 'nearest' });
    };

    const mostrar = (sugerencias) => {
      lista.replaceChildren();
      sugerencias.forEach((s, i) => {
        const item = document.createElement('a');
        item.href = s.url;
        item.id = `sugerencia_${i}`;
        item.className = 'list-group-item list-group-item-action d-flex justify-content-between gap-2';
        item.setAttribute('role', 'option');
        item.setAttribute('aria-selected', 'false');
        const texto = document.createElement('span');
        const titulo = document.createElement('strong');
        titulo.textContent = s.texto;
        const detalle = document.createElement('span');
        detalle.className = 'small text-muted d-block';
        detalle.textContent = s.detalle;
        texto.append(titulo, detalle);
        const tipo = document.createElement('span');
        tipo.className = 'badge text-bg-light align-self-center';
        tipo.textContent = s.tipo;
        item.append(texto, tipo);
        lista.append(item);
      });
      lista.hidden = sugerencias.length === 0;
      buscador.setAttribute('aria-expanded', sugerencias.length ? 'true' : 'false');
      activa = -1;
    };

    buscador.addEventListener('input', () => {
      clearTimeout(demora);
      const texto = buscador.value.trim();
      if (texto.length < 2 && !/^\d$/.test(texto)) {
        cerrar();
        return;
      }
      demora = setTimeout(() => {
        const numero = ++pedido;
        fetch(`${buscador.dataset.sugerenciasUrl}?q=${encodeURIComponent(texto)}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
          .then((r) => (r.ok ? r.json() : []))
          .then((sugerencias) => { if (numero === pedido) mostrar(sugerencias); })
          .catch(() => {});
      }, 200);
    });

    buscador.addEventListener('keydown', (event) => {
      if (lista.hidden) {
        return;
      }
      if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault();
        marcar(activa + (event.key === 'ArrowDown' ? 1 : -1));
      } else if (event.key === 'Enter' && activa >= 0) {
        event.preventDefault();
        window.location.href = opciones()[activa].href;
      } else if (event.key === 'Escape') {
        cerrar();
      }
    });

    document.addEventListener('click', (event) => {
      if (!event.target.closest('.buscador')) {
        cerrar();
      }
    });
  }

  // ---------- Borradores: lo que se está cargando no se pierde ----------
  /**
   * Formularios con data-borrador: lo escrito se guarda en este navegador mientras se carga. Si
   * se cierra la pestaña o se corta la luz, al volver se recupera (con aviso y "Descartar").
   * Se borra al enviar el formulario, al salir del sistema o a las 8 h: son datos de clientes
   * y no deben quedar en la PC. Si el formulario volvió con errores, manda lo del servidor.
   */
  const PREFIJO_BORRADOR = 'borrador:';
  const VIDA_BORRADOR = 8 * 60 * 60 * 1000;
  const NO_GUARDAR = ['_token', 'confirmar_otra', 'deshaciendo'];
  const almacen = (() => { try { return window.localStorage; } catch (e) { return null; } })();

  function borrarBorradores() {
    if (!almacen) {
      return;
    }
    Object.keys(almacen).filter((k) => k.startsWith(PREFIJO_BORRADOR)).forEach((k) => almacen.removeItem(k));
  }

  // Al salir del sistema no queda ningún borrador.
  document.addEventListener('submit', (event) => {
    if (event.target.action?.endsWith('/logout')) {
      borrarBorradores();
    }
  }, true);

  function restaurarCampos(form, datos) {
    const pendientes = [];
    const porNombre = {};
    datos.forEach(([nombre, valor]) => { (porNombre[nombre] ??= []).push(valor); });
    Object.entries(porNombre).forEach(([nombre, valores]) => {
      const campos = form.querySelectorAll(`[name="${CSS.escape(nombre)}"]`);
      if (campos.length === 0) {
        pendientes.push([nombre, valores]); // todavía no existe (se arma después, p. ej. los ítems de la orden)
        return;
      }
      const campo = campos[0];
      if (campo.tagName === 'SELECT') {
        const existe = valores.every((v) => v === '' || [...campo.options].some((o) => o.value === v) || campo.dataset.crear);
        if (!existe) {
          pendientes.push([nombre, valores]); // las opciones llegan después (p. ej., los modelos de la marca)
          return;
        }
        if (campo.tomselect) {
          valores.forEach((v) => { if (v && !campo.tomselect.options[v]) campo.tomselect.addOption({ value: v, text: v.replace(/^nuevo:/, '') }); });
          campo.tomselect.setValue(campo.multiple ? valores : valores[0]);
        } else {
          campo.value = valores[0];
          campo.dispatchEvent(new Event('change', { bubbles: true }));
        }
      } else if (campo.type === 'checkbox' || campo.type === 'radio') {
        campos.forEach((c) => { c.checked = valores.includes(c.value); });
      } else if (campo.type !== 'hidden' || campo.value === '') {
        campo.value = valores[0];
        campo.dispatchEvent(new Event('input', { bubbles: true }));
      }
    });
    return pendientes;
  }

  document.querySelectorAll('form[data-borrador]').forEach((form) => {
    if (!almacen) {
      return;
    }
    // Con la query: "turnos/crear?cliente_id=7" (volviendo de cargar un cliente) no recupera el borrador de un turno en blanco.
    const clave = PREFIJO_BORRADOR + window.location.pathname + window.location.search + '#' + (form.id || form.getAttribute('action'));
    const guardar = () => {
      const datos = [...new FormData(form).entries()]
        .filter(([nombre, valor]) => !NO_GUARDAR.includes(nombre) && typeof valor === 'string');
      try { almacen.setItem(clave, JSON.stringify({ t: Date.now(), datos })); } catch (e) { /* lleno o bloqueado */ }
    };
    let demora = null;
    const guardarEnUnRato = () => { clearTimeout(demora); demora = setTimeout(guardar, 500); };

    let guardado = null;
    try { guardado = JSON.parse(almacen.getItem(clave) || 'null'); } catch (e) { guardado = null; }
    const vigente = guardado && Date.now() - guardado.t < VIDA_BORRADOR && guardado.datos.some(([n, v]) => v !== '' && !n.startsWith('_'));

    // Se recupera con la página ya cargada (los scripts de cada pantalla escuchando) y se
    // reintenta lo que depende de otra cosa: los modelos llegan después de elegir la marca, los
    // ítems de la orden se arman después de elegir los servicios.
    const faltantes = (datos) => datos.filter(([nombre, valor]) => {
      const campo = form.querySelector(`[name="${CSS.escape(nombre)}"]`);
      if (!campo) {
        return true;
      }
      if (campo.tagName === 'SELECT') {
        return campo.multiple ? ![...campo.selectedOptions].some((o) => o.value === valor) : campo.value !== valor;
      }
      return false;
    });
    if (vigente && document.body.dataset.volvioConErrores !== '1') {
      window.addEventListener('load', () => {
        restaurarCampos(form, guardado.datos);
        [700, 1600].forEach((espera) => setTimeout(() => {
          const pendientes = faltantes(guardado.datos);
          if (pendientes.length) {
            restaurarCampos(form, pendientes);
          }
        }, espera));
        form.dispatchEvent(new CustomEvent('borrador:restaurado'));
      });
      const aviso = document.createElement('div');
      aviso.className = 'alert alert-info d-flex flex-wrap gap-2 align-items-center';
      aviso.setAttribute('role', 'status');
      aviso.innerHTML = '<i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i><span class="me-auto">Recuperamos lo que estabas cargando.</span>';
      const descartar = document.createElement('button');
      descartar.type = 'button';
      descartar.className = 'btn btn-sm btn-outline-secondary';
      descartar.textContent = 'Descartar y empezar de cero';
      descartar.addEventListener('click', () => { almacen.removeItem(clave); window.location.reload(); });
      aviso.append(descartar);
      form.prepend(aviso);
    } else if (guardado && !vigente) {
      almacen.removeItem(clave);
    }

    form.addEventListener('input', guardarEnUnRato);
    form.addEventListener('change', guardarEnUnRato);
    form.addEventListener('submit', () => { clearTimeout(demora); almacen.removeItem(clave); });
  });

  // ---------- Atajos de teclado (para el mostrador) ----------
  /**
   * "/" busca; las letras de data-atajos (en <body>) llevan a cada pantalla; "?" muestra la
   * lista. Solo cuando no se está escribiendo en un campo y sin Ctrl/Alt/Cmd, para no pisar
   * nada del navegador.
   */
  const atajos = (() => { try { return JSON.parse(document.body.dataset.atajos || '{}'); } catch (e) { return {}; } })();
  const escribiendo = (el) => el && (el.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(el.tagName));

  function mostrarAtajos() {
    const filas = [['/', 'Buscar (patente, DNI, apellido u orden)'], ...Object.entries(atajos).map(([tecla, [, texto]]) => [tecla.toUpperCase(), texto]), ['?', 'Ver esta lista']];
    window.avisar('Atajos de teclado:\n' + filas.map(([tecla, texto]) => `${tecla}  →  ${texto}`).join('\n'));
  }

  document.addEventListener('click', (event) => {
    if (event.target.closest('.js-ver-atajos')) {
      mostrarAtajos();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.ctrlKey || event.altKey || event.metaKey || escribiendo(document.activeElement) || document.querySelector('.modal.show')) {
      return;
    }
    if (event.key === '/') {
      const buscador = document.getElementById('busqueda_rapida');
      if (buscador) {
        event.preventDefault();
        buscador.focus();
      }
    } else if (event.key === '?') {
      event.preventDefault();
      mostrarAtajos();
    } else if (atajos[event.key.toLowerCase()] && !event.shiftKey) {
      event.preventDefault();
      window.location.href = atajos[event.key.toLowerCase()][0];
    }
  });

  // ---------- Pestañas: la activa a la vista (en el celular la tira se desliza) ----------
  document.querySelectorAll('.nav-tabs .nav-link.active').forEach((pestana) => {
    pestana.scrollIntoView({ block: 'nearest', inline: 'center' });
  });

  // ---------- Avisos flotantes: se van solos ----------
  if (window.bootstrap) {
    document.querySelectorAll('.js-aviso').forEach((aviso) => {
      // 10 segundos (los mensajes con un "qué sigue" no se alcanzaban a leer en 6); mientras el
      // mouse está encima o tiene el foco, Bootstrap no lo cierra.
      window.bootstrap.Toast.getOrCreateInstance(aviso, { delay: parseInt(aviso.dataset.demora || '10000', 10) }).show();
    });
  }

  // ---------- Desplegables con buscador (Tom Select) ----------
  /**
   * Convierte un <select class="js-buscable"> en un desplegable con buscador.
   * El <select> original sigue siendo la fuente de verdad: sus opciones y su "change"
   * funcionan igual que antes, así que los scripts de cada pantalla no cambian.
   */
  window.initBuscable = function (select) {
    if (!window.TomSelect) {
      return;
    }
    select.tomselect?.destroy();
    // data-crear="prefijo:": si lo que se busca no está, se puede agregar escribiéndolo. El
    // valor que viaja es "prefijo:texto" y el servidor lo da de alta (p. ej., un modelo nuevo).
    const crear = select.dataset.crear;
    const escapar = (texto) => texto.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
    new window.TomSelect(select, {
      plugins: select.multiple ? ['remove_button'] : ['clear_button'],
      placeholder: select.dataset.placeholder || '',
      maxOptions: null,
      create: crear ? (texto) => ({ value: crear + texto.trim(), text: texto.trim() }) : false,
      render: {
        // data-chip-corto: lo elegido muestra solo el nombre ("Filtro de aceite"), sin el precio
        // ni el stock que sí ayudan al buscar: así los chips no tapan el lugar para escribir.
        ...(select.dataset.chipCorto !== undefined ? {
          item: (data, escape) => `<div>${escape(String(data.text).replace(/\s*\(\$.*$/s, '').trim())}</div>`,
        } : {}),
        no_results: () => '<div class="no-results">Sin resultados</div>',
        option_create: (data) => `<div class="create"><i class="bi bi-plus-circle" aria-hidden="true"></i> Agregar <strong>${escapar(data.input)}</strong></div>`,
      },
    });
  };

  document.querySelectorAll('select.js-buscable').forEach((select) => window.initBuscable(select));

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

  // ---------- DataTables ----------
  const idioma = {
    decimal: ',',
    thousands: '.',
    emptyTable: 'No hay registros cargados',
    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
    infoEmpty: 'Sin registros',
    infoFiltered: '(filtrado de _MAX_ registros)',
    lengthMenu: '_MENU_ por página',
    loadingRecords: 'Cargando...',
    search: 'Filtrar esta lista:',
    zeroRecords: 'No se encontraron resultados',
    aria: {
      orderable: 'Ordenar por esta columna',
      orderableReverse: 'Ordenar al revés',
      orderableRemove: 'Quitar el orden',
    },
    paginate: { first: 'Primero', last: 'Último', next: 'Siguiente', previous: 'Anterior' },
    processing: '<div class="spinner-border spinner-border-sm text-secondary me-2" role="status"></div>Cargando…',
  };

  // En pantallas chicas, las columnas que no entran se pliegan en un detalle por fila.
  const comunes = { responsive: true, pagingType: 'simple_numbers', autoWidth: false };

  /**
   * Opciones de cada tabla. data-vacio="…" en la <table> reemplaza el "No hay registros
   * cargados" por un estado vacío propio (texto plano: se escapa acá). Con data-vacio-accion
   * (url) y data-vacio-boton (texto), además muestra el botón para cargar el primero.
   */
  function opciones(tabla) {
    const vacio = tabla.dataset.vacio;
    const escapar = (texto) => $('<div>').text(texto).html();
    const boton = tabla.dataset.vacioAccion
      ? `<div class="mt-2"><a href="${escapar(tabla.dataset.vacioAccion)}" class="btn btn-sm btn-seccion"><i class="bi bi-plus-lg" aria-hidden="true"></i> ${escapar(tabla.dataset.vacioBoton || 'Cargar')}</a></div>`
      : '';
    const texto = vacio ? `<div class="vacio py-3"><i class="bi bi-inbox" aria-hidden="true"></i>${escapar(vacio)}${boton}</div>` : idioma.emptyTable;
    // Qué columnas se pliegan último en pantallas chicas. Con data-prioridad="n" en el <th>
    // (1 = siempre visible) cada tabla dice cuáles importan: el saldo, el estado, el stock…
    // Sin eso, quedan la primera columna y la de acciones.
    const ths = [...tabla.querySelectorAll('thead th')];
    let prioridades = ths.flatMap((th, i) => (th.dataset.prioridad ? [{ targets: i, responsivePriority: parseInt(th.dataset.prioridad, 10) }] : []));
    if (prioridades.length === 0) {
      const acciones = ths.map((th) => th.textContent.trim().toLowerCase()).indexOf('acciones');
      prioridades = [{ targets: 0, responsivePriority: 1 }];
      if (acciones > 0) {
        prioridades.push({ targets: acciones, responsivePriority: 2 });
      }
    }

    return { ...comunes, columnDefs: prioridades, language: { ...idioma, emptyTable: texto } };
  }

  // Tablas con todos los datos en la página.
  $('.js-datatable').each(function () {
    $(this).addClass('w-100').DataTable(opciones(this));
  });

  // Tablas paginadas en el servidor: data-server="url" y, opcional,
  // data-filtros="#form" con campos que se envían junto a cada consulta.
  $('table[data-server]').each(function () {
    const $tabla = $(this).addClass('w-100');
    const $filtros = $($tabla.data('filtros') || []);

    const tabla = $tabla.DataTable({
      ...opciones(this),
      serverSide: true,
      processing: true,
      searchDelay: 400,
      pageLength: 25,
      ajax: {
        url: $tabla.data('server'),
        data: (d) => {
          $filtros.serializeArray().forEach((campo) => { d[campo.name] = campo.value; });
        },
      },
    });

    $filtros.on('change submit', (e) => {
      e.preventDefault();
      tabla.ajax.reload();
    });
  });

  /**
   * Reemplaza las opciones de un <select> creando nodos de texto (sin inyectar HTML).
   * @param {jQuery} $select
   * @param {Array<{id: number, texto: string}>} opciones
   * @param {string} placeholder
   */
  window.cargarOpciones = function ($select, opciones, placeholder) {
    const select = $select[0];
    select.tomselect?.destroy();
    $select.empty().append(new Option('', ''));
    opciones.forEach((o) => $select.append(new Option(o.texto, o.id)));
    // Si se pueden agregar opciones escribiéndolas (data-crear), una lista vacía no bloquea el campo.
    select.disabled = opciones.length === 0 && !select.dataset.crear;
    select.dataset.placeholder = placeholder;
    $select.val('');
    window.initBuscable(select);
    $select.trigger('change');
  };

  /**
   * Cambio de estado inline (órdenes y turnos): POST por AJAX y recarga.
   */
  window.estadoInline = function (selector, entidad) {
    $(document).on('change', selector, function () {
      const $select = $(this);
      // El estado de antes viene en data-actual: si se cancela o el servidor lo rechaza, vuelve a ese.
      const volver = () => $select.val($select.data('actual'));
      const etiqueta = $select.find('option:selected').text();

      // Lo que ya se sabe que va a fallar se avisa antes de preguntar.
      if ($select.val() === 'finalizado' && $select.data('sin-items')) {
        volver();
        window.avisar('Para terminar el trabajo, primero cargá en la orden los servicios o repuestos que se hicieron.');
        return;
      }

      const aviso = $select.val() === 'finalizado' ? ' Se descuentan del stock los repuestos usados y, si está activado, se le avisa al cliente que el vehículo está listo.' : '';
      const cancelar = $select.val() === 'cancelado';
      window.confirmar(`¿Cambiar el estado ${entidad} a "${etiqueta}"?${aviso}`, {
        peligro: cancelar,
        aceptar: cancelar ? 'Sí, cancelar' : `Sí, pasar a "${etiqueta}"`,
        textoCancelar: 'No, dejarlo como está',
      }).then((ok) => {
        if (!ok) {
          volver();
          return;
        }

        $.post($select.data('url'), { estado: $select.val() })
          .done(() => window.location.reload())
          .fail((xhr) => {
            volver();
            window.avisar(xhr.responseJSON?.message || 'No se pudo cambiar el estado.');
          });
      });
    });
  };
})();
