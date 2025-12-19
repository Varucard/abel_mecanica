<?php
require_once '../includes/config_database.php';
require_once '../clases/Servicios.php';

// Verificar acción
$action = $_GET['action'] ?? $_POST['action'] ?? null;

if (!$action) {
  header("Location: ../views/registrar_servicio.php");
  exit;
}

try {
  switch ($action) {
    case 'nuevo':
      header("Location: ../views/registrar_servicio.php");
      exit;

    case 'guardar':
      $nombre = $_POST['nombre'] ?? '';
      $descripcion = $_POST['descripcion'] ?? '';
      $precio_base = $_POST['precio_base'] ?? 0.0;

      if ($nombre) {
        $servicio = new Servicios($nombre, $descripcion, $precio_base);
        if ($servicio->guardar($conn)) {
          header("Location: ../views/registrar_servicio.php?success=Servicio creado correctamente");
        } else {
          header("Location: ../views/registrar_servicio.php?error=Error al crear el servicio");
        }
      }
      exit;

    case 'editar':
      $id = $_GET['id'] ?? null;
      if ($id) {
        header("Location: ../views/registrar_servicio.php?id=$id");
        exit;
      }
      break;

    case 'actualizar':
      $id = $_POST['id'] ?? null;
      $nombre = $_POST['nombre'] ?? '';
      $descripcion = $_POST['descripcion'] ?? '';
      $precio_base = $_POST['precio_base'] ?? 0.0;

      if ($id && $nombre) {
        $servicio = new Servicios($nombre, $descripcion, $precio_base);
        $servicio->setId($id);
        if ($servicio->actualizar($conn)) {
          header("Location: ../views/registrar_servicio.php?success=Servicio actualizado correctamente");
        } else {
          header("Location: ../views/registrar_servicio.php?error=Error al actualizar el servicio");
        }
      }
      exit;

    case 'delete':
      $id = $_GET['id'] ?? null;
      if ($id && Servicios::eliminar($conn, $id)) {
        header("Location: ../views/registrar_servicio.php?success=Servicio eliminado correctamente");
      } else {
        header("Location: ../views/registrar_servicio.php?error=Error al eliminar servicio");
      }
      exit;

    default:
      header("Location: ../views/registrar_servicio.php");
      exit;
  }
} catch (Exception $e) {
  header("Location: ../views/registrar_servicio.php?error=" . urlencode($e->getMessage()));
  exit;
}
