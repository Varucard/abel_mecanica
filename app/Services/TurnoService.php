<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoTurno;
use App\Exceptions\NotFoundException;
use App\Models\Turno;
use App\Repositories\ClienteRepository;
use App\Repositories\TurnoRepository;
use App\Repositories\VehiculoRepository;
use App\Support\Validator;

final class TurnoService
{
  public function __construct(
    private readonly TurnoRepository $turnos,
    private readonly ClienteRepository $clientes,
    private readonly VehiculoRepository $vehiculos,
  ) {
  }

  /** @return array<string, mixed> */
  public function obtener(int $id): array
  {
    return $this->turnos->find($id) ?? throw new NotFoundException('Turno no encontrado.');
  }

  /** @param array<string, mixed> $input */
  public function guardar(array $input, ?int $id = null): int
  {
    if ($id !== null) {
      $this->obtener($id);
    }

    $clienteId = (int) ($input['cliente_id'] ?? 0);
    $vehiculoId = (int) ($input['vehiculo_id'] ?? 0);
    $fecha = trim((string) ($input['fecha'] ?? ''));
    $hora = substr(trim((string) ($input['hora'] ?? '')), 0, 5);
    $descripcion = Validator::nullable((string) ($input['descripcion'] ?? ''));
    $estado = EstadoTurno::tryFrom((string) ($input['estado'] ?? EstadoTurno::Pendiente->value));

    $v = (new Validator())
      ->check($clienteId > 0 && $this->clientes->find($clienteId) !== null, 'Seleccioná un cliente válido.')
      ->check($vehiculoId > 0 && $this->vehiculos->perteneceACliente($vehiculoId, $clienteId), 'El vehículo no pertenece al cliente seleccionado.')
      ->check(Validator::fecha($fecha), 'La fecha no es válida.')
      ->check(Validator::hora($hora), 'La hora no es válida.')
      ->check($estado !== null, 'El estado del turno no es válido.')
      ->check($id !== null || !Validator::fecha($fecha) || $fecha >= date('Y-m-d'), 'No se pueden agendar turnos en fechas pasadas.');
    $v->validate();

    if (!$estado->liberaHorario()) {
      $v->check(!$this->turnos->horarioOcupado($fecha, $hora, $id), 'Ya hay un turno agendado para esa fecha y hora.')->validate();
    }

    return $this->turnos->save(new Turno($clienteId, $vehiculoId, $fecha, $hora, $descripcion, $estado, $id));
  }

  public function cambiarEstado(int $id, string $estado): EstadoTurno
  {
    $turno = $this->obtener($id);

    $nuevo = EstadoTurno::tryFrom($estado);
    $v = (new Validator())->check($nuevo !== null, 'Estado de turno inválido.');
    $v->validate();

    if (!$nuevo->liberaHorario()) {
      $v->check(
        !$this->turnos->horarioOcupado($turno['fecha'], substr($turno['hora'], 0, 5), $id),
        'No se puede reactivar: ya hay otro turno en ese horario.'
      )->validate();
    }

    $this->turnos->setEstado($id, $nuevo);

    return $nuevo;
  }

  public function eliminar(int $id): void
  {
    $this->obtener($id);
    $this->turnos->delete($id);
  }
}
