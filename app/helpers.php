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
