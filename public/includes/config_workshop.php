<?php

/**
 * Configuración del Taller
 * Datos generales para usar en presupuestos, facturas, encabezados, etc.
 */

$config_taller = [
  'nombre' => 'Mecánica Abel',
  'cuit' => '20-40137152-5',
  'direccion' => 'Saenz Peña 296 (ex 1702)',
  'telefono' => '11-3635-9867',
  'whatsapp' => '+5491136359867',
  'email' => 'abel.amarquez@gmail.com',
  'logo' => '../assets/img/logo.png',
];

$config_trabajo = [
  'validez' => 10,
  'garantia' => 10,
  'tiempo_estimado' => 3,
  'forma_pago' => array(
    0 => 'Contado',
    1 => 'Tarjeta de Crédito',
    2 => 'Tarjeta de Débito',
    3 => 'Transferencia Bancaria',
    4 => 'Mercado Pago',
  ),
  'observaciones' => array(
    0 => 'Tiempo estimado de reparación: 2-4 días hábiles desde la aceptación y recepción de repuestos.',
    1 => 'Los precios están sujetos a modificación si surgen imprevistos o variaciones en repuestos.',
  ),
  'mensaje_legal' => 'Este documento no es una factura y no posee validez fiscal.'
];
