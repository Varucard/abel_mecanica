<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Servicio;
use App\Repositories\Repository;
use App\Repositories\ServicioRepository;
use App\Support\Validator;
use PDOException;

final class ServicioService
{
  public function __construct(private readonly ServicioRepository $servicios)
  {
  }

  /** @return array<string, mixed> */
  public function obtener(int $id): array
  {
    return $this->servicios->find($id) ?? throw new NotFoundException('Servicio no encontrado.');
  }

  /** @param array<string, mixed> $input */
  public function guardar(array $input, ?int $id = null): int
  {
    if ($id !== null) {
      $this->obtener($id);
    }

    $nombre = trim((string) ($input['nombre'] ?? ''));
    $precio = Validator::importe((string) ($input['precio_base'] ?? ''));
    $descripcion = Validator::nullable((string) ($input['descripcion'] ?? ''));

    (new Validator())
      ->check(Validator::largo($nombre, 2, 100), 'El nombre del servicio debe tener entre 2 y 100 caracteres.')
      ->check($precio !== null, 'El precio base debe ser un número mayor o igual a 0.')
      ->validate();

    try {
      return $this->servicios->save(new Servicio($nombre, $precio, $descripcion, $id));
    } catch (PDOException $e) {
      throw Repository::isDuplicate($e) ? new ValidationException(['Ya existe un servicio con ese nombre.']) : $e;
    }
  }

  public function eliminar(int $id): void
  {
    $this->obtener($id);

    try {
      $this->servicios->delete($id);
    } catch (PDOException $e) {
      throw Repository::isReferenced($e)
        ? new ValidationException(['El servicio figura en órdenes existentes y no se puede eliminar.'])
        : $e;
    }
  }
}
