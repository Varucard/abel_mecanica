<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start();
require_once '../includes/config_database.php';
require_once '../clases/Clientes.php';

/*
|--------------------------------------------------------------------------
| 1) ELIMINAR CLIENTE
|--------------------------------------------------------------------------
*/
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {

    $id = intval($_GET['id']);

    if (Clientes::eliminar($conn, $id)) {
        if (ob_get_level() > 0) ob_end_clean();
        header("Location: ../views/listar_clientes.php?success=delete");
        exit;
    } else {
        if (ob_get_level() > 0) ob_end_clean();
        header("Location: ../views/listar_clientes.php?error=delete");
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| 2) CAMBIAR ESTADO (activo / inactivo)
|--------------------------------------------------------------------------
*/
if (isset($_GET['action']) && $_GET['action'] === 'cambiar_estado' && isset($_GET['id']) && isset($_GET['estado'])) {

    $id = intval($_GET['id']);
    $estado = $_GET['estado'] === 'activo' ? 'inactivo' : 'activo';

    if (Clientes::cambiarEstado($conn, $id, $estado)) {
        if (ob_get_level() > 0) ob_end_clean();
        header("Location: ../views/listar_clientes.php?success=estado");
        exit;
    } else {
        if (ob_get_level() > 0) ob_end_clean();
        header("Location: ../views/listar_clientes.php?error=estado");
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| 3) PROCESAR CREAR / EDITAR CLIENTE (POST)
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id         = isset($_POST['id']) && $_POST['id'] !== '' ? intval($_POST['id']) : null;
    $nombre     = mb_strtoupper(trim($_POST['nombre']));
    $apellido   = mb_strtoupper(trim($_POST['apellido']));
    $dni        = trim($_POST['dni']);
    $telefono   = trim($_POST['telefono']);
    $direccion  = mb_strtoupper(trim($_POST['direccion']));

    // Validaciones básicas
    $errors = [];

    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]{2,50}$/', $nombre))
        $errors[] = "El nombre debe contener solo letras y espacios (2-50 caracteres).";

    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]{2,50}$/', $apellido))
        $errors[] = "El apellido debe contener solo letras y espacios (2-50 caracteres).";

    if (!preg_match('/^[0-9]{6,8}$/', $dni))
        $errors[] = "El DNI debe tener entre 6 y 8 dígitos.";

    if (!preg_match('/^[0-9]{10}$/', $telefono))
        $errors[] = "El teléfono debe tener exactamente 10 dígitos.";

    if (strlen($direccion) < 5 || strlen($direccion) > 200)
        $errors[] = "La dirección debe tener entre 5 y 200 caracteres.";

    if (count($errors) > 0) {
        if (ob_get_level() > 0) ob_end_clean();
        header("Location: ../views/registrar_cliente.php?error=" . urlencode(implode(", ", $errors)) . ($id ? "&id=$id" : ""));
        exit;
    }

    // Crear objeto Cliente
    $cliente = new Clientes($nombre, $apellido, $dni, $telefono, $direccion);

    try {

        // EDITAR
        if ($id) {
            if ($cliente->actualizar($conn, $id)) {
                if (ob_get_level() > 0) ob_end_clean();
                header("Location: ../views/listar_clientes.php?success=update");
                exit;
            }
        }

        // CREAR
        else {
            if ($cliente->guardar($conn)) {
                if (ob_get_level() > 0) ob_end_clean();
                header("Location: ../views/listar_clientes.php?success=create");
                exit;
            }
        }

        // Si no guardó:
        if (ob_get_level() > 0) ob_end_clean();
        header("Location: ../views/registrar_cliente.php?error=" . urlencode("No se pudo guardar el cliente.") . ($id ? "&id=$id" : ""));
        exit;

    } catch (Exception $e) {
        if (ob_get_level() > 0) ob_end_clean();
        header("Location: ../views/registrar_cliente.php?error=" . urlencode($e->getMessage()) . ($id ? "&id=$id" : ""));
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| 4) SI VIENE GET CON SOLO id → IR A FORMULARIO DE EDITAR
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && !isset($_GET['action'])) {
    $id = intval($_GET['id']);
    if (ob_get_level() > 0) ob_end_clean();
    header("Location: ../views/registrar_cliente.php?id=" . $id);
    exit;
}

/*
|--------------------------------------------------------------------------
| 5) MANEJO DE success / error DIRECTO POR GET
|--------------------------------------------------------------------------
*/
if (isset($_GET['success'])) {
    if (ob_get_level() > 0) ob_end_clean();
    header("Location: ../views/listar_clientes.php?success=" . urlencode($_GET['success']));
    exit;
}

if (isset($_GET['error'])) {
    if (ob_get_level() > 0) ob_end_clean();
    header("Location: ../views/registrar_cliente.php?error=" . urlencode($_GET['error']));
    exit;
}

/*
|--------------------------------------------------------------------------
| 6) SI NO HAY NADA → IR A LA LISTA (EVITA PANTALLA EN BLANCO)
|--------------------------------------------------------------------------
*/
if (ob_get_level() > 0) ob_end_clean();
header("Location: ../views/listar_clientes.php");
exit;

