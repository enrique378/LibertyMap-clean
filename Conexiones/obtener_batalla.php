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

// RECIBIR Y VALIDAR EL ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode([
        "error" => true,
        "mensaje" => "ID de batalla inválido"
    ]);
    $conn->close();
    exit();
}

// CONSULTA SQL CON FILTRO POR ID (usando prepared statement)
$sql = "SELECT 
        b.idBatalla, b.nombre, b.descripcion, b.fecha, b.personajes, b.ganador, 
        u.estado, u.ciudad, u.latitud, u.longitud 
    FROM batallas b
    INNER JOIN ubicacion u ON b.idBatalla = u.idUbicacion
    WHERE b.idBatalla = ?
    LIMIT 1";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "error" => true,
        "mensaje" => "Error al preparar consulta: " . $conn->error
    ]);
    $conn->close();
    exit();
}

$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $row = $resultado->fetch_assoc();
    
    $batalla = array(
        "id" => $row["idBatalla"],
        "nombre" => $row["nombre"],
        "descripcion" => $row["descripcion"],
        "fecha" => $row["fecha"],
        "personajes" => $row["personajes"],
        "ganador" => $row["ganador"],
        "estado" => $row["estado"],
        "ciudad" => $row["ciudad"],
        "latitud" => $row["latitud"],
        "longitud" => $row["longitud"]
    );
    
    echo json_encode($batalla, JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(404);
    echo json_encode([
        "error" => true,
        "mensaje" => "Batalla no encontrada con ID: " . $id
    ]);
}

$stmt->close();
$conn->close();
?>