<?php

class Turno
{
  private $id;
  private $cliente_id;
  private $vehiculo_id;
  private $fecha;
  private $hora;
  private $descripcion;
  private $estado;
  private $created_at;
  private $updated_at;

  public function __construct($cliente_id = null, $vehiculo_id = null, $fecha = null, $hora = null, $descripcion = null, $estado = 'pendiente')
  {
    $this->cliente_id = $cliente_id;
    $this->vehiculo_id = $vehiculo_id;
    $this->fecha = $fecha;
    $this->hora = $hora;
    $this->descripcion = $descripcion;
    $this->estado = $estado;
  }

  // Getters
  public function getId()
  {
    return $this->id;
  }
  public function getClienteId()
  {
    return $this->cliente_id;
  }
  public function getVehiculoId()
  {
    return $this->vehiculo_id;
  }
  public function getFecha()
  {
    return $this->fecha;
  }
  public function getHora()
  {
    return $this->hora;
  }
  public function getDescripcion()
  {
    return $this->descripcion;
  }
  public function getEstado()
  {
    return $this->estado;
  }
  public function getCreatedAt()
  {
    return $this->created_at;
  }
  public function getUpdatedAt()
  {
    return $this->updated_at;
  }

  // Setters
  public function setId($id)
  {
    $this->id = $id;
  }
  public function setClienteId($cliente_id)
  {
    $this->cliente_id = $cliente_id;
  }
  public function setVehiculoId($vehiculo_id)
  {
    $this->vehiculo_id = $vehiculo_id;
  }
  public function setFecha($fecha)
  {
    $this->fecha = $fecha;
  }
  public function setHora($hora)
  {
    $this->hora = $hora;
  }
  public function setDescripcion($descripcion)
  {
    $this->descripcion = $descripcion;
  }
  public function setEstado($estado)
  {
    $this->estado = $estado;
  }

  // CRUD: Crear
  public function guardar($conn)
  {
    $sql = "INSERT INTO turnos (cliente_id, vehiculo_id, fecha, hora, descripcion, estado) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$this->cliente_id, $this->vehiculo_id, $this->fecha, $this->hora, $this->descripcion, $this->estado])) {
      $this->id = $conn->lastInsertId();
      return true;
    }
    return false;
  }

  // Leer todos (con datos de cliente y vehículo)
  public static function obtenerTodos($conn)
  {
    $sql = "SELECT 
              t.id,
              t.cliente_id,
              t.vehiculo_id,
              t.fecha,
              t.hora,
              t.descripcion,
              t.estado,
              t.created_at,
              t.updated_at,
              CONCAT(p.nombre, ' ', p.apellido) as cliente_nombre,
              CONCAT(m.nombre, ' ', mo.nombre, ' (', v.patente, ')') as vehiculo_info
            FROM turnos t
            INNER JOIN clientes c ON t.cliente_id = c.id
            INNER JOIN personas p ON c.persona_id = p.id
            INNER JOIN vehiculos v ON t.vehiculo_id = v.id
            INNER JOIN marcas m ON v.marca_id = m.id
            INNER JOIN modelos mo ON v.modelo_id = mo.id
            ORDER BY t.fecha DESC, t.hora DESC";
    return $conn->query($sql);
  }

  // Leer por ID
  public static function obtenerPorId($conn, $id)
  {
    $sql = "SELECT * FROM turnos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
  }

  // Leer por fecha
  public static function obtenerPorFecha($conn, $fecha)
  {
    $sql = "SELECT 
              t.id,
              t.cliente_id,
              t.vehiculo_id,
              t.fecha,
              t.hora,
              t.descripcion,
              t.estado,
              CONCAT(p.nombre, ' ', p.apellido) as cliente_nombre,
              CONCAT(m.nombre, ' ', mo.nombre, ' (', v.patente, ')') as vehiculo_info
            FROM turnos t
            INNER JOIN clientes c ON t.cliente_id = c.id
            INNER JOIN personas p ON c.persona_id = p.id
            INNER JOIN vehiculos v ON t.vehiculo_id = v.id
            INNER JOIN marcas m ON v.marca_id = m.id
            INNER JOIN modelos mo ON v.modelo_id = mo.id
            WHERE t.fecha = ?
            ORDER BY t.hora ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$fecha]);
    return $stmt;
  }

  // Leer por estado
  public static function obtenerPorEstado($conn, $estado)
  {
    $sql = "SELECT 
              t.id,
              t.cliente_id,
              t.vehiculo_id,
              t.fecha,
              t.hora,
              t.descripcion,
              t.estado,
              CONCAT(p.nombre, ' ', p.apellido) as cliente_nombre,
              CONCAT(m.nombre, ' ', mo.nombre, ' (', v.patente, ')') as vehiculo_info
            FROM turnos t
            INNER JOIN clientes c ON t.cliente_id = c.id
            INNER JOIN personas p ON c.persona_id = p.id
            INNER JOIN vehiculos v ON t.vehiculo_id = v.id
            INNER JOIN marcas m ON v.marca_id = m.id
            INNER JOIN modelos mo ON v.modelo_id = mo.id
            WHERE t.estado = ?
            ORDER BY t.fecha ASC, t.hora ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$estado]);
    return $stmt;
  }

  // Actualizar
  public function actualizar($conn)
  {
    $sql = "UPDATE turnos 
            SET cliente_id = ?, vehiculo_id = ?, fecha = ?, hora = ?, descripcion = ?, estado = ? 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$this->cliente_id, $this->vehiculo_id, $this->fecha, $this->hora, $this->descripcion, $this->estado, $this->id]);
  }

  // Cambiar solo el estado
  public function cambiarEstado($conn)
  {
    $sql = "UPDATE turnos SET estado = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$this->estado, $this->id]);
  }

  // Eliminar
  public static function eliminar($conn, $id)
  {
    $sql = "DELETE FROM turnos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$id]);
  }

  // Verificar disponibilidad de horario
  public static function verificarDisponibilidad($conn, $fecha, $hora, $turno_id = null)
  {
    $sql = "SELECT COUNT(*) as total 
            FROM turnos
            WHERE fecha = ? 
            AND hora = ? 
            AND estado NOT IN ('cancelado', 'no_asistio')";

    if ($turno_id) {
      $sql .= " AND id != ?";
      $stmt = $conn->prepare($sql);
      $stmt->execute([$fecha, $hora, $turno_id]);
    } else {
      $stmt = $conn->prepare($sql);
      $stmt->execute([$fecha, $hora]);
    }

    $row = $stmt->fetch();
    return $row['total'] == 0; // true si está disponible
  }

  // Obtener próximos turnos (para dashboard)
  public static function obtenerProximos($conn, $limite = 5)
  {
    $sql = "SELECT 
              t.id,
              t.fecha,
              t.hora,
              t.estado,
              CONCAT(p.nombre, ' ', p.apellido) as cliente_nombre,
              CONCAT(m.nombre, ' ', mo.nombre) as vehiculo_info
            FROM turnos t
            INNER JOIN clientes c ON t.cliente_id = c.id
            INNER JOIN personas p ON c.persona_id = p.id
            INNER JOIN vehiculos v ON t.vehiculo_id = v.id
            INNER JOIN marcas m ON v.marca_id = m.id
            INNER JOIN modelos mo ON v.modelo_id = mo.id
            WHERE t.fecha >= CURDATE()
            AND t.estado IN ('pendiente', 'confirmado')
            ORDER BY t.fecha ASC, t.hora ASC
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$limite]);
    return $stmt;
  }
}
