<?php

class Marcas {
  private $id;
  private $nombre;

  public function __construct($nombre = null) {
    $this->nombre = $nombre;
  }

  // Getters y setters
  public function getId() { return $this->id; }
  public function getNombre() { return $this->nombre; }
  public function setId($id) { $this->id = $id; }
  public function setNombre($nombre) { $this->nombre = $nombre; }

  // CRUD: Crear
  public function guardar($conn) {
    $sql = "INSERT INTO marcas (nombre) VALUES (?)";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$this->nombre])) {
      $this->id = $conn->lastInsertId();
      return true;
    }
    return false;
  }

  // Leer todos
  public static function obtenerTodos($conn) {
    $sql = "SELECT * FROM marcas ORDER BY nombre";
    return $conn->query($sql);
  }

  // Leer por ID
  public static function obtenerPorId($conn, $id) {
    $sql = "SELECT * FROM marcas WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
  }

  // Actualizar
  public function actualizar($conn) {
    $sql = "UPDATE marcas SET nombre = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$this->nombre, $this->id]);
  }

  // Eliminar
  public static function eliminar($conn, $id) {
    $sql = "DELETE FROM marcas WHERE id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$id]);
  }
}
