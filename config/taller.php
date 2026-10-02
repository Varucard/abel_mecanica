<?php

/**
 * Valores por defecto de la configuración del taller.
 *
 * Lo que se edita desde la pantalla "Configuración > Sistema" se guarda en
 * storage/config/taller.json y tiene prioridad sobre estos valores.
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
];
