<?php
require_once '../includes/config_database.php';
require_once '../clases/Marcas.php';

$action = $_GET['action'] ?? $_POST['action'] ?? null;

if (!$action) {
  header("Location: ../views/registrar_marcas.php");
  exit;
}

try {
  switch ($action) {
    case 'guardar':
      $nombre = trim($_POST['nombre'] ?? '');
      if ($nombre) {
        $marca = new Marcas($nombre);
        if ($marca->guardar($conn)) {
          header("Location: ../views/registrar_marcas.php?success=Marca registrada correctamente");
        } else {
          header("Location: ../views/registrar_marcas.php?error=Error al registrar marca");
        }
      }
      exit;

    case 'actualizar':
      $id = $_POST['id'] ?? null;
      $nombre = trim($_POST['nombre'] ?? '');
      if ($id && $nombre) {
        $marca = new Marcas($nombre);
        $marca->setId($id);
        if ($marca->actualizar($conn)) {
          header("Location: ../views/registrar_marcas.php?success=Marca actualizada correctamente");
        } else {
          header("Location: ../views/registrar_marcas.php?error=Error al actualizar marca");
        }
      }
      exit;

    case 'delete':
      $id = $_GET['id'] ?? null;
      if ($id && Marcas::eliminar($conn, $id)) {
        header("Location: ../views/registrar_marcas.php?success=Marca eliminada correctamente");
      } else {
        header("Location: ../views/registrar_marcas.php?error=Error al eliminar marca");
      }
      exit;

    default:
      header("Location: ../views/registrar_marcas.php");
      exit;
  }
} catch (Exception $e) {
  header("Location: ../views/registrar_marcas.php?error=" . urlencode($e->getMessage()));
  exit;
}
