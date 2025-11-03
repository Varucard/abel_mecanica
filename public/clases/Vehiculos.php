<?php

class Vehiculos {
  private $id;
  private $marca_id;
  private $modelo_id;
  private $anio;
  private $patente;
  private $cliente_id;

  public function __construct($marca_id = null, $modelo_id = null, $anio = null, $patente = null, $cliente_id = null) {
    $this->marca_id = $marca_id;
    $this->modelo_id = $modelo_id;
    $this->anio = $anio;
    $this->patente = $patente;
    $this->cliente_id = $cliente_id;
  }

  // Getters
  public function getId() {
    return $this->id;
  }

  public function getMarcaId() {
    return $this->marca_id;
  }

  public function getModeloId() {
    return $this->modelo_id;
  }

  public function getAnio() {
    return $this->anio;
  }

  public function getPatente() {
    return $this->patente;
  }

  public function getClienteId() {
    return $this->cliente_id;
  }

  // Setters
  public function setId($id) {
    $this->id = $id;
  }

  public function setMarcaId($marca_id) {
    $this->marca_id = $marca_id;
  }

  public function setModeloId($modelo_id) {
    $this->modelo_id = $modelo_id;
  }

  public function setAnio($anio) {
    $this->anio = $anio;
  }

  public function setPatente($patente) {
    $this->patente = $patente;
  }

  public function setClienteId($cliente_id) {
    $this->cliente_id = $cliente_id;
  }

  public function guardar() {
    global $conn;
    try {
      $sql = "INSERT INTO vehiculos (cliente_id, marca_id, modelo_id, anio, patente) VALUES (?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($sql);
      
      if ($stmt->execute([$this->cliente_id, $this->marca_id, $this->modelo_id, $this->anio, $this->patente])) {
        $this->id = $conn->lastInsertId();
        return true;
      }
      return false;
    } catch (PDOException $e) {
      // Error 1062 = Duplicate entry
      if ($e->getCode() == 23000) {
        throw new Exception("La patente ya corresponde a otro vehículo en el sistema.");
      }
      throw new Exception("Error al registrar el vehículo: " . $e->getMessage());
    }
  }

  public static function obtenerTodos() {
    global $conn;
    $sql = "SELECT v.id, v.patente, v.anio, CONCAT(p.nombre, ' ', p.apellido) AS cliente,
            m.nombre AS marca, mo.nombre AS modelo
            FROM vehiculos v 
            INNER JOIN clientes c ON v.cliente_id = c.id 
            INNER JOIN personas p ON c.persona_id = p.id
            INNER JOIN marcas m ON v.marca_id = m.id
            INNER JOIN modelos mo ON v.modelo_id = mo.id
            WHERE v.estado = 'activo'
            ORDER BY v.id DESC";
    return $conn->query($sql);
  }

  public static function obtenerPorCliente($cliente_id) {
    global $conn;
    $sql = "SELECT v.id, v.patente, v.anio, m.nombre AS marca, mo.nombre AS modelo
            FROM vehiculos v 
            INNER JOIN marcas m ON v.marca_id = m.id
            INNER JOIN modelos mo ON v.modelo_id = mo.id
            WHERE v.cliente_id = ? AND v.estado = 'activo'";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$cliente_id]))
      return $stmt;

    return null;
  }

  public static function contarPorCliente($cliente_id) {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM vehiculos WHERE cliente_id = ? AND estado = 'activo'";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$cliente_id])) {
      $result = $stmt->fetch();
      return $result['total'];
    }
    return 0;
  }

  public static function obtenerParaSelect() {
    global $conn;
    $sql = "SELECT v.id, v.patente, CONCAT(p.nombre, ' ', p.apellido) AS cliente,
            m.nombre AS marca, mo.nombre AS modelo
            FROM vehiculos v 
            INNER JOIN clientes c ON v.cliente_id = c.id 
            INNER JOIN personas p ON c.persona_id = p.id
            INNER JOIN marcas m ON v.marca_id = m.id
            INNER JOIN modelos mo ON v.modelo_id = mo.id
            WHERE v.estado = 'activo'
            ORDER BY v.id DESC";
    return $conn->query($sql);
  }

  public static function obtenerMarcas() {
    global $conn;
    $sql = "SELECT * FROM marcas ORDER BY nombre";
    return $conn->query($sql);
  }

  public static function obtenerModelosPorMarca($marca_id) {
    global $conn;
    $sql = "SELECT * FROM modelos WHERE marca_id = ? ORDER BY nombre";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$marca_id]))
      return $stmt;

    return null;
  }

  public static function obtenerModelosPorMarcaJSON($marca_id) {
    global $conn;
    $sql = "SELECT * FROM modelos WHERE marca_id = ? ORDER BY nombre";
    $stmt = $conn->prepare($sql);
    
    $result = [];
    if ($stmt->execute([$marca_id])) {
      while ($modelo = $stmt->fetch()) {
        $result[] = $modelo;
      }
    }
    return $result;
  }
}
?>
