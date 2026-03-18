<?php
header('Content-Type: application/json; charset=utf-8');

// Paso 1: Verificar que llegamos aquí
$debug = array();
$debug['paso'] = 'inicio';

try {
    // Paso 2: Incluir conexión
    $debug['paso'] = 'antes_conexion';
    include 'conexion.php';
    $debug['paso'] = 'despues_conexion';
    
    // Paso 3: Verificar variable conn
    if (!isset($conn)) {
        throw new Exception('conn no existe');
    }
    $debug['paso'] = 'conn_existe';
    
    // Paso 4: Verificar conexión
    if ($conn->connect_error) {
        throw new Exception($conn->connect_error);
    }
    $debug['paso'] = 'conn_ok';
    
    // Paso 5: Ejecutar query
    $sql = "SELECT idPregunta, pregunta, respuesta FROM preguntas ORDER BY idPregunta ASC";
    $resultado = $conn->query($sql);
    $debug['paso'] = 'query_ejecutada';
    
    // Paso 6: Verificar resultado
    if (!$resultado) {
        throw new Exception($conn->error);
    }
    $debug['paso'] = 'resultado_ok';
    $debug['num_rows'] = $resultado->num_rows;
    
    // Paso 7: Procesar datos
    $preguntas = array();
    
    while($fila = $resultado->fetch_assoc()) {
        $idBatallaCalculado = (int)ceil((int)$fila["idPregunta"] / 2);
        
        $preguntas[] = array(
            "idPregunta" => (int)$fila["idPregunta"],
            "pregunta" => $fila["pregunta"],
            "respuesta" => $fila["respuesta"],
            "idBatalla" => $idBatallaCalculado
        );
    }
    
    $debug['paso'] = 'datos_procesados';
    $debug['total_preguntas'] = count($preguntas);
    
    $conn->close();
    
    // Paso 8: Devolver resultado
    echo json_encode($preguntas, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    $debug['error'] = $e->getMessage();
    echo json_encode($debug, JSON_UNESCAPED_UNICODE);
}
?>