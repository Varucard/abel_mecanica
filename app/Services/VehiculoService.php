<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Combustible;
use App\Enums\Estado;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Vehiculo;
use App\Repositories\ClienteRepository;
use App\Repositories\ModeloRepository;
use App\Repositories\Repository;
use App\Repositories\VehiculoRepository;
use App\Support\ImageUpload;
use App\Support\Validator;
use PDOException;

final class VehiculoService
{
  public const ANIO_MINIMO = 1940;
  public const KM_MAXIMO = 9_999_999;

  public function __construct(
    private readonly VehiculoRepository $vehiculos,
    private readonly ClienteRepository $clientes,
    private readonly ModeloRepository $modelos,
  ) {
  }

  public static function anioMaximo(): int
  {
    // Se admite el modelo del año próximo (los 0 km suelen salir con año siguiente).
    return (int) date('Y') + 1;
  }

  /** Patentes argentinas: Mercosur (AB123CD) o formato anterior (ABC123). */
  public static function patenteValida(string $patente): bool
  {
    return (bool) preg_match('/^([A-Z]{2}\d{3}[A-Z]{2}|[A-Z]{3}\d{3})$/', $patente);
  }

  /** @return array<string, mixed> */
  public function obtener(int $id): array
  {
    return $this->vehiculos->find($id) ?? throw new NotFoundException('Vehículo no encontrado.');
  }

  /** @param array<string, mixed> $input */
  public function crear(array $input): int
  {
    return $this->persistir($this->construir($input));
  }

  /** @param array<string, mixed> $input */
  public function actualizar(int $id, array $input): void
  {
    $this->obtener($id);
    $this->persistir($this->construir($input, $id));
  }

  /** @param array<string, mixed> $archivo elemento de $_FILES */
  public function agregarImagen(int $id, array $archivo, ?string $descripcion, ?int $usuarioId): void
  {
    $this->obtener($id);
    $descripcion = Validator::nullable((string) $descripcion);
    (new Validator())->check($descripcion === null || Validator::largo($descripcion, 1, 255), 'La descripción es demasiado larga.')->validate();

    $nombre = ImageUpload::en('vehiculos')->guardar($archivo);
    $this->vehiculos->agregarImagen($id, $nombre, $descripcion, $usuarioId);
  }

  /** Elimina una imagen. Devuelve el id del vehículo. */
  public function eliminarImagen(int $imagenId): int
  {
    $imagen = $this->vehiculos->imagen($imagenId) ?? throw new NotFoundException('Imagen no encontrada.');
    $this->vehiculos->eliminarImagen($imagenId);
    ImageUpload::en('vehiculos')->eliminar($imagen['archivo']);

    return (int) $imagen['vehiculo_id'];
  }

  public function rutaImagen(int $vehiculoId, int $imagenId): string
  {
    $imagen = $this->vehiculos->imagen($imagenId);

    return ($imagen && (int) $imagen['vehiculo_id'] === $vehiculoId ? ImageUpload::en('vehiculos')->ruta($imagen['archivo']) : null)
      ?? throw new NotFoundException('Imagen no encontrada.');
  }

  public function alternarEstado(int $id): Estado
  {
    $nuevo = Estado::from($this->obtener($id)['estado'])->alternar();
    $this->vehiculos->setEstado($id, $nuevo);

    return $nuevo;
  }

  private function persistir(Vehiculo $vehiculo): int
  {
    try {
      if ($vehiculo->id === null) {
        return $this->vehiculos->create($vehiculo);
      }
      $this->vehiculos->update($vehiculo);

      return $vehiculo->id;
    } catch (PDOException $e) {
      throw Repository::isDuplicate($e)
        ? new ValidationException([str_contains($e->getMessage(), 'chasis')
          ? 'El número de chasis ya corresponde a otro vehículo registrado.'
          : 'La patente ya corresponde a otro vehículo registrado.'])
        : $e;
    }
  }

  /** @param array<string, mixed> $input */
  private function construir(array $input, ?int $id = null): Vehiculo
  {
    $clienteId = (int) ($input['cliente_id'] ?? 0);
    $marcaId = (int) ($input['marca_id'] ?? 0);
    $modeloId = (int) ($input['modelo_id'] ?? 0);
    $anio = (int) ($input['anio'] ?? 0);
    $patente = strtoupper(preg_replace('/[\s-]/', '', (string) ($input['patente'] ?? '')));
    $kmRaw = trim((string) ($input['kilometraje'] ?? ''));
    $km = $kmRaw === '' ? null : filter_var($kmRaw, FILTER_VALIDATE_INT);
    $combustibleRaw = trim((string) ($input['combustible'] ?? ''));
    $combustible = $combustibleRaw === '' ? null : Combustible::tryFrom($combustibleRaw);
    $chasis = Validator::nullable(strtoupper(preg_replace('/\s/', '', (string) ($input['numero_chasis'] ?? ''))));
    $motor = Validator::nullable((string) ($input['motor'] ?? ''));
    $color = Validator::nullable((string) ($input['color'] ?? ''));

    (new Validator())
      ->check($clienteId > 0 && $this->clientes->find($clienteId) !== null, 'Seleccioná un cliente válido.')
      ->check($marcaId > 0 && $modeloId > 0 && $this->modelos->perteneceAMarca($modeloId, $marcaId), 'Seleccioná una marca y un modelo válidos.')
      ->check($anio >= self::ANIO_MINIMO && $anio <= self::anioMaximo(), sprintf('El año debe estar entre %d y %d.', self::ANIO_MINIMO, self::anioMaximo()))
      ->check(self::patenteValida($patente), 'La patente debe tener formato AB123CD o ABC123.')
      ->check($km === null || ($km !== false && $km >= 0 && $km <= self::KM_MAXIMO), 'El kilometraje debe estar entre 0 y 9.999.999 km.')
      ->check($combustibleRaw === '' || $combustible !== null, 'Seleccioná un combustible válido.')
      ->check($chasis === null || (bool) preg_match('/^[A-HJ-NPR-Z0-9]{8,17}$/', $chasis), 'El número de chasis (VIN) debe tener entre 8 y 17 caracteres alfanuméricos (sin I, O ni Q).')
      ->check($motor === null || Validator::largo($motor, 1, 50), 'El motor no puede superar los 50 caracteres.')
      ->check($color === null || Validator::largo($color, 1, 30), 'El color no puede superar los 30 caracteres.')
      ->validate();

    return new Vehiculo(
      $clienteId, $marcaId, $modeloId, $anio, $patente, $km ?: null, id: $id,
      motor: $motor, combustible: $combustible, color: $color, numeroChasis: $chasis,
      detalle: Validator::nullable((string) ($input['detalle'] ?? '')),
    );
  }
}
