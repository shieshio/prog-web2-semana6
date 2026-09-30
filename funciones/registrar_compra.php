<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../config/conexion.php';

$idProducto = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT);
$idCliente  = filter_input(INPUT_POST, 'id_cliente', FILTER_VALIDATE_INT);
$cantidad   = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);

// Disponibilidad: consultar stock del producto deseado
$sql = "SELECT nombre, precio, stock FROM PRODUCTO WHERE id_producto = ?";
$sent = $conn->prepare($sql);
$sent->bind_param('i', $idProducto);
$sent->execute();
$producto = $sent->get_result()->fetch_assoc();

if ($producto === null || $cantidad < 1 || $cantidad > (int)$producto['stock']) {
    exit('<p class="mensaje-error">Producto no disponible en el stock solicitado.</p>');
}

$total = $producto['precio'] * $cantidad;

$sql = "INSERT INTO COMPRA (cantidad, total, fecha, id_producto, id_cliente)
        VALUES (?, ?, CURDATE(), ?, ?)";
$sent = $conn->prepare($sql);
$sent->bind_param('idii', $cantidad, $total, $idProducto, $idCliente);

if ($sent->execute() === TRUE) {
    echo "Compra registrada correctamente<br>";
} else {
    echo "Error: " . $sent->error;
}
$sent->close();
$conn->close();

