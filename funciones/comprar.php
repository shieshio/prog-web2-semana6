<?php
require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/catalogo.php';

$carrito = $_SESSION['carrito'] ?? [];
if (empty($carrito)) {
    header('Location: ../index.php');
    exit;
}
$totales = totalCarrito($carrito);

// Obtener clientes desde BD para el select
$stmt = $conn->prepare("SELECT id_cliente, nombre, email FROM CLIENTE ORDER BY nombre");
$stmt->execute();
$clientes = $stmt->get_result();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Completar Compra | Casa Morada</title>
  <link rel="stylesheet" href="../styles.css" />
</head>
<body>
  <nav class="navegacion">
    <a href="../index.php">Tienda</a>
    <a href="seguimiento.php">Seguimiento de Pedidos</a>
  </nav>

  <h1>Finalizar Compra</h1>

  <?php if (empty($carrito)): ?>
    <div class="mensaje-error">El carrito está vacío.</div>
    <a href="../index.php"><button>Volver a la Tienda</button></a>
  <?php else: ?>
    
    <!-- RESUMEN DEL CARRITO -->
    <div class="carrito-resumen">
      <h2>Productos en tu Carrito</h2>
      <?php foreach ($carrito as $id => $linea): ?>
        <div class="carrito-item">
          <span><?php echo htmlspecialchars($linea['nombre']); ?></span>
          <span><?php echo (int)$linea['unidades']; ?> × <?php echo formatearCLP($linea['precio']); ?></span>
          <strong><?php echo formatearCLP($linea['precio'] * $linea['unidades']); ?></strong>
        </div>
      <?php endforeach; ?>
      
      <div class="total-div">
        <p>Subtotal: <?php echo formatearCLP($totales['subtotal']); ?></p>
        <?php if ($totales['descuento'] > 0): ?>
          <p>Descuento: -<?php echo formatearCLP($totales['descuento']); ?></p>
        <?php endif; ?>
        <p><strong>Total a Pagar: <?php echo formatearCLP($totales['total']); ?></strong></p>
      </div>
    </div>

    <hr />

    <!-- FORMULARIO DE COMPRA -->
    <div class="formulario-compra">
      <h2>Datos de la Compra</h2>
      
      <form action="guardar_pedido.php" method="post">
        <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>" />

        <div class="campo-formulario">
          <label for="cliente">Seleccione Cliente *</label>
          <select id="cliente" name="id_cliente" required>
            <option value="">-- Seleccione --</option>
            <?php while ($c = $clientes->fetch_assoc()): ?>
              <option value="<?php echo (int)$c['id_cliente']; ?>">
                <?php echo htmlspecialchars($c['nombre']); ?> (<?php echo htmlspecialchars($c['email']); ?>)
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="campo-formulario">
          <label for="tipo_pedido">Tipo de Pedido *</label>
          <select id="tipo_pedido" name="tipo_pedido" required>
            <option value="">-- Seleccione --</option>
            <option value="Retiro en tienda">Retiro en tienda</option>
            <option value="Envío a domicilio">Envío a domicilio</option>
            <option value="Delivery express">Delivery express</option>
          </select>
        </div>

        <div class="campo-formulario">
          <label for="descripcion">Notas adicionales (opcional)</label>
          <textarea id="descripcion" name="descripcion" rows="3" placeholder="Ej: Entregar en portería, dejar en recepción..."></textarea>
        </div>

        <div class="botones-formulario">
          <button type="submit" class="boton-compra">✓ Confirmar y Pagar</button>
          <a href="../index.php" class="boton-cancelar">Cancelar</a>
        </div>
      </form>
    </div>

  <?php endif; ?>

  <footer>
    <p>&copy; 2026 Casa Morada - Todos los derechos reservados</p>
  </footer>
</body>