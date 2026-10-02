<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Enums\Rol;
use App\Exceptions\ValidationException;
use App\Services\UsuarioService;

final class UsuarioServiceTest extends IntegrationTestCase
{
  private UsuarioService $usuarios;

  protected function setUp(): void
  {
    parent::setUp();
    $this->usuarios = $this->make(UsuarioService::class);
  }

  private function crear(string $usuario, Rol $rol = Rol::Empleado): int
  {
    return $this->usuarios->crear([
      'nombre' => 'Prueba', 'usuario' => $usuario, 'rol' => $rol->value,
      'clave' => 'clave-segura', 'clave_confirmacion' => 'clave-segura',
    ]);
  }

  public function testAutenticaConCredencialesValidasSinExponerElHash(): void
  {
    $this->crear('Abel');

    $usuario = $this->usuarios->autenticar(' abel ', 'clave-segura', '10.0.0.1');

    $this->assertSame('abel', $usuario['usuario']);
    $this->assertArrayNotHasKey('password_hash', $usuario);
    $this->assertSame(60, strlen($this->db->query("SELECT password_hash FROM usuarios")->fetchColumn()));
  }

  public function testRechazaClaveIncorrectaYUsuarioInactivo(): void
  {
    $id = $this->crear('abel');

    try {
      $this->usuarios->autenticar('abel', 'otra', '10.0.0.1');
      $this->fail('Debía rechazar la clave');
    } catch (ValidationException $e) {
      $this->assertSame(['Usuario o contraseña incorrectos.'], $e->errors());
    }

    $this->crear('admin', Rol::Administrador);
    $this->usuarios->actualizar($id, ['nombre' => 'Prueba', 'usuario' => 'abel', 'rol' => 'empleado'], 999);

    $this->expectExceptionMessage('Usuario o contraseña incorrectos.');
    $this->usuarios->autenticar('abel', 'clave-segura', '10.0.0.2');
  }

  public function testBloqueaTrasDemasiadosIntentos(): void
  {
    $this->crear('abel');

    for ($i = 0; $i < UsuarioService::MAX_INTENTOS; $i++) {
      try {
        $this->usuarios->autenticar('abel', 'mal', '10.0.0.3');
      } catch (ValidationException) {
      }
    }

    // Incluso con la clave correcta queda bloqueado.
    $this->expectExceptionMessage('Demasiados intentos fallidos');
    $this->usuarios->autenticar('abel', 'clave-segura', '10.0.0.3');
  }

  public function testSiempreQuedaUnAdministradorActivo(): void
  {
    $admin = $this->crear('admin', Rol::Administrador);
    $otro = $this->crear('jefe', Rol::Administrador);

    // No puede degradarse a sí mismo.
    try {
      $this->usuarios->actualizar($admin, ['nombre' => 'Admin', 'usuario' => 'admin', 'rol' => 'empleado', 'activo' => 1], $admin);
      $this->fail('No debía permitir quitarse el rol');
    } catch (ValidationException $e) {
      $this->assertStringContainsString('No podés quitarte el rol', $e->getMessage());
    }

    // Otro admin sí puede degradarlo, mientras quede uno.
    $this->usuarios->actualizar($admin, ['nombre' => 'Admin', 'usuario' => 'admin', 'rol' => 'empleado', 'activo' => 1], $otro);

    $this->expectExceptionMessage('al menos un administrador activo');
    $this->usuarios->actualizar($otro, ['nombre' => 'Jefe', 'usuario' => 'jefe', 'rol' => 'empleado', 'activo' => 1], 999);
  }

  public function testValidaClaveYUsuarioDuplicado(): void
  {
    $this->crear('abel');

    try {
      $this->usuarios->crear(['nombre' => 'Xavier', 'usuario' => 'otro', 'rol' => 'empleado', 'clave' => 'corta', 'clave_confirmacion' => 'corta']);
      $this->fail('Debía rechazar la clave corta');
    } catch (ValidationException $e) {
      $this->assertStringContainsString('al menos', $e->getMessage());
    }

    $this->expectExceptionMessage('Ese nombre de usuario ya existe.');
    $this->crear('ABEL');
  }
}
