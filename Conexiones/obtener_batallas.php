<?php
header('Content-Type: application/json');
include("conexion.php");

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$sql = "SELECT b.idBatalla, b.nombre, u.latitud, u.longitud
        FROM batallas b
        INNER JOIN ubicacion u ON b.idBatalla = u.idUbicacion";

$resultado = $conn->query($sql);

$batallas = array();
if ($resultado->num_rows > 0) {
    while($fila = $resultado->fetch_assoc()) {
        $batallas[] = array(
            "id" => $fila["idBatalla"],
            "nombre" => $fila["nombre"],
            "latitud" => $fila["latitud"],
            "longitud" => $fila["longitud"]
        );
    }
}

$conn->close();

echo json_encode($batallas);
?>
