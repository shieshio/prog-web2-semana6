<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validar($_POST['csrf'] ?? null)) {
    http_response_code(403);
    exit("<p class='mensaje-error'>Petición rechazada.</p>");
}

$num = filter_input(INPUT_POST, 'num_pedido', FILTER_VALIDATE_INT);

// Buscar pedido en COMPRA
$stmt = $conn->prepare("SELECT id_compra, id_cliente, estado FROM COMPRA WHERE id_compra = ?");
$stmt->bind_param('i', $num);
$stmt->execute();
$datos = $stmt->get_result()->fetch_assoc();

if ($datos === null) {
    echo "<p class='mensaje-error'>Número de pedido inválido.</p>";
    echo "<a href='seguimiento.php'>Volver</a>";
    exit;
}

// Actualizar estado
$update = $conn->prepare("UPDATE COMPRA SET estado = 'Recibido' WHERE id_compra = ?");
$update->bind_param('i', $num);
$update->execute();
$update->close();
$conn->close();
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación de recepción</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <nav class="navegacion">
        <a href="../index.php">Tienda</a>
        <a href="seguimiento.php">Seguimiento de Pedidos</a>
    </nav>

<?php if ($datos === null): ?>
    <p class="mensaje-error">Número de pedido inválido.</p>
<?php else: ?>
    <div class="mensaje-exito">
        Pedido #<?php echo (int)$num; ?>: Estado - 
        <strong><?php echo htmlspecialchars($datos['estado']); ?></strong>
    </div>
<?php endif; ?>

    <?php if ($pedido->puedeResenar()): ?>
        <div class="resenas">
            <h2>Dejar Reseña</h2>
            <form action="enviar_resena.php" method="post">
                <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>" />
                <input type="hidden" name="num_pedido" value="<?php echo (int) $num; ?>" />

                <label for="producto">Producto:</label>
                <input type="text" id="producto" name="producto"
                       value="<?php echo htmlspecialchars($pedido->producto()); ?>"
                       required><br>

                <label for="calificacion">Calificación (1-5):</label>
                <input type="number" id="calificacion" name="calificacion"
                       min="1" max="5" required><br>

                <label for="comentario">Comentario:</label>
                <textarea id="comentario" name="comentario" rows="3" required></textarea><br>

                <button type="submit">Enviar Reseña</button>
            </form>
        </div>
    <?php endif; ?>

    <br>
    <a href="seguimiento.php"><button>Volver al Seguimiento</button></a>
</body>
</html>