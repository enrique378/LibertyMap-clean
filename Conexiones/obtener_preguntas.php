<?php
header('Content-Type: application/json');
header('Content-Type: text/html; charset=utf-8');
include("conexion.php");

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(array('error' => 'Conexión fallida: ' . $conn->connect_error)));
}

$sql = "SELECT idPregunta, pregunta, respuesta
        FROM preguntas
        ORDER BY idPregunta ASC";

$resultado = $conn->query($sql);

$preguntas = array();

if ($resultado) {
    if ($resultado->num_rows > 0) {
        while($fila = $resultado->fetch_assoc()) {
            $preguntas[] = array(
                "idPregunta" => $fila["idPregunta"],
                "pregunta" => $fila["pregunta"],
                "respuesta" => $fila["respuesta"]
            );
        }
    }
} else {
    http_response_code(500);
    echo json_encode(array('error' => 'Error en la consulta: ' . $conn->error));
    $conn->close();
    exit;
}

$conn->close();

echo json_encode($preguntas, JSON_UNESCAPED_UNICODE);
?>