<?php

class Repuestos
{
  private $id;
  private $nombre;
  private $descripcion;
  private $precio;

  public function __construct($nombre = null, $descripcion = null, $precio = 0.0)
  {
    $this->nombre = $nombre;
    $this->descripcion = $descripcion;
    $this->precio = $precio;
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
  public function getPrecio()
  {
    return $this->precio;
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
  public function setPrecio($precio)
  {
    $this->precio = $precio;
  }

  // CRUD: Crear
  public function guardar($conn)
  {
    $sql = "INSERT INTO repuestos (nombre, descripcion, precio) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$this->nombre, $this->descripcion, $this->precio])) {
      $this->id = $conn->lastInsertId();
      return true;
    }
    return false;
  }

  // Leer todos
  public static function obtenerTodos($conn)
  {
    $sql = "SELECT * FROM repuestos ORDER BY nombre";
    return $conn->query($sql);
  }

  // Leer por ID
  public static function obtenerPorId($conn, $id)
  {
    $sql = "SELECT * FROM repuestos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
  }

  // Actualizar
  public function actualizar($conn)
  {
    $sql = "UPDATE repuestos SET nombre = ?, descripcion = ?, precio = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$this->nombre, $this->descripcion, $this->precio, $this->id]);
  }

  // Eliminar
  public static function eliminar($conn, $id)
  {
    $sql = "DELETE FROM repuestos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$id]);
  }
}
