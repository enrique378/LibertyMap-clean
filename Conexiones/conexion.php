<?php
$servidor = "localhost";
$usuario = "root";      
$contrasena = "";
$base_datos = "libertymap";

$conn = new mysqli($servidor, $usuario, $contrasena, $base_datos);

if ($conn->connect_error) {
    die("Error al conectar con la base de datos: " . $conn->connect_error);
}
?>