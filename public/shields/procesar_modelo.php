<?php
require_once '../includes/config_database.php';
require_once '../clases/Modelos.php';

$action = $_GET['action'] ?? $_POST['action'] ?? null;

if (!$action) {
  header("Location: ../views/registrar_modelos.php");
  exit;
}

try {
  switch ($action) {
    case 'guardar':
      $marca_id = $_POST['marca_id'] ?? null;
      $nombre = trim($_POST['nombre'] ?? '');
      if ($marca_id && $nombre) {
        $modelo = new Modelos($marca_id, $nombre);
        if ($modelo->guardar($conn)) {
          header("Location: ../views/registrar_modelos.php?success=Modelo registrado correctamente");
        } else {
          header("Location: ../views/registrar_modelos.php?error=Error al registrar modelo");
        }
      }
      exit;

    case 'actualizar':
      $id = $_POST['id'] ?? null;
      $marca_id = $_POST['marca_id'] ?? null;
      $nombre = trim($_POST['nombre'] ?? '');
      if ($id && $marca_id && $nombre) {
        $modelo = new Modelos($marca_id, $nombre);
        $modelo->setId($id);
        if ($modelo->actualizar($conn)) {
          header("Location: ../views/registrar_modelos.php?success=Modelo actualizado correctamente");
        } else {
          header("Location: ../views/registrar_modelos.php?error=Error al actualizar modelo");
        }
      }
      exit;

    case 'delete':
      $id = $_GET['id'] ?? null;
      if ($id && Modelos::eliminar($conn, $id)) {
        header("Location: ../views/registrar_modelos.php?success=Modelo eliminado correctamente");
      } else {
        header("Location: ../views/registrar_modelos.php?error=Error al eliminar modelo");
      }
      exit;

    default:
      header("Location: ../views/registrar_modelos.php");
      exit;
  }
} catch (Exception $e) {
  header("Location: ../views/registrar_modelos.php?error=" . urlencode($e->getMessage()));
  exit;
}
