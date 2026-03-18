<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Evitar que errores PHP rompan el JSON
error_reporting(0);
ini_set('display_errors', 0);

include("conexion.php");

// Verificar conexión
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "error" => true,
        "mensaje" => "Error de conexión a la base de datos"
    ]);
    exit();
}

// Consulta mejorada con más información para los marcadores
$sql = "SELECT 
        b.idBatalla, b.nombre, b.fecha,
        u.latitud, u.longitud, u.estado, u.ciudad
    FROM batallas b
    INNER JOIN ubicacion u ON b.idBatalla = u.idUbicacion
    ORDER BY b.fecha ASC";

$resultado = $conn->query($sql);

$batallas = array();

if ($resultado && $resultado->num_rows > 0) {
    while($fila = $resultado->fetch_assoc()) {
        $batallas[] = array(
            "id" => $fila["idBatalla"],
            "nombre" => $fila["nombre"],
            "fecha" => $fila["fecha"],
            "latitud" => $fila["latitud"],
            "longitud" => $fila["longitud"],
            "estado" => $fila["estado"],
            "ciudad" => $fila["ciudad"]
        );
    }
}

$conn->close();

echo json_encode($batallas, JSON_UNESCAPED_UNICODE);
?>