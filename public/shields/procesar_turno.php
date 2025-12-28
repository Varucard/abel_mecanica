<?php
require_once '../includes/config_database.php';
require_once '../Clases/Turnos.php';

class TurnoController
{
  private $conn;

  public function __construct($conn)
  {
    $this->conn = $conn;
  }

  // Crear un nuevo turno
  public function crear($datos)
  {
    try {
      // Validar datos requeridos
      if (
        empty($datos['cliente_id']) || empty($datos['vehiculo_id']) ||
        empty($datos['fecha']) || empty($datos['hora'])
      ) {
        return [
          'status' => 'error',
          'message' => 'Todos los campos obligatorios deben estar completos'
        ];
      }

      // Verificar disponibilidad del horario
      if (!Turno::verificarDisponibilidad($this->conn, $datos['fecha'], $datos['hora'])) {
        return [
          'status' => 'error',
          'message' => 'Ya existe un turno agendado para ese horario'
        ];
      }

      $turno = new Turno(
        $datos['cliente_id'],
        $datos['vehiculo_id'],
        $datos['fecha'],
        $datos['hora'],
        $datos['descripcion'] ?? null,
        $datos['estado'] ?? 'pendiente'
      );

      if ($turno->guardar($this->conn)) {
        return [
          'status' => 'success',
          'message' => 'Turno agendado correctamente',
          'id' => $turno->getId()
        ];
      } else {
        return [
          'status' => 'error',
          'message' => 'Error al guardar el turno en la base de datos'
        ];
      }
    } catch (Exception $e) {
      return [
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
      ];
    }
  }

  // Obtener todos los turnos
  public function listar()
  {
    try {
      $stmt = Turno::obtenerTodos($this->conn);
      return [
        'status' => 'success',
        'data' => $stmt->fetchAll()
      ];
    } catch (Exception $e) {
      return [
        'status' => 'error',
        'message' => 'Error al obtener turnos: ' . $e->getMessage()
      ];
    }
  }

  // Obtener turnos por fecha
  public function listarPorFecha($fecha)
  {
    try {
      $stmt = Turno::obtenerPorFecha($this->conn, $fecha);
      return [
        'status' => 'success',
        'data' => $stmt->fetchAll()
      ];
    } catch (Exception $e) {
      return [
        'status' => 'error',
        'message' => 'Error al obtener turnos: ' . $e->getMessage()
      ];
    }
  }

  // Obtener turnos por estado
  public function listarPorEstado($estado)
  {
    try {
      $stmt = Turno::obtenerPorEstado($this->conn, $estado);
      return [
        'status' => 'success',
        'data' => $stmt->fetchAll()
      ];
    } catch (Exception $e) {
      return [
        'status' => 'error',
        'message' => 'Error al obtener turnos: ' . $e->getMessage()
      ];
    }
  }

  // Obtener un turno específico
  public function obtenerPorId($id)
  {
    try {
      $turno = Turno::obtenerPorId($this->conn, $id);
      if ($turno) {
        return [
          'status' => 'success',
          'data' => $turno
        ];
      } else {
        return [
          'status' => 'error',
          'message' => 'Turno no encontrado'
        ];
      }
    } catch (Exception $e) {
      return [
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
      ];
    }
  }

  // Actualizar un turno
  public function actualizar($id, $datos)
  {
    try {
      // Validar datos requeridos
      if (
        empty($datos['cliente_id']) || empty($datos['vehiculo_id']) ||
        empty($datos['fecha']) || empty($datos['hora'])
      ) {
        return [
          'status' => 'error',
          'message' => 'Todos los campos obligatorios deben estar completos'
        ];
      }

      // Verificar disponibilidad del horario (excluyendo el turno actual)
      if (!Turno::verificarDisponibilidad($this->conn, $datos['fecha'], $datos['hora'], $id)) {
        return [
          'status' => 'error',
          'message' => 'Ya existe un turno agendado para ese horario'
        ];
      }

      $turno = new Turno();
      $turno->setId($id);
      $turno->setClienteId($datos['cliente_id']);
      $turno->setVehiculoId($datos['vehiculo_id']);
      $turno->setFecha($datos['fecha']);
      $turno->setHora($datos['hora']);
      $turno->setDescripcion($datos['descripcion'] ?? null);
      $turno->setEstado($datos['estado'] ?? 'pendiente');

      if ($turno->actualizar($this->conn)) {
        return [
          'status' => 'success',
          'message' => 'Turno actualizado correctamente'
        ];
      } else {
        return [
          'status' => 'error',
          'message' => 'Error al actualizar el turno'
        ];
      }
    } catch (Exception $e) {
      return [
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
      ];
    }
  }

