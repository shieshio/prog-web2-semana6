<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/catalogo.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validar($_POST['csrf'] ?? null)) {
    http_response_code(403);
    exit('<p class="mensaje-error">Petición rechazada.</p>');
}

$id_cliente = filter_input(INPUT_POST, 'id_cliente', FILTER_VALIDATE_INT);
$nombre     = filtro($_POST['nombre'] ?? '');
$email      = filtro($_POST['email'] ?? '');
$descripcion = filtro($_POST['descripcion'] ?? '');
$tipoPedido = filtro($_POST['tipo_pedido'] ?? '');

$carrito = $_SESSION['carrito'] ?? [];
$totales = totalCarrito($carrito);
$unidades = $totales['unidades'];

// VALIDACIÓN AGREGADA: Evitar procesamiento sin carrito
if (empty($carrito)) {
    http_response_code(400);
    exit('<p class="mensaje-error">Carrito vacío. No hay nada que pagar.</p>');
}

// Verificar que el cliente existe en DB
$stmt = $conn->prepare("SELECT id_cliente FROM CLIENTE WHERE id_cliente = ?");
$stmt->bind_param('i', $id_cliente);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    exit('<p class="mensaje-error">Cliente no encontrado.</p>');
}

// Generar número de pedido
$numero_pedido = random_int(100000, 999999);

// Registrar todas las líneas del carrito como COMPRAS individuales
foreach ($carrito as $id_producto => $linea) {
    $cantidad = (int)$linea['unidades'];
    $total_linea = $linea['precio'] * $cantidad;
    
    $stmt = $conn->prepare("INSERT INTO COMPRA (cantidad, total, fecha, id_producto, id_cliente)
                            VALUES (?, ?, CURDATE(), ?, ?)");
    $stmt->bind_param('idii', $cantidad, $total_linea, $id_producto, $id_cliente);
    $stmt->execute();
}

unset($_SESSION['carrito']);
$conn->close();
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pago Exitoso | Casa Morada</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <nav class="navegacion">
        <a href="../index.php">Tienda</a>
        <a href="seguimiento.php">Seguimiento de Pedidos</a>
    </nav>

    <div class="mensaje-exito">
        <?php echo "¡Pago realizado con éxito!<br><br>"; ?>
        
        <!-- Información del pedido (sin objeto Pedido) -->
        <strong>Número de pedido:</strong> #<?php echo $numero_pedido; ?><br><br>
        <strong>Tipo de pedido:</strong> <?php echo htmlspecialchars($tipoPedido); ?><br>
        <strong>Cliente:</strong> <?php echo htmlspecialchars($nombre); ?> (<?php echo htmlspecialchars($email); ?>)<br>
        <strong>Descripción:</strong> <?php echo htmlspecialchars($descripcion); ?><br>
        <strong>Total a pagar:</strong> <?php echo formatearCLP($totales['total']); ?><br>
        <strong>Unidades:</strong> <?php echo (int)$unidades; ?>
    </div>

    <br>
    <a href="../index.php"><button>Volver a la Tienda</button></a>
    <a href="seguimiento.php"><button>Ver estado del pedido</button></a>
</body>
</html>