<?php

/**
 * Valores por defecto de la configuración del sistema.
 *
 * Lo que se edita desde "Configuración > Sistema" se guarda en
 * storage/config/taller.json y tiene prioridad sobre estos valores.
 *
 * Variables disponibles en las plantillas de mensajes:
 *   {cliente} {fecha} {hora} {vehiculo} {patente} {taller} {direccion}
 *   {telefono} {link_turno} {link_seguimiento}
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
  ],

  'stock' => [
    // Permitir finalizar órdenes aunque el stock de un repuesto quede negativo.
    'permitir_negativo' => true,
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
