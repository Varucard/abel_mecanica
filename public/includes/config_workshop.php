<?php

/**
 * Configuración del Taller
 * Datos generales para usar en presupuestos, facturas, encabezados, etc.
 */

$config_taller = [
  'nombre' => 'Mecánica Abel',
  'cuit' => '20-40137152-5',
  'direccion' => 'Saenz Peña 296 (ex 1702)',
  'telefono' => '+54 9 11 3635-9867',
  'whatsapp' => '+5491136359867',  // sin guiones ni espacios para el link de WhatsApp
  'email' => 'abel.amarquez@gmail.com',
  'logo' => '../assets/img/logo.png',  // ruta relativa al archivo que lo use
  'firma' => '../assets/img/firma.png',  // ruta relativa al archivo que lo use
];

$config_trabajo = [
  'validez' => 10, //Días
  'garantia' => 90, //Días
  'tiempo_estimado' => 3, //Días
  'forma_pago' => ['Contado', 'Tarjeta de Crédito', 'Tarjeta de Débito', 'Transferencia Bancaria', 'Mercado Pago'],
  'mensaje_legal' => 'Este documento no es una factura y no posee validez fiscal.'
];
