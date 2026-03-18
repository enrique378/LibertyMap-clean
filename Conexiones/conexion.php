<?php
if ($_SERVER['SERVER_NAME'] == 'localhost') {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "libertymap";
} else {
    $host = "sql302.infinityfree.com";
    $user = "if0_40699727";
    $pass = "R62hp88bYJv";
    $db   = "if0_40699727_libertymap";
}

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>