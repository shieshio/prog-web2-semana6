<?php
require_once 'config/sesion.php';
require_once 'config/catalogo.php';

$catalogo = obtenerCatalogo();
$carrito = $_SESSION['carrito'] ?? [];
$totales = totalCarrito($carrito);
$carritoVacio = empty($carrito);

$aviso = '';
if (isset($_GET['agregado'])) { $aviso = 'Producto agregado al carrito.'; }
if (isset($_GET['eliminado'])) { $aviso = 'Producto eliminado del carrito.'; }
if (isset($_GET['vaciado']))  { $aviso = 'Carrito vaciado.'; }
if (isset($_GET['actualizado'])) { $aviso = 'Cantidad actualizada.'; }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="styles.css" />
  <title>Venta de Tecnología | Casa Morada</title>
</head>
<body>
  <nav class="navegacion">
    <a href="index.php">Tienda</a>
    <a href="funciones/seguimiento.php">Seguimiento de Pedidos</a>
    <a href="login.php">Administración</a> <!-- ← NUEVO -->
  </nav>

  <h1 id="titulo-tienda">Destacados del Mes</h1>

  <div id="aviso" class="estado-msg" hidden></div>
  <?php if ($aviso !== ''): ?>
    <div class="mensaje-exito"><?php echo $aviso; ?></div>
  <?php endif; ?>

  <!-- Filtros: actualizados en tiempo real (input/change), sin botones ni alert() -->
  <div class="filtros">
    <select id="filtro-categoria">
      <option value="">Todas las Categorías</option>
      <option value="Notebooks">Notebooks</option>
      <option value="Consolas">Consolas</option>
      <option value="Periféricos">Periféricos</option>
      <option value="Procesadores">Procesadores</option>
      <option value="Tarjetas de video">Tarjetas de video</option>
      <option value="Smartwatches">Smartwatches</option>
    </select>
    <input type="number" id="filtro-precio" placeholder="Precio máximo CLP" min="0" />
    <button id="btn-limpiar">Limpiar Filtros</button>
  </div>

  <div class="tabla-container">
    <table id="tabla-productos">
      <thead>
        <tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Acción</th></tr>
      </thead>
      <tbody id="tabla-body"><!-- Renderizado con DocumentFragment desde scripts.js --></tbody>
    </table>
  </div>

  <!-- Carrito gestionado con sesiones PHP -->
  <div class="carrito" id="carrito">
    <h2>Carrito de Compras (<?php echo (int) $totales['unidades']; ?> unidades)</h2>

    <?php if ($carritoVacio): ?>
      <p>El carrito está vacío.</p>
    <?php else: ?>
      <?php foreach ($carrito as $id => $linea): ?>
        <div class="carrito-item">
          <p><?php echo htmlspecialchars($linea['nombre']); ?> —
             <?php echo formatearCLP($linea['precio']); ?></p>
          <form action="funciones/actualizar_carrito.php" method="post" class="accion-form">
            <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>" />
            <input type="hidden" name="id" value="<?php echo (int) $id; ?>" />
            <label>Unid.: <input type="number" name="unidades" value="<?php echo (int) $linea['unidades']; ?>" min="1" max="20" /></label>
            <button type="submit" name="accion" value="actualizar">Actualizar</button>
            <button type="submit" name="accion" value="eliminar" class="btn-rojo">Eliminar</button>
          </form>
        </div>
      <?php endforeach; ?>

      <div class="total-div">
        <p>Subtotal: <?php echo formatearCLP($totales['subtotal']); ?></p>
        <?php if ($totales['descuento'] > 0): ?>
          <p>Descuento: -<?php echo formatearCLP($totales['descuento']); ?></p>
        <?php endif; ?>
        <p><strong>Total: <?php echo formatearCLP($totales['total']); ?></strong></p>
      </div>

      <form action="funciones/actualizar_carrito.php" method="post" class="accion-form">
        <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>" />
        <button type="submit" name="accion" value="vaciar">Vaciar Carrito</button>
      </form>
      <a href="funciones/comprar.php" class="boton-compra" id="btn-comprar">Realizar Compra</a>
    <?php endif; ?>
  </div>
  
  <!--Puente de datos: Pasa el array PHP actualizado desde BD a JS -->
  <script>window.PRODUCTOS = <?php echo json_encode($catalogo, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK); ?>; </script>
  <script>window.CSRF_TOKEN = "<?php echo csrf_token(); ?>";</script>
  <script>window.PRODUCTOS = <?php echo json_encode($catalogo, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>;</script>
  <script src="scripts.js"></script>
</body>
</html>