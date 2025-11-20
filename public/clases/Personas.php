<?php

class Personas {
  protected $id;
  protected $nombre;
  protected $apellido;
  protected $dni;

  public function __construct($nombre = null, $apellido = null, $dni = null) {
    $this->nombre = $nombre;
    $this->apellido = $apellido;
    $this->dni = $dni;
  }

  // Getters
  public function getId() {
    return $this->id;
  }

  public function getNombre() {
    return $this->nombre;
  }

  public function getApellido() {
    return $this->apellido;
  }

  public function getDni() {
    return $this->dni;
  }

  // Setters
  public function setId($id) {
    $this->id = $id;
  }

  public function setNombre($nombre) {
    $this->nombre = $nombre;
  }

  public function setApellido($apellido) {
    $this->apellido = $apellido;
  }

  public function setDni($dni) {
    $this->dni = $dni;
  }

  public function guardar($conn) {
    $sql = "INSERT INTO personas (nombre, apellido, dni) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$this->nombre, $this->apellido, $this->dni])) {
      $this->id = $conn->lastInsertId();
      return true;
    }
    
    return false;
  }

  public static function buscarPorDni($conn, $dni) {
    $sql = "SELECT * FROM personas WHERE dni = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$dni]))
      return $stmt->fetch();
    
    return null;
  }
}
