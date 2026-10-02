<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Repuesto;
use App\Repositories\Repository;
use App\Repositories\RepuestoRepository;
use App\Support\Validator;
use PDOException;

final class RepuestoService
{
  public function __construct(private readonly RepuestoRepository $repuestos)
  {
  }

  /** @return array<string, mixed> */
  public function obtener(int $id): array
  {
    return $this->repuestos->find($id) ?? throw new NotFoundException('Repuesto no encontrado.');
  }

  /** @param array<string, mixed> $input */
  public function guardar(array $input, ?int $id = null): int
  {
    if ($id !== null) {
      $this->obtener($id);
    }

    $nombre = trim((string) ($input['nombre'] ?? ''));
    $precio = Validator::importe((string) ($input['precio'] ?? ''));
    $descripcion = Validator::nullable((string) ($input['descripcion'] ?? ''));

    (new Validator())
      ->check(Validator::largo($nombre, 2, 150), 'El nombre del repuesto debe tener entre 2 y 150 caracteres.')
      ->check($precio !== null, 'El precio debe ser un número mayor o igual a 0.')
      ->validate();

    return $this->repuestos->save(new Repuesto($nombre, $precio, $descripcion, $id));
  }

  public function eliminar(int $id): void
  {
    $this->obtener($id);

    try {
      $this->repuestos->delete($id);
    } catch (PDOException $e) {
      throw Repository::isReferenced($e)
        ? new ValidationException(['El repuesto figura en órdenes existentes y no se puede eliminar.'])
        : $e;
    }
  }
}
