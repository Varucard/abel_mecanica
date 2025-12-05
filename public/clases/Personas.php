<?php

class Personas {
  protected $id;
  protected $nombre;
  protected $apellido;
  protected $dni;
  protected $email; // puede ser null

  public function __construct($nombre = null, $apellido = null, $dni = null, $email = null) {
    $this->nombre   = $nombre;
    $this->apellido = $apellido;
    $this->dni      = $dni;
    $this->email    = $email; // null por defecto
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

  public function getEmail() {
    return $this->email;
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

  public function setEmail($email) {
    $this->email = $email;
  }

  public function guardar($conn) {
    $sql = "INSERT INTO personas (nombre, apellido, dni, email) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Si viene string vacío, lo guardamos como NULL
    $email = $this->email !== '' ? $this->email : null;

    if ($stmt->execute([$this->nombre, $this->apellido, $this->dni, $email])) {
      $this->id = $conn->lastInsertId();
      return true;
    }

    return false;
  }

  public static function buscarPorDni($conn, $dni) {
    $sql = "SELECT * FROM personas WHERE dni = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt->execute([$dni])) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    return null;
  }

  public static function buscarPorEmail($conn, $email) {
    $sql = "SELECT * FROM personas WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt->execute([$email])) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    return null;
  }
}
