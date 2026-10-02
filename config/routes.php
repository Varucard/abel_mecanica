<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\ClienteController;
use App\Controllers\ConfiguracionController;
use App\Controllers\HomeController;
use App\Controllers\MarcaController;
use App\Controllers\ModeloController;
use App\Controllers\OrdenController;
use App\Controllers\RepuestoController;
use App\Controllers\ServicioController;
use App\Controllers\TurnoController;
use App\Controllers\UsuarioController;
use App\Controllers\VehiculoController;
use App\Core\Router;

return function (Router $r): void {
  // Acceso
  $r->get('/login', [AuthController::class, 'loginForm'], Router::ACCESO_PUBLICO);
  $r->post('/login', [AuthController::class, 'login'], Router::ACCESO_PUBLICO);
  $r->post('/logout', [AuthController::class, 'logout']);
  $r->get('/instalacion', [AuthController::class, 'setupForm'], Router::ACCESO_PUBLICO);
  $r->post('/instalacion', [AuthController::class, 'setup'], Router::ACCESO_PUBLICO);
  $r->get('/perfil/clave', [UsuarioController::class, 'claveForm']);
  $r->post('/perfil/clave', [UsuarioController::class, 'cambiarClave']);

  $r->get('/', [HomeController::class, 'index']);

  // Clientes
  $r->get('/clientes', [ClienteController::class, 'index']);
  $r->get('/clientes/crear', [ClienteController::class, 'create']);
  $r->post('/clientes', [ClienteController::class, 'store']);
  $r->get('/clientes/{id}/editar', [ClienteController::class, 'edit']);
  $r->post('/clientes/{id}', [ClienteController::class, 'update']);
  $r->post('/clientes/{id}/estado', [ClienteController::class, 'toggle']);
  $r->post('/clientes/{id}/eliminar', [ClienteController::class, 'destroy']);
  $r->get('/clientes/{clienteId}/vehiculos', [VehiculoController::class, 'porCliente']);

  // Vehículos
  $r->get('/vehiculos', [VehiculoController::class, 'index']);
  $r->get('/vehiculos/crear', [VehiculoController::class, 'create']);
  $r->post('/vehiculos', [VehiculoController::class, 'store']);
  $r->get('/vehiculos/{id}/editar', [VehiculoController::class, 'edit']);
  $r->post('/vehiculos/{id}', [VehiculoController::class, 'update']);
  $r->post('/vehiculos/{id}/estado', [VehiculoController::class, 'toggle']);

  // Catálogos
  foreach (['marcas' => MarcaController::class, 'modelos' => ModeloController::class,
            'servicios' => ServicioController::class, 'repuestos' => RepuestoController::class] as $path => $controller) {
    $r->get("/{$path}", [$controller, 'index']);
    $r->post("/{$path}", [$controller, 'store']);
    $r->get("/{$path}/{id}/editar", [$controller, 'edit']);
    $r->post("/{$path}/{id}", [$controller, 'update']);
    $r->post("/{$path}/{id}/eliminar", [$controller, 'destroy']);
  }
  $r->get('/marcas/{marcaId}/modelos', [VehiculoController::class, 'modelosPorMarca']);

  // Órdenes y presupuestos
  $r->get('/ordenes', [OrdenController::class, 'index']);
  $r->get('/ordenes/crear', [OrdenController::class, 'create']);
  $r->post('/ordenes', [OrdenController::class, 'store']);
  $r->get('/ordenes/{id}/editar', [OrdenController::class, 'edit']);
  $r->post('/ordenes/{id}', [OrdenController::class, 'update']);
  $r->post('/ordenes/{id}/estado', [OrdenController::class, 'cambiarEstado']);
  $r->get('/ordenes/{id}/presupuesto', [OrdenController::class, 'presupuesto']);
  $r->get('/ordenes/{id}/presupuesto/pdf', [OrdenController::class, 'pdf']);

  // Turnos
  $r->get('/turnos', [TurnoController::class, 'index']);
  $r->get('/turnos/crear', [TurnoController::class, 'create']);
  $r->post('/turnos', [TurnoController::class, 'store']);
  $r->get('/turnos/{id}/editar', [TurnoController::class, 'edit']);
  $r->post('/turnos/{id}', [TurnoController::class, 'update']);
  $r->post('/turnos/{id}/estado', [TurnoController::class, 'cambiarEstado']);
  $r->post('/turnos/{id}/eliminar', [TurnoController::class, 'destroy']);

  // Solo administradores
  $admin = Router::ACCESO_ADMIN;
  $r->get('/configuracion', [ConfiguracionController::class, 'edit'], $admin);
  $r->post('/configuracion', [ConfiguracionController::class, 'update'], $admin);
  $r->get('/usuarios', [UsuarioController::class, 'index'], $admin);
  $r->get('/usuarios/crear', [UsuarioController::class, 'create'], $admin);
  $r->post('/usuarios', [UsuarioController::class, 'store'], $admin);
  $r->get('/usuarios/{id}/editar', [UsuarioController::class, 'edit'], $admin);
  $r->post('/usuarios/{id}', [UsuarioController::class, 'update'], $admin);
};
