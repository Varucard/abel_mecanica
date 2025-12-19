<?php
require_once '../includes/config_database.php';
require_once '../clases/Repuestos.php';

// Verificar acción
$action = $_GET['action'] ?? $_POST['action'] ?? null;

if (!$action) {
  header("Location: ../views/registrar_repuesto.php");
  exit;
}

try {
  switch ($action) {
    case 'nuevo':
      header("Location: ../views/registrar_repuesto.php");
      exit;

    case 'guardar':
      $nombre = $_POST['nombre'] ?? '';
      $descripcion = $_POST['descripcion'] ?? '';
      $precio = $_POST['precio'] ?? 0.0;

      if ($nombre) {
        $repuesto = new Repuestos($nombre, $descripcion, $precio);
        if ($repuesto->guardar($conn)) {
          header("Location: ../views/registrar_repuesto.php?success=Repuesto creado correctamente");
        } else {
          header("Location: ../views/registrar_repuesto.php?error=Error al crear el repuesto");
        }
      }
      exit;

    case 'editar':
      $id = $_GET['id'] ?? null;
      if ($id) {
        header("Location: ../views/registrar_repuesto.php?id=$id");
        exit;
      }
      break;

    case 'actualizar':
      $id = $_POST['id'] ?? null;
      $nombre = $_POST['nombre'] ?? '';
      $descripcion = $_POST['descripcion'] ?? '';
      $precio = $_POST['precio'] ?? 0.0;

      if ($id && $nombre) {
        $repuesto = new Repuestos($nombre, $descripcion, $precio);
        $repuesto->setId($id);
        if ($repuesto->actualizar($conn)) {
          header("Location: ../views/registrar_repuesto.php?success=Repuesto actualizado correctamente");
        } else {
          header("Location: ../views/registrar_repuesto.php?error=Error al actualizar el repuesto");
        }
      }
      exit;

    case 'delete':
      $id = $_GET['id'] ?? null;
      if ($id && Repuestos::eliminar($conn, $id)) {
        header("Location: ../views/registrar_repuesto.php?success=Repuesto eliminado correctamente");
      } else {
        header("Location: ../views/registrar_repuesto.php?error=Error al eliminar repuesto");
      }
      exit;

    default:
      header("Location: ../views/registrar_repuesto.php");
      exit;
  }
} catch (Exception $e) {
  header("Location: ../views/registrar_repuesto.php?error=" . urlencode($e->getMessage()));
  exit;
}
