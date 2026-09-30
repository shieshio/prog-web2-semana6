<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../config/conexion.php';

$num = filter_input(INPUT_GET, 'num_pedido', FILTER_VALIDATE_INT);

$stmt = $conn->prepare("SELECT p.nombre AS producto, p.stock, c.total, c.estado, cl.nombre AS cliente
FROM COMPRA c
INNER JOIN PRODUCTO p ON c.id_producto = p.id_producto
INNER JOIN CLIENTE cl ON c.id_cliente = cl.id_cliente
WHERE c.id_compra = ?");
$stmt->bind_param('i', $num);
$stmt->execute();
$datos = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Estado del Pedido</title>
  <link rel="stylesheet" href="../styles.css">
</head>
<body>
  <!-- ... navegación ... -->
  
  <?php if ($datos === null): ?>
    <div class="mensaje-error">
      No se encontró el pedido #<?php echo htmlspecialchars((string) $num); ?>
    </div>
  <?php else: ?>
    <div class="mensaje-exito">
      Pedido #<?php echo (int) $num; ?>:
      <strong><?php echo htmlspecialchars($datos['estado']); ?></strong><br>
      Cliente: <?php echo htmlspecialchars($datos['nombre']); ?> —
      <?php echo htmlspecialchars($datos['descripcion']); ?><br>
      <?php echo htmlspecialchars($datos['producto']); ?> ×
      <?php echo (int) $datos['unidades']; ?> u. —
      Total: <?php echo formatearCLP((float) $datos['total']); ?>
    </div>
  <?php endif; ?>
  
  <br>
  <a href="seguimiento.php"><button>Volver al Seguimiento</button></a>
</body>
</html>