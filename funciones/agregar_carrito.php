<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validar($_POST['csrf'] ?? null)) {
    http_response_code(403);
    exit('<p class="mensaje-error">Petición rechazada.</p>');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

// Obtener producto y stock desde BD (NO confiar en el cliente)
$stmt = $conn->prepare("SELECT id_producto, nombre, precio, stock FROM PRODUCTO WHERE id_producto = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$producto = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($producto === null) {
    header('Location: ../index.php');
    exit;
}

const MAX_UNIDADES = 20;

if (!isset($_SESSION['carrito'][$producto['id_producto']])) {
    $_SESSION['carrito'][$producto['id_producto']] = [
        'id' => $producto['id_producto'],
        'nombre' => $producto['nombre'],
        'precio' => $producto['precio'],
        'unidades' => 1,
    ];
} else {
    $linea = &$_SESSION['carrito'][$producto['id_producto']];
    $linea['unidades'] = min($linea['unidades'] + 1, MAX_UNIDADES, $producto['stock']);
}
unset($linea);

header('Location: ../index.php?agregado=1#carrito');
exit;