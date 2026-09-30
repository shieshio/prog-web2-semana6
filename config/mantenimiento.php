<?php
declare(strict_types=1);

require_once __DIR__ . '/sesion.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Mantenimiento | Casa Morada</title>
  <link rel="stylesheet" href="../styles.css" />
</head>
<body>
  <nav class="navegacion">
    <a href="../index.php">Tienda</a>
    <a href="../funciones/seguimiento.php">Seguimiento</a>
    <a href="mantenimiento.php" style="background-color: #ff9800;">Mantenimiento</a>
    <a href="logout.php">Cerrar Sesión</a>
  </nav>

  <h1>Panel de Administración</h1>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; max-width: 800px; margin: 0 auto;">
    
    <!-- Tarjeta Clientes -->
    <div class="carrito">
      <h2>Gestión de Clientes</h2>
      <p>Registrar, consultar, modificar y eliminar clientes en una sola página</p>
      <div style="margin-top: 15px;">
        <a href="../formularios/gestion_clientes.php"><button>Ir a Gestión de Clientes</button></a>
      </div>
    </div>

    <!-- Tarjeta Productos -->
    <div class="carrito">
      <h2>Gestión de Productos</h2>
      <p>Registrar, consultar, modificar y eliminar productos en una sola página</p>
      <div style="margin-top: 15px;">
        <a href="../formularios/gestion_productos.php"><button>Ir a Gestión de Productos</button></a>
      </div>
    </div>
    
  </div>
</body>
</html>

