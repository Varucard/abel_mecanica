<?php

class Servicios
{
  private $id;
  private $nombre;
  private $descripcion;
  private $precio_base;

  public function __construct($nombre = null, $descripcion = null, $precio_base = 0.0)
  {
    $this->nombre = $nombre;
    $this->descripcion = $descripcion;
    $this->precio_base = $precio_base;
  }

  // Getters y setters
  public function getId()
  {
    return $this->id;
  }
  public function getNombre()
  {
    return $this->nombre;
  }
  public function getDescripcion()
  {
    return $this->descripcion;
  }
  public function getPrecioBase()
  {
    return $this->precio_base;
  }

  public function setId($id)
  {
    $this->id = $id;
  }
  public function setNombre($nombre)
  {
    $this->nombre = $nombre;
  }
  public function setDescripcion($descripcion)
  {
    $this->descripcion = $descripcion;
  }
  public function setPrecioBase($precio_base)
  {
    $this->precio_base = $precio_base;
  }

  // CRUD: Crear
  public function guardar($conn)
  {
    $sql = "INSERT INTO servicios (nombre, descripcion, precio_base) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$this->nombre, $this->descripcion, $this->precio_base])) {
      $this->id = $conn->lastInsertId();
      return true;
    }
    return false;
  }

  // Leer todos
  public static function obtenerTodos($conn)
  {
    $sql = "SELECT * FROM servicios WHERE id != 1 ORDER BY nombre";
    return $conn->query($sql);
  }

  // Leer por ID
  public static function obtenerPorId($conn, $id)
  {
    $sql = "SELECT * FROM servicios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
  }

  // Actualizar
  public function actualizar($conn)
  {
    $sql = "UPDATE servicios SET nombre = ?, descripcion = ?, precio_base = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$this->nombre, $this->descripcion, $this->precio_base, $this->id]);
  }

  // Eliminar
  public static function eliminar($conn, $id)
  {
    $sql = "DELETE FROM servicios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$id]);
  }
}
