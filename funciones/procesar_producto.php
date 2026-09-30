<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../formularios/formulario_producto.html');
    exit;
}

$nombre      = filtro($_POST['nombre'] ?? '');
$descripcion = filtro($_POST['descripcion'] ?? '');
$precio      = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
$stock       = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);

if ($nombre === '' || $precio === null || $precio === false
    || $stock === null || $stock === false || $stock < 0) {
    exit('<p class="mensaje-error">Datos del producto inválidos.</p>');
}

// Sentencia preparada para integridad de información (S6F)
$sql = "INSERT INTO PRODUCTO (nombre, descripcion, precio, stock) VALUES (?, ?, ?, ?)";
$sentencia = $conn->prepare($sql);
$sentencia->bind_param('ssdi', $nombre, $descripcion, $precio, $stock);

if ($sentencia->execute() === TRUE) {
    echo "Datos ingresados correctamente<br>";
} else {
    echo "Error: " . $sentencia->error;
}
$sentencia->close();
$conn->close();