<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\BusquedaController;
use App\Controllers\ClienteController;
use App\Controllers\ConfiguracionController;
use App\Controllers\EmpleadoController;
use App\Controllers\HomeController;
use App\Controllers\MarcaController;
use App\Controllers\ModeloController;
use App\Controllers\OrdenController;
use App\Controllers\PagoController;
use App\Controllers\ProveedorController;
use App\Controllers\PublicoController;
use App\Controllers\RegistroController;
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
  // Páginas públicas para clientes
  $publico = Router::ACCESO_PUBLICO;
  $r->get('/turno/{token:token}', [PublicoController::class, 'turno'], $publico);
  $r->post('/turno/{token:token}/confirmar', [PublicoController::class, 'confirmarTurno'], $publico);
  $r->post('/turno/{token:token}/cancelar', [PublicoController::class, 'cancelarTurno'], $publico);
  $r->get('/seguimiento', [PublicoController::class, 'seguimiento'], $publico);
  $r->post('/seguimiento', [PublicoController::class, 'consultar'], $publico);

  $r->get('/perfil/clave', [UsuarioController::class, 'claveForm']);
  $r->post('/perfil/clave', [UsuarioController::class, 'cambiarClave']);

  $r->get('/', [HomeController::class, 'index']);
  $r->get('/buscar', [BusquedaController::class, 'index']);

  // Clientes
  $r->get('/clientes', [ClienteController::class, 'index']);
  $r->get('/clientes/crear', [ClienteController::class, 'create']);
  $r->post('/clientes', [ClienteController::class, 'store']);
  $r->get('/clientes/{id}', [ClienteController::class, 'show']);
  $r->get('/clientes/{id}/editar', [ClienteController::class, 'edit']);
  $r->get('/clientes/{id}/foto', [ClienteController::class, 'foto']);
  $r->post('/clientes/{id}/foto', [ClienteController::class, 'subirFoto']);
  $r->post('/clientes/{id}/foto/eliminar', [ClienteController::class, 'quitarFoto']);
  $r->post('/clientes/{id}', [ClienteController::class, 'update']);
  $r->post('/clientes/{id}/estado', [ClienteController::class, 'toggle']);
  $r->post('/clientes/{id}/eliminar', [ClienteController::class, 'destroy']);
  $r->get('/clientes/{clienteId}/vehiculos', [VehiculoController::class, 'porCliente']);

  // Vehículos
  $r->get('/vehiculos', [VehiculoController::class, 'index']);
  $r->get('/vehiculos/crear', [VehiculoController::class, 'create']);
  $r->post('/vehiculos', [VehiculoController::class, 'store']);
  $r->get('/vehiculos/{id}', [VehiculoController::class, 'show']);
  $r->get('/vehiculos/{id}/editar', [VehiculoController::class, 'edit']);
  $r->post('/vehiculos/{id}/imagenes', [VehiculoController::class, 'subirImagen']);
  $r->get('/vehiculos/{id}/imagenes/{imagenId}', [VehiculoController::class, 'imagen']);
  $r->post('/imagenes/{imagenId}/eliminar', [VehiculoController::class, 'eliminarImagen']);
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

  // Stock y proveedores
  $r->get('/repuestos/{id}/stock', [RepuestoController::class, 'stock']);
  $r->post('/repuestos/{id}/ingresos', [RepuestoController::class, 'ingresar']);
  $r->post('/repuestos/{id}/ajustes', [RepuestoController::class, 'ajustar']);
  $r->get('/proveedores', [ProveedorController::class, 'index']);
  $r->get('/proveedores/crear', [ProveedorController::class, 'create']);
  $r->post('/proveedores', [ProveedorController::class, 'store']);
  $r->get('/proveedores/{id}/editar', [ProveedorController::class, 'edit']);
  $r->post('/proveedores/{id}', [ProveedorController::class, 'update']);
  $r->post('/proveedores/{id}/eliminar', [ProveedorController::class, 'destroy']);

  // Órdenes y presupuestos
  $r->get('/ordenes', [OrdenController::class, 'index']);
  $r->get('/ordenes/crear', [OrdenController::class, 'create']);
  $r->post('/ordenes', [OrdenController::class, 'store']);
  $r->get('/ordenes/{id}', [OrdenController::class, 'show']);
  $r->get('/ordenes/{id}/editar', [OrdenController::class, 'edit']);
  $r->post('/ordenes/{id}/pagos', [PagoController::class, 'store']);
  $r->get('/deudores', [PagoController::class, 'deudores']);
  $r->post('/ordenes/{id}', [OrdenController::class, 'update']);
  $r->post('/ordenes/{id}/estado', [OrdenController::class, 'cambiarEstado']);
  $r->get('/ordenes/{id}/presupuesto', [OrdenController::class, 'presupuesto']);
  $r->get('/ordenes/{id}/presupuesto/pdf', [OrdenController::class, 'pdf']);
  $r->get('/ordenes/{id}/entrega', [OrdenController::class, 'entrega']);
  $r->get('/ordenes/{id}/entrega/pdf', [OrdenController::class, 'entregaPdf']);

  // Turnos
  $r->get('/turnos', [TurnoController::class, 'index']);
  $r->get('/turnos/crear', [TurnoController::class, 'create']);
  $r->post('/turnos', [TurnoController::class, 'store']);
  $r->get('/turnos/{id}/editar', [TurnoController::class, 'edit']);
  $r->post('/turnos/{id}', [TurnoController::class, 'update']);
  $r->post('/turnos/{id}/estado', [TurnoController::class, 'cambiarEstado']);
  $r->post('/turnos/{id}/eliminar', [TurnoController::class, 'destroy']);
  $r->post('/turnos/{id}/whatsapp', [TurnoController::class, 'whatsapp']);
  $r->post('/turnos/{id}/recordar', [TurnoController::class, 'recordar']);
  $r->post('/turnos/{id}/confirmacion', [TurnoController::class, 'pedirConfirmacion']);

  // Solo administradores
  $admin = Router::ACCESO_ADMIN;
  $r->get('/auditoria', [RegistroController::class, 'auditoria'], $admin);
  $r->get('/auditoria/datos', [RegistroController::class, 'auditoriaDatos'], $admin);
  $r->get('/logs', [RegistroController::class, 'logs'], $admin);
  $r->get('/configuracion', [ConfiguracionController::class, 'index'], $admin);
  $r->post('/configuracion/feriados/importar', [ConfiguracionController::class, 'importarFeriados'], $admin);
  $r->get('/configuracion/{seccion:slug}', [ConfiguracionController::class, 'edit'], $admin);
  $r->post('/configuracion/{seccion:slug}', [ConfiguracionController::class, 'update'], $admin);
  $r->get('/usuarios', [UsuarioController::class, 'index'], $admin);
  $r->get('/usuarios/crear', [UsuarioController::class, 'create'], $admin);
  $r->post('/usuarios', [UsuarioController::class, 'store'], $admin);
  $r->get('/usuarios/{id}/editar', [UsuarioController::class, 'edit'], $admin);
  $r->post('/usuarios/{id}', [UsuarioController::class, 'update'], $admin);
  $r->post('/pagos/{id}/anular', [PagoController::class, 'destroy'], $admin);
  $r->get('/empleados', [EmpleadoController::class, 'index'], $admin);
  $r->get('/empleados/crear', [EmpleadoController::class, 'create'], $admin);
  $r->post('/empleados', [EmpleadoController::class, 'store'], $admin);
  $r->get('/empleados/{id}/editar', [EmpleadoController::class, 'edit'], $admin);
  $r->post('/empleados/{id}', [EmpleadoController::class, 'update'], $admin);
  $r->post('/empleados/{id}/estado', [EmpleadoController::class, 'toggle'], $admin);
};
