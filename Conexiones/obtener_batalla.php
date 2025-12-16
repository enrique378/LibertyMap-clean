<?php
header('Content-Type: application/json');
include("conexion.php");

$sql = "SELECT 
        idBatalla, nombre, descripcion, fecha, personajes, ganador, 
        ubicacion.estado, ubicacion.ciudad, ubicacion.latitud, ubicacion.longitud 
    FROM batallas
    INNER JOIN ubicacion ON batallas.idBatalla  = ubicacion.idUbicacion";

$resultado = $conn->query($sql);

$batalla = array();
if ($resultado->num_rows > 0){
while($row = $resultado->fetch_assoc()) {
    $batalla [] = array(
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
}
}

$conn->close();

echo json_encode($batalla);
?>
