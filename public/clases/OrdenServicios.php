<?php

class OrdenServicios {
  private $id;
  private $vehiculo_id;
  private $servicio_id;
  private $costo;
  private $fecha_realizado;
  private $estado;

  public function __construct($vehiculo_id = null, $servicio_id = null, $costo = null, $fecha_realizado = null, $estado = 'pendiente') {
    $this->vehiculo_id = $vehiculo_id;
    $this->servicio_id = $servicio_id;
    $this->costo = $costo;
    $this->fecha_realizado = $fecha_realizado;
    $this->estado = $estado;
  }

  // Getters
  public function getId() {
    return $this->id;
  }

  public function getVehiculoId() {
    return $this->vehiculo_id;
  }

  public function getServicioId() {
    return $this->servicio_id;
  }

  public function getCosto() {
    return $this->costo;
  }

  public function getFechaRealizado() {
    return $this->fecha_realizado;
  }

  public function getEstado() {
    return $this->estado;
  }

  // Setters
  public function setId($id) {
    $this->id = $id;
  }

  public function setVehiculoId($vehiculo_id) {
    $this->vehiculo_id = $vehiculo_id;
  }

  public function setServicioId($servicio_id) {
    $this->servicio_id = $servicio_id;
  }

  public function setCosto($costo) {
    $this->costo = $costo;
  }

  public function setFechaRealizado($fecha_realizado) {
    $this->fecha_realizado = $fecha_realizado;
  }

  public function setEstado($estado) {
    $this->estado = $estado;
  }

  public function guardar() {
    global $conn;
    try {
      $sql = "INSERT INTO ordenes (vehiculo_id, servicio_id, costo, fecha_realizado, estado) VALUES (?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($sql);
      
      if ($stmt->execute([$this->vehiculo_id, $this->servicio_id, $this->costo, $this->fecha_realizado, $this->estado])) {
        $this->id = $conn->lastInsertId();
        return true;
      }
      return false;
    } catch (PDOException $e) {
      throw new Exception("Error al crear la orden: " . $e->getMessage());
    }
  }

  public static function verOrdenes() {
    global $conn;
    $sql = "SELECT * FROM vw_ordenes_completas ORDER BY created_at DESC";
    return $conn->query($sql);
  }

  public static function obtenerServicios() {
    global $conn;
    $sql = "SELECT * FROM servicios WHERE estado = 'activo' ORDER BY nombre";
    return $conn->query($sql);
  }

  public static function cambiarEstado($id, $estado) {
    global $conn;
    try {
      if ($estado === 'finalizado') {
        // establecer fecha_finalizado con la fecha actual
        $sql = "UPDATE ordenes SET estado = ?, fecha_finalizado = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $fecha = date('Y-m-d');
        return $stmt->execute([$estado, $fecha, $id]);
      } else {
        // si se cambia a cualquier otro estado, limpiar fecha_finalizado
        $sql = "UPDATE ordenes SET estado = ?, fecha_finalizado = NULL WHERE id = ?";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([$estado, $id]);
      }
    } catch (Exception $e) {
      return false;
    }
  }
}
?>
