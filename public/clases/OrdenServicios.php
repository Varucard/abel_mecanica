<?php

class OrdenServicios {

  private $id;
  private $orden_id;
  private $servicio_id;
  private $repuesto_id;
  private $costo;

  public function __construct($orden_id = null, $servicio_id = null, $repuesto_id = null, $costo = null) {
    $this->orden_id    = $orden_id;
    $this->servicio_id = $servicio_id;
    $this->repuesto_id = $repuesto_id;
    $this->costo       = $costo;
  }

  /* ===================== GETTERS ===================== */
  public function getId()         { return $this->id; }
  public function getOrdenId()    { return $this->orden_id; }
  public function getServicioId() { return $this->servicio_id; }
  public function getRepuestoId() { return $this->repuesto_id; }
  public function getCosto()      { return $this->costo; }

  /* ===================== SETTERS ===================== */
  public function setId($id)                    { $this->id = $id; }
  public function setOrdenId($orden_id)         { $this->orden_id = $orden_id; }
  public function setServicioId($servicio_id)   { $this->servicio_id = $servicio_id; }
  public function setRepuestoId($repuesto_id)   { $this->repuesto_id = $repuesto_id; }
  public function setCosto($costo)              { $this->costo = $costo; }

  /* ===================== GUARDAR ===================== */
  public function guardar() {
    global $conn;

    try {
      $sql = "
        INSERT INTO ordenes_servicios
          (orden_id, servicio_id, repuesto_id, costo)
        VALUES (?, ?, ?, ?)
      ";

      $stmt = $conn->prepare($sql);

      if ($stmt->execute([
        $this->orden_id,
        $this->servicio_id,
        $this->repuesto_id,
        $this->costo
      ])) {
        $this->id = $conn->lastInsertId();
        return true;
      }

      return false;

    } catch (PDOException $e) {
      throw new Exception(
        "Error al agregar servicio/repuesto a la orden: " . $e->getMessage()
      );
    }
  }

  /* ===================== OBTENER ÓRDENES ===================== */
  public static function obtenerTodas() {
    global $conn;

    $sql = "
      SELECT 
        o.id,
        o.total AS costo,
        o.fecha_realizado,
        o.estado,
        CONCAT(p.apellido, ', ', p.nombre) AS cliente,
        CONCAT(v.patente, ' - ', ma.nombre, ' ', mo.nombre) AS vehiculo,

        GROUP_CONCAT(DISTINCT s.nombre ORDER BY s.nombre SEPARATOR ', ') AS servicios,
        GROUP_CONCAT(DISTINCT r.nombre ORDER BY r.nombre SEPARATOR ', ') AS repuestos

      FROM ordenes o
      INNER JOIN vehiculos v ON o.vehiculo_id = v.id
      INNER JOIN clientes c ON v.cliente_id = c.id
      INNER JOIN personas p ON c.persona_id = p.id
      INNER JOIN marcas ma  ON v.marca_id = ma.id
      INNER JOIN modelos mo ON v.modelo_id = mo.id
      INNER JOIN ordenes_servicios os ON os.orden_id = o.id

      LEFT JOIN servicios s ON os.servicio_id = s.id
      LEFT JOIN repuestos r ON os.repuesto_id = r.id

      GROUP BY o.id
      ORDER BY o.fecha_realizado DESC, o.id DESC
    ";

    return $conn->query($sql);
  }

  public static function verOrdenes() {
    return self::obtenerTodas();
  }

  /* ===================== SERVICIOS ===================== */
  public static function obtenerServicios() {
    global $conn;
    $sql = "SELECT * FROM servicios WHERE estado = 'activo' AND id != 1 ORDER BY nombre";
    return $conn->query($sql);
  }

  public static function obtenerRepuestos() {
    global $conn;
    $sql = "SELECT * FROM repuestos ORDER BY nombre";
    return $conn->query($sql);
  }

  /* ===================== CAMBIAR ESTADO ===================== */
  public static function cambiarEstado($id, $estado) {
    global $conn;

    try {
      if ($estado === 'finalizado') {
        $sql = "UPDATE ordenes SET estado = ?, fecha_finalizado = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([$estado, date('Y-m-d'), $id]);
      } else {
        $sql = "UPDATE ordenes SET estado = ?, fecha_finalizado = NULL WHERE id = ?";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([$estado, $id]);
      }
    } catch (Exception $e) {
      return false;
    }
  }
}
