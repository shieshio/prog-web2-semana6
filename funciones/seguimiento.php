<?php require_once __DIR__ . '/../config/sesion.php';?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../styles.css" />
  <title>Seguimiento de Pedidos | Casa Morada</title>
</head>
<body>
  <nav class="navegacion">
    <a href="../index.php">Tienda</a>
    <a href="seguimiento.php">Seguimiento de Pedidos</a>
  </nav>

  <h1>Seguimiento de Pedidos</h1>

  <div class="busqueda">
    <h2>Consultar Estado</h2>
    <form action="ver_estado.php" method="get">
      <label for="num_pedido">Número de Pedido:</label>
      <input type="number" id="num_pedido" name="num_pedido" required />
      <button type="submit">Buscar</button>
    </form>
  </div>

  <hr />

  <div class="confirmacion">
    <h2>Confirmar Recepción de Pedido</h2>
    <form action="confirmar_recepcion.php" method="post">
      <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>" />
      <label for="num_pedido_confirmar">Número de Pedido:</label>
      <input type="number" id="num_pedido_confirmar" name="num_pedido" required />
      <button type="submit">Confirmar Recepción</button>
    </form>
  </div>
</body>
</html>