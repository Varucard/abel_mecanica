<?php
  // Configuración de conexión a la base de datos con PDO
  $host = 'database'; // Nombre del contenedor MySQL
  $dbname = 'taller_mecanico';
  $username = 'root';
  $password = 'root';

  try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
      
  } catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
  }
