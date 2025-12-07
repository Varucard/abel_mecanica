<?php
  ob_start();
  require_once '../includes/config_database.php';
  require_once '../clases/Vehiculos.php';

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cliente_id = intval($_POST['cliente_id']);
    $marca_id = intval($_POST['marca_id']);
    $modelo_id = intval($_POST['modelo_id']);
    $anio = intval($_POST['anio']);
    $patente = strtoupper(trim($_POST['patente']));
    $kilometraje = isset($_POST['kilometraje']) && $_POST['kilometraje'] !== '' ? intval($_POST['kilometraje']) : null; // <-- nuevo

    // Validaciones en PHP
    $errors = [];

    // Validar año
    if ($anio < 1940 || $anio > 2025)
      $errors[] = "El año debe estar entre 1940 y 2025";

    // Validar patente (formato: AB123CD o ABC123)
    if (!preg_match('/^[A-Z]{2,3}[0-9]{3}[A-Z]{2}|[A-Z]{3}[0-9]{3}$/', $patente))
      $errors[] = "La patente debe tener formato AB123CD o ABC123";

    // Validar kilometraje (solo si se ingresó)
    if ($kilometraje !== null && ($kilometraje < 0 || $kilometraje > 9999999))
      $errors[] = "El kilometraje debe estar entre 0 y 9,999,999 km";

    if (count($errors) > 0) {
      header("Location: ../views/registrar_vehiculo.php?error=" . urlencode(implode(", ", $errors)));
      exit;
    }

    // Crear objeto Vehículo y guardar
    try {
      $vehiculo = new Vehiculos($marca_id, $modelo_id, $anio, $patente, $cliente_id, $kilometraje); // <-- agregamos kilometraje
      
      if ($vehiculo->guardar()) {
        ob_end_clean();
        header("Location: ../views/registrar_vehiculo.php?success=create");
        exit;
      } else {
        ob_end_clean();
        header("Location: ../views/registrar_vehiculo.php?error=No se pudo registrar el vehículo. Intente nuevamente.");
        exit;
      }
    } catch (Exception $e) {
      ob_end_clean();
      header("Location: ../views/registrar_vehiculo.php?error=" . urlencode($e->getMessage()));
      exit;
    }
  }
