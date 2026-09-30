<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../config/conexion.php';

$nombre    = filtro($_POST['nombre'] ?? '');
$email     = filtro($_POST['email'] ?? '');
$direccion = filtro($_POST['direccion'] ?? '');

if ($nombre === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit('<p class="mensaje-error">Datos del cliente inválidos.</p>');
}

$sql = "INSERT INTO CLIENTE (nombre, email, direccion) VALUES (?, ?, ?)";
$sentencia = $conn->prepare($sql);
$sentencia->bind_param('sss', $nombre, $email, $direccion);

if ($sentencia->execute() === TRUE) {
    echo "Datos ingresados correctamente<br>";
} else {
    echo "Error: " . $sentencia->error;
}
$sentencia->close();
$conn->close();
?>
