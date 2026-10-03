<?php

/**
 * Valores por defecto de la configuración del sistema.
 *
 * Lo que se edita desde "Configuración > Sistema" se guarda en
 * storage/config/taller.json y tiene prioridad sobre estos valores.
 *
 * Variables disponibles en las plantillas de mensajes:
 *   Generales: {cliente} {vehiculo} {patente} {taller} {direccion} {telefono} {link_seguimiento}
 *   Turnos:    {fecha} {hora} {link_turno}
 *   Órdenes:   {numero} {total} {link_presupuesto} {km_proximo} {fecha_proximo}
 */
return [
  'taller' => [
    'nombre' => 'Mecánica Abel',
    'cuit' => '20-40137152-5',
    'direccion' => 'Saenz Peña 296 (ex 1702)',
    'telefono' => '11-3635-9867',
    'whatsapp' => '+5491136359867',
    'email' => 'abel.amarquez@gmail.com',
  ],

  'trabajo' => [
    'validez' => 10,
    'garantia' => 10,
    'tiempo_estimado' => 3,
    'forma_pago' => [
      'Contado',
      'Tarjeta de Crédito',
      'Tarjeta de Débito',
      'Transferencia Bancaria',
      'Mercado Pago',
    ],
    'observaciones' => [
      'Tiempo estimado de reparación: 2-4 días hábiles desde la aceptación y recepción de repuestos.',
      'Los precios están sujetos a modificación si surgen imprevistos o variaciones en repuestos.',
    ],
    'mensaje_legal' => 'Este documento no es una factura y no posee validez fiscal.',
    // Si el cliente acepta el presupuesto desde el link, la orden pasa a "En proceso".
    'aceptar_inicia_trabajo' => true,
  ],

  'turnos' => [
    // Horario de atención por día (1 = lunes … 7 = domingo). null = cerrado.
    'horario' => [
      '1' => ['desde' => '08:00', 'hasta' => '18:00'],
      '2' => ['desde' => '08:00', 'hasta' => '18:00'],
      '3' => ['desde' => '08:00', 'hasta' => '18:00'],
      '4' => ['desde' => '08:00', 'hasta' => '18:00'],
      '5' => ['desde' => '08:00', 'hasta' => '18:00'],
      '6' => ['desde' => '08:00', 'hasta' => '13:00'],
      '7' => null,
    ],
    // Rechazar turnos fuera del horario de atención o en feriados.
    'validar_horario' => true,
    // Cuántos turnos se aceptan en el mismo día y hora (p. ej. cantidad de elevadores).
    'cupos_por_horario' => 1,
    // Duración de cada franja en la agenda semanal (minutos).
    'intervalo_minutos' => 60,
    // Fechas no laborables (AAAA-MM-DD). Se pueden importar los feriados nacionales.
    'feriados' => [],
    // Enviar email para que el cliente confirme o cancele el turno al agendarlo.
    'enviar_confirmacion' => true,
    // Recordatorio automático el día hábil anterior al turno, desde esta hora.
    'recordatorio_automatico' => true,
    'recordatorio_hora' => '10:00',
  ],

  'notificaciones' => [
    // Canales a usar, en orden de preferencia. Disponibles: email (whatsapp: próximamente).
    'canales' => ['email'],
    // Mostrar el botón manual "WhatsApp" (abre wa.me con el mensaje armado).
    'boton_whatsapp_manual' => true,
    // Código de país para los links de WhatsApp (Argentina: 54).
    'codigo_pais' => '54',
    // Avisar por email al taller cuando un cliente acepta o rechaza un presupuesto.
    'avisar_taller' => true,
  ],

  'mensajes' => [
    'whatsapp_confirmacion' => 'Hola {cliente}, agendamos tu turno en {taller} el {fecha} a las {hora} hs para tu {vehiculo}. '
      . 'Confirmalo o cancelalo desde acá: {link_turno}',
    'whatsapp_recordatorio' => 'Hola {cliente}, te recordamos tu turno en {taller} el {fecha} a las {hora} hs para tu {vehiculo}. '
      . 'Dirección: {direccion}. Si no podés asistir, avisanos al {telefono}. ¡Gracias!',
    'email_confirmacion_asunto' => 'Confirmá tu turno en {taller} - {fecha} {hora} hs',
    'email_confirmacion' => "Hola {cliente}:\n\nAgendamos tu turno en {taller} para el {fecha} a las {hora} hs (vehículo {vehiculo}).\n\n"
      . "Confirmá tu asistencia o cancelá el turno desde este link:\n{link_turno}\n\n"
      . "Dirección: {direccion}\nTeléfono: {telefono}\n\n¡Gracias!",
    'email_recordatorio_asunto' => 'Recordatorio: tu turno en {taller} es el {fecha} a las {hora} hs',
    'email_recordatorio' => "Hola {cliente}:\n\nTe recordamos tu turno en {taller} el {fecha} a las {hora} hs para tu {vehiculo}.\n\n"
      . "Si todavía no lo confirmaste, o no podés asistir, entrá acá:\n{link_turno}\n\n"
      . "Dirección: {direccion}\nTeléfono: {telefono}\n\n¡Te esperamos!",
    'email_presupuesto_asunto' => 'Presupuesto N° {numero} de {taller} - {vehiculo}',
    'email_presupuesto' => "Hola {cliente}:\n\nTe enviamos el presupuesto N° {numero} para tu {vehiculo} por un total de $ {total}. "
      . "Lo tenés adjunto en PDF.\n\nPodés aceptarlo o rechazarlo desde este link:\n{link_presupuesto}\n\n"
      . "Ante cualquier consulta, escribinos o llamanos al {telefono}.\n\n¡Gracias!",
    'email_service_asunto' => 'Se acerca el service de tu {vehiculo}',
    'email_service' => "Hola {cliente}:\n\nTe recordamos que el próximo service de tu {vehiculo} está previsto para el {fecha_proximo}"
      . " o a los {km_proximo} km, lo que ocurra primero.\n\nPedí tu turno llamando al {telefono} o respondiendo este email.\n\n"
      . "{taller}\n{direccion}",
    'whatsapp_presupuesto' => 'Hola {cliente}, te enviamos el presupuesto N° {numero} por $ {total}. Podés aceptarlo desde acá: {link_presupuesto}',
    'whatsapp_service' => 'Hola {cliente}, se acerca el service de tu {vehiculo} ({fecha_proximo} o {km_proximo} km). ¡Pedí tu turno!',
  ],

  'service' => [
    // Intervalos sugeridos al cargar el próximo service de una orden.
    'intervalo_km' => 10000,
    'intervalo_meses' => 6,
    // Aviso automático al cliente antes de la fecha del próximo service.
    'aviso_automatico' => true,
    'aviso_dias_antes' => 7,
  ],

  'stock' => [
    // Permitir finalizar órdenes aunque el stock de un repuesto quede negativo.
    'permitir_negativo' => true,
    // Margen sobre el costo para sugerir el precio de venta de los repuestos (%).
    'margen_sugerido' => 40,
  ],

  'portal' => [
    // Página pública "Seguí tu vehículo" (/seguimiento).
    'habilitado' => true,
    // Además del DNI, pedir la patente de uno de sus vehículos (recomendado).
    'requiere_patente' => true,
    'mostrar_montos' => true,
    'cantidad_ordenes' => 10,
  ],
];
