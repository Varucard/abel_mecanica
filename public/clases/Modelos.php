<?php
require_once 'Marcas.php';

class Modelos {
  private $id;
  private $marca_id;
  private $nombre;

  public function __construct($marca_id = null, $nombre = null) {
    $this->marca_id = $marca_id;
    $this->nombre = $nombre;
  }

  // Getters y setters
  public function getId() { return $this->id; }
  public function getMarcaId() { return $this->marca_id; }
  public function getNombre() { return $this->nombre; }
  public function setId($id) { $this->id = $id; }
  public function setMarcaId($marca_id) { $this->marca_id = $marca_id; }
  public function setNombre($nombre) { $this->nombre = $nombre; }

  // CRUD: Crear
  public function guardar($conn) {
    $sql = "INSERT INTO modelos (marca_id, nombre) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$this->marca_id, $this->nombre])) {
      $this->id = $conn->lastInsertId();
      return true;
    }
    return false;
  }

  // Leer todos
  public static function obtenerTodos($conn) {
    $sql = "SELECT m.id, m.nombre, m.marca_id, ma.nombre AS marca_nombre 
            FROM modelos m 
            INNER JOIN marcas ma ON m.marca_id = ma.id 
            ORDER BY ma.nombre, m.nombre";
    return $conn->query($sql);
  }

  // Leer por ID
  public static function obtenerPorId($conn, $id) {
    $sql = "SELECT * FROM modelos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
  }

  // Actualizar
  public function actualizar($conn) {
    $sql = "UPDATE modelos SET nombre = ?, marca_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$this->nombre, $this->marca_id, $this->id]);
  }

  // Eliminar
  public static function eliminar($conn, $id) {
    $sql = "DELETE FROM modelos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$id]);
  }
}
