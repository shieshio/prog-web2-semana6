<?php
declare(strict_types=1);

$servername = "127.0.0.1";
$username   = "root";
$password   = "1234";
$database   = "TIENDA";
$port       = 3307;

$conn = new mysqli($servername, $username, $password, $database, $port);

if ($conn->connect_error) {
    die("Fallo de conexión: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
?>

