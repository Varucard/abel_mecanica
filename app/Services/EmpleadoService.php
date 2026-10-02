<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Estado;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Empleado;
use App\Repositories\EmpleadoRepository;
use App\Repositories\Repository;
use App\Support\Validator;
use PDOException;

final class EmpleadoService
{
  public function __construct(private readonly EmpleadoRepository $empleados)
  {
  }

  /** @return array<string, mixed> */
  public function obtener(int $id): array
  {
    return $this->empleados->find($id) ?? throw new NotFoundException('Empleado no encontrado.');
  }

  /** @param array<string, mixed> $input */
  public function crear(array $input): int
  {
    try {
      return $this->empleados->create($this->construir($input));
    } catch (PDOException $e) {
      throw Repository::isDuplicate($e) ? new ValidationException(['Esa persona ya está registrada como empleado.']) : $e;
    }
  }

  /** @param array<string, mixed> $input */
  public function actualizar(int $id, array $input): void
  {
    $actual = $this->obtener($id);
    $input['dni'] = $actual['dni'];
    $this->empleados->update($this->construir($input, $id));
  }

  public function alternarEstado(int $id): Estado
  {
    $nuevo = Estado::from($this->obtener($id)['estado'])->alternar();
    $this->empleados->setEstado($id, $nuevo);

    return $nuevo;
  }

  /** @param array<string, mixed> $input */
  private function construir(array $input, ?int $id = null): Empleado
  {
    $nombre = mb_strtoupper(trim((string) ($input['nombre'] ?? '')));
    $apellido = mb_strtoupper(trim((string) ($input['apellido'] ?? '')));
    $dni = trim((string) ($input['dni'] ?? ''));
    $puesto = trim((string) ($input['puesto'] ?? ''));
    $telefono = Validator::nullable(preg_replace('/\D/', '', (string) ($input['telefono'] ?? '')));
    $ingreso = Validator::nullable((string) ($input['fecha_ingreso'] ?? ''));
    $email = Validator::nullable(mb_strtolower((string) ($input['email'] ?? '')));

    (new Validator())
      ->check(Validator::soloLetras($nombre, 2, 50), 'El nombre debe contener solo letras (2 a 50 caracteres).')
      ->check(Validator::soloLetras($apellido, 2, 50), 'El apellido debe contener solo letras (2 a 50 caracteres).')
      ->check((bool) preg_match('/^\d{6,8}$/', $dni), 'El DNI debe tener entre 6 y 8 dígitos.')
      ->check(Validator::largo($puesto, 2, 50), 'Indicá el puesto (2 a 50 caracteres).')
      ->check($telefono === null || (bool) preg_match('/^\d{10}$/', $telefono), 'El teléfono debe tener 10 dígitos.')
      ->check($ingreso === null || (Validator::fecha($ingreso) && $ingreso <= date('Y-m-d')), 'La fecha de ingreso no es válida.')
      ->check($email === null || filter_var($email, FILTER_VALIDATE_EMAIL) !== false, 'El email no tiene un formato válido.')
      ->validate();

    return new Empleado($nombre, $apellido, $dni, $puesto, $telefono, $ingreso, $email, id: $id);
  }
}
