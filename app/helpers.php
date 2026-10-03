<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Session;

/**
 * Funciones de ayuda para las vistas.
 */

/** Escapa texto para HTML (contenido y atributos). */
function e(mixed $value): string
{
  return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** URL absoluta dentro de la aplicación, respetando el subdirectorio de instalación. */
function url(string $path = '/'): string
{
  return App::instance()->basePath . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
  return url('assets/' . ltrim($path, '/'));
}

/** Formato de moneda argentino: 1.234,50 */
function money(mixed $amount): string
{
  return number_format((float) $amount, 2, ',', '.');
}

function format_date(?string $date, string $format = 'd/m/Y'): string
{
  return $date ? date($format, strtotime($date)) : '—';
}

function session(): Session
{
  return App::instance()->container->get(Session::class);
}

/** Valor previo del formulario (si hubo error) o el valor por defecto. */
function old(string $key, mixed $default = ''): mixed
{
  return session()->old($key, $default);
}

function csrf_field(): string
{
  return '<input type="hidden" name="_token" value="' . e(session()->csrfToken()) . '">';
}

function selected(bool $condition): string
{
  return $condition ? 'selected' : '';
}

/** Primer segmento de la ruta actual (p. ej. "clientes"), para resaltar la sección. */
function current_section(): string
{
  $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
  $path = substr($path, strlen(App::instance()->basePath));

  return explode('/', trim($path, '/'))[0] ?? '';
}

function auth(): \App\Core\Auth
{
  return App::instance()->container->get(\App\Core\Auth::class);
}

/** Cantidad sin decimales innecesarios: 2 → "2", 1.5 → "1,5". */
function qty(mixed $value): string
{
  return rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');
}

/**
 * Teléfono en formato internacional sin "+". Para Argentina (54) un número de
 * 10 dígitos (área + número) se marca como celular: 54 9 + área + número.
 */
function telefono_internacional(string $telefono, string $codigoPais = '54'): string
{
  $digitos = preg_replace('/\D/', '', $telefono);

  if (strlen($digitos) <= 10) {
    return $codigoPais . ($codigoPais === '54' && strlen($digitos) === 10 ? '9' : '') . $digitos;
  }

  return $digitos;
}

/** Link de WhatsApp (wa.me) con un mensaje opcional ya escrito. */
function whatsapp_url(string $telefono, string $mensaje = '', string $codigoPais = '54'): string
{
  return 'https://wa.me/' . telefono_internacional($telefono, $codigoPais)
    . ($mensaje !== '' ? '?text=' . rawurlencode($mensaje) : '');
}

/**
 * URL absoluta (para links en emails). Usa APP_URL; si no está definida y hay
 * una petición web en curso, la arma con el host actual.
 */
function absolute_url(string $path = '/'): string
{
  $base = \App\Core\Env::get('APP_URL');

  if ($base === null) {
    $esquema = \App\Core\Request::esHttps() ? 'https' : 'http';
    $base = isset($_SERVER['HTTP_HOST']) ? $esquema . '://' . $_SERVER['HTTP_HOST'] . App::instance()->basePath : 'http://localhost';
  }

  return rtrim($base, '/') . '/' . ltrim($path, '/');
}

/** URL de la ficha de una entidad auditada, o null si no tiene pantalla propia. */
function url_entidad(string $entidad, ?int $id): ?string
{
  if ($id === null) {
    return null;
  }

  $rutas = [
    'orden' => "ordenes/{$id}", 'cliente' => "clientes/{$id}", 'vehiculo' => "vehiculos/{$id}",
    'repuesto' => "repuestos/{$id}/stock", 'turno' => "turnos/{$id}/editar", 'usuario' => "usuarios/{$id}/editar",
    'empleado' => "empleados/{$id}/editar", 'servicio' => "servicios/{$id}/editar", 'proveedor' => "proveedores/{$id}/editar",
    'combo' => "combos/{$id}/editar",
  ];

  return isset($rutas[$entidad]) ? url($rutas[$entidad]) : null;
}