  // Cambiar solo el estado de un turno
  public function cambiarEstado($id, $nuevo_estado)
  {
    try {
      // Validar que el estado sea válido
      $estados_validos = ['pendiente', 'confirmado', 'realizado', 'cancelado', 'no_asistio'];
      if (!in_array($nuevo_estado, $estados_validos)) {
        return [
          'status' => 'error',
          'message' => 'Estado no válido'
        ];
      }

      $turno = new Turno();
      $turno->setId($id);
      $turno->setEstado($nuevo_estado);

      if ($turno->cambiarEstado($this->conn)) {
        return [
          'status' => 'success',
          'message' => 'Estado actualizado correctamente'
        ];
      } else {
        return [
          'status' => 'error',
          'message' => 'Error al cambiar el estado'
        ];
      }
    } catch (Exception $e) {
      return [
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
      ];
    }
  }

  // Eliminar un turno
  public function eliminar($id)
  {
    try {
      if (Turno::eliminar($this->conn, $id)) {
        return [
          'status' => 'success',
          'message' => 'Turno eliminado correctamente'
        ];
      } else {
        return [
          'status' => 'error',
          'message' => 'Error al eliminar el turno'
        ];
      }
    } catch (Exception $e) {
      return [
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
      ];
    }
  }

  // Obtener vehículos de un cliente (útil para el select dinámico)
  public function obtenerVehiculosCliente($cliente_id)
  {
    try {
      $stmt = $this->conn->prepare("
                SELECT 
                    v.id, 
                    CONCAT(m.nombre, ' ', mo.nombre, ' (', v.patente, ')') as info 
                FROM vehiculos v 
                JOIN marcas m ON v.marca_id = m.id 
                JOIN modelos mo ON v.modelo_id = mo.id 
                WHERE v.cliente_id = ? 
                AND v.estado = 'activo'
                ORDER BY m.nombre, mo.nombre
            ");
      $stmt->execute([$cliente_id]);

      return [
        'status' => 'success',
        'data' => $stmt->fetchAll()
      ];
    } catch (Exception $e) {
      return [
        'status' => 'error',
        'message' => 'Error al obtener vehículos: ' . $e->getMessage()
      ];
    }
  }

  // Obtener próximos turnos (para dashboard)
  public function obtenerProximos($limite = 5)
  {
    try {
      $stmt = Turno::obtenerProximos($this->conn, $limite);
      return [
        'status' => 'success',
        'data' => $stmt->fetchAll()
      ];
    } catch (Exception $e) {
      return [
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
      ];
    }
  }
}

// Manejo de peticiones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['accion'])) {
  $controller = new TurnoController($conn);

  // POST: Crear, actualizar, cambiar estado, eliminar
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
      case 'crear':
        $resultado = $controller->crear($_POST);
        break;

      case 'actualizar':
        $resultado = $controller->actualizar($_POST['id'], $_POST);
        break;

      case 'cambiar_estado':
        $resultado = $controller->cambiarEstado($_POST['id'], $_POST['nuevo_estado']);
        break;

      case 'eliminar':
        $resultado = $controller->eliminar($_POST['id']);
        break;

      default:
        $resultado = ['status' => 'error', 'message' => 'Acción no válida'];
    }

    header('Content-Type: application/json');
    echo json_encode($resultado);
    exit;
  }

  // GET: Listar, obtener por ID, obtener vehículos
  if (isset($_GET['accion'])) {
    $accion = $_GET['accion'];

    switch ($accion) {
      case 'listar':
        $resultado = $controller->listar();
        break;

      case 'listar_por_fecha':
        $resultado = $controller->listarPorFecha($_GET['fecha']);
        break;

      case 'listar_por_estado':
        $resultado = $controller->listarPorEstado($_GET['estado']);
        break;

      case 'obtener':
        $resultado = $controller->obtenerPorId($_GET['id']);
        break;

      case 'obtener_vehiculos':
        $resultado = $controller->obtenerVehiculosCliente($_GET['cliente_id']);
        break;

      case 'proximos':
        $limite = $_GET['limite'] ?? 5;
        $resultado = $controller->obtenerProximos($limite);
        break;

      default:
        $resultado = ['status' => 'error', 'message' => 'Acción no válida'];
    }

    header('Content-Type: application/json');
    echo json_encode($resultado);
    exit;
  }
}
