<?php
require_once 'Personas.php';

class Clientes extends Personas {
  private $telefono;
  private $direccion;
  private $id_cliente;
  private $persona_id; // ID en la tabla personas (vinculado a la clase base)

  public function __construct($nombre = null, $apellido = null, $dni = null, $telefono = null, $direccion = null) {
    parent::__construct($nombre, $apellido, $dni);
    $this->telefono = $telefono;
    $this->direccion = $direccion;
  }

  // Persona_id getter/setter
  public function getPersonaId() {
    return $this->persona_id;
  }

  public function setPersonaId($persona_id) {
    $this->persona_id = $persona_id;
    // mantener también el id de la clase base (personas.id)
    $this->setId($persona_id);
  }

  // Getters
  public function getTelefono() {
    return $this->telefono;
  }

  public function getDireccion() {
    return $this->direccion;
  }

  public function getIdCliente() {
    return $this->id_cliente;
  }

  // Setters
  public function setTelefono($telefono) {
    $this->telefono = $telefono;
  }

  public function setDireccion($direccion) {
    $this->direccion = $direccion;
  }

  public function setIdCliente($id_cliente) {
    $this->id_cliente = $id_cliente;
  }

  // Método CRUD: Create
  public function guardar($conn) {
    $conn->beginTransaction();
      
    try {
      // Primero guardar la persona
      $sql_persona = "INSERT INTO personas (nombre, apellido, dni) VALUES (?, ?, ?)";
      $stmt_persona = $conn->prepare($sql_persona);
      
      if (!$stmt_persona->execute([$this->getNombre(), $this->getApellido(), $this->getDni()]))
        throw new Exception("Error al insertar persona");

      $persona_id = $conn->lastInsertId();

      // registrar persona_id en la instancia
      $this->persona_id = $persona_id;
      $this->setId($persona_id);

      // Luego guardar el cliente
      $sql_cliente = "INSERT INTO clientes (persona_id, telefono, direccion) VALUES (?, ?, ?)";
      $stmt_cliente = $conn->prepare($sql_cliente);
      
      if (!$stmt_cliente->execute([$persona_id, $this->telefono, $this->direccion]))
        throw new Exception("Error al insertar cliente");

      $this->id_cliente = $conn->lastInsertId();
      $conn->commit();

      return true;
    } catch (PDOException $e) {
      $conn->rollback();
      // Error 23000 = Duplicate entry
      if ($e->getCode() == 23000)
        throw new Exception("Ese DNI ya está presente en nuestra base de datos.");

      throw new Exception("Error al registrar el cliente: " . $e->getMessage());
    } catch (Exception $e) {
      $conn->rollback();
      throw $e;
    }
  }

  // Método CRUD: Read (obtener todos)
  public static function obtenerTodos($conn) {
    $sql = "SELECT c.id, c.persona_id, p.nombre, p.apellido, p.dni, c.telefono, c.direccion, c.estado
            FROM clientes c 
            INNER JOIN personas p ON c.persona_id = p.id 
            ORDER BY p.apellido, p.nombre";
    return $conn->query($sql);
  }

  // Método CRUD: Read (obtener uno por ID)
  public static function obtenerPorId($conn, $id) {
    $sql = "SELECT c.id, c.persona_id, p.nombre, p.apellido, p.dni, c.telefono, c.direccion, c.estado
            FROM clientes c 
            INNER JOIN personas p ON c.persona_id = p.id 
            WHERE c.id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$id]))
      return $stmt->fetch();

    return null;
  }

  // Método CRUD: Update
  public function actualizar($conn) {
    $conn->beginTransaction();
      
    try {
      // Asegurar que tenemos el persona_id correcto
      if (empty($this->persona_id)) {
        // intentar obtener persona_id a partir del id_cliente
        if (!empty($this->id_cliente)) {
            $stmt = $conn->prepare("SELECT persona_id FROM clientes WHERE id = ?");
            $stmt->execute([$this->id_cliente]);
            $res = $stmt->fetch();
            
            if ($res && isset($res['persona_id'])) {
              $this->persona_id = $res['persona_id'];
              $this->setId($this->persona_id);
            } else {
              // intentar obtener id_cliente a partir de personas.id si está disponible
              if (!empty($this->getId())) {
                $stmt2 = $conn->prepare("SELECT id FROM clientes WHERE persona_id = ?");
                $stmt2->execute([$this->getId()]);
                $r2 = $stmt2->fetch();

                if ($r2 && isset($r2['id']))
                  $this->id_cliente = $r2['id'];
              }
            }
          } else {
            // si no disponemos de id_cliente, intentar buscarlo por persona id
            if (!empty($this->getId())) {
              $stmt2 = $conn->prepare("SELECT id, persona_id FROM clientes WHERE persona_id = ?");
              $stmt2->execute([$this->getId()]);
              $r2 = $stmt2->fetch();
              if ($r2) {
                $this->id_cliente = $r2['id'];
                $this->persona_id = $r2['persona_id'];
              }
            }
          }
      }

      // Actualizar persona (usar persona_id)
      if (!empty($this->persona_id)) {
        $sql_persona = "UPDATE personas SET nombre = ?, apellido = ? WHERE id = ?";
        $stmt_persona = $conn->prepare($sql_persona);

        if (!$stmt_persona->execute([$this->nombre, $this->apellido, $this->persona_id])) 
          throw new Exception("Error al actualizar persona");
      }

      // Actualizar cliente
      $sql_cliente = "UPDATE clientes SET telefono = ?, direccion = ? WHERE id = ?";
      $stmt_cliente = $conn->prepare($sql_cliente);
      
      // Actualizar cliente (usar id_cliente)
      if (!empty($this->id_cliente)) {
        $sql_cliente = "UPDATE clientes SET telefono = ?, direccion = ? WHERE id = ?";
        $stmt_cliente = $conn->prepare($sql_cliente);

        if (!$stmt_cliente->execute([$this->telefono, $this->direccion, $this->id_cliente]))
          throw new Exception("Error al actualizar cliente");
      } else {
          // Si no tenemos id_cliente, intentar actualizar por persona_id
        if (!empty($this->persona_id)) {
            $sql_cliente = "UPDATE clientes SET telefono = ?, direccion = ? WHERE persona_id = ?";
            $stmt_cliente = $conn->prepare($sql_cliente);

            if (!$stmt_cliente->execute([$this->telefono, $this->direccion, $this->persona_id])) 
              throw new Exception("Error al actualizar cliente");
        }
      }
      
      $conn->commit();
      return true;
    } catch (Exception $e) {
      $conn->rollback();
      return false;
    }
  }

  // Método CRUD: Delete
  public static function eliminar($conn, $id) {
    // Al tener FOREIGN KEY con CASCADE, solo necesitamos eliminar la persona
    $sql = "DELETE p FROM personas p
            INNER JOIN clientes c ON p.id = c.persona_id
            WHERE c.id = ?";
    $stmt = $conn->prepare($sql);
    
    return $stmt->execute([$id]);
  }

  // Cambiar estado del cliente
  public static function cambiarEstado($conn, $id, $estado) {
    $sql = "UPDATE clientes SET estado = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    return $stmt->execute([$estado, $id]);
  }
}
?>
