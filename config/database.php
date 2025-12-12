<?php
$servidor = "localhost"; // Servidor de MySQL
$usuario = "root"; // Usuario de MySQL
$password = ""; // Contraseña de MySQL
$base_datos = "MiNuevoAmigo";

// Crear conexión
$conn = new mysqli($servidor, $usuario, $password, $base_datos);

// Comprobar conexión
if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}

?>