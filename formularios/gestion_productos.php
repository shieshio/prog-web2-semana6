<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/sesion.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/catalogo.php';

$mensaje = '';
$tipoMensaje = '';
$action = $_GET['action'] ?? 'listar';
$id_edicion = null;
$producto_edicion = null;

// ========================================
// OPERACIONES POST
// ========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // REGISTRAR NUEVO PRODUCTO
    if (isset($_POST['accion']) && $_POST['accion'] === 'crear') {
        $nombre      = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio      = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
        $stock       = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
        $categoria   = trim($_POST['categoria'] ?? '');
        
        if ($nombre === '' || $precio === null || $precio <= 0 || $stock === null || $stock < 0 || $categoria === '') {
            $mensaje = 'Todos los campos son obligatorios';
            $tipoMensaje = 'error';
        } else {
            $stmt = $conn->prepare("INSERT INTO PRODUCTO (nombre, descripcion, precio, stock, categoria) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('ssdis', $nombre, $descripcion, $precio, $stock, $categoria);
            
            if ($stmt->execute()) {
                $mensaje = 'Producto registrado correctamente';
                $tipoMensaje = 'exito';
                $action = 'listar';
            } else {
                $mensaje = 'Error al registrar: ' . $stmt->error;
                $tipoMensaje = 'error';
            }
            $stmt->close();
        }
    }
    
    // ACTUALIZAR PRODUCTO
    elseif (isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nombre      = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio      = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
        $stock       = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
        $categoria   = trim($_POST['categoria'] ?? '');
        
        if ($id === null || $id === false || $nombre === '' || $precio === null || $precio <= 0 || $stock === null || $stock < 0 || $categoria === '') {
            $mensaje = 'Datos inválidos';
            $tipoMensaje = 'error';
        } else {
            $update = $conn->prepare("UPDATE PRODUCTO SET nombre=?, descripcion=?, precio=?, stock=?, categoria=? WHERE id_producto=?");
            $update->bind_param('ssdisi', $nombre, $descripcion, $precio, $stock, $categoria, $id);
            
            if ($update->execute()) {
                $mensaje = 'Producto actualizado correctamente';
                $tipoMensaje = 'exito';
                $action = 'listar';
            } else {
                $mensaje = 'Error al actualizar';
                $tipoMensaje = 'error';
            }
            $update->close();
        }
    }
    
    // ELIMINAR PRODUCTO
    elseif (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        
        if ($id !== null && $id !== false) {
            $stmt = $conn->prepare("DELETE FROM PRODUCTO WHERE id_producto = ?");
            $stmt->bind_param('i', $id);
            
            if ($stmt->execute()) {
                $mensaje = 'Producto eliminado correctamente';
                $tipoMensaje = 'exito';
            } else {
                $mensaje = 'Error al eliminar: ' . $stmt->error;
                $tipoMensaje = 'error';
            }
            $stmt->close();
        }
        $action = 'listar';
    }
}

// ========================================
// OBTENER DATOS PARA EDICIÓN
// ========================================

$producto_edicion = null;
if ($action === 'editar') {
    $id_edicion = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if ($id_edicion !== null && $id_edicion !== false) {
        $stmt = $conn->prepare("SELECT id_producto, nombre, descripcion, precio, stock, categoria FROM PRODUCTO WHERE id_producto = ?");
        $stmt->bind_param('i', $id_edicion);
        $stmt->execute();
        $producto_edicion = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($producto_edicion === null) {
            header('Location: gestion_productos.php?action=listar');
            exit;
        }
    } else {
        header('Location: gestion_productos.php?action=listar');
        exit;
    }
}

// ========================================
// OBTENER LISTA DE PRODUCTOS
// ========================================

$buscar = $_GET['buscar'] ?? '';
$criterio = $_GET['criterio'] ?? 'nombre';

$sql = "SELECT id_producto, nombre, descripcion, precio, stock, categoria FROM PRODUCTO";
$params = [];
$types = "";

if ($buscar !== '') {
    $criterios_validos = ['nombre', 'precio', 'stock', 'categoria'];
    if (!in_array($criterio, $criterios_validos)) {
        $criterio = 'nombre';
    }
    
    if ($criterio === 'precio' || $criterio === 'stock') {
        $sql .= " WHERE $criterio = ?";
        $params[] = ($criterio === 'precio') ? (float)$buscar : (int)$buscar;
        $types .= ($criterio === 'precio') ? "d" : "i";
    } else {
        $sql .= " WHERE $criterio LIKE ?";
        $params[] = "%$buscar%";
        $types .= "s";
    }
}

$sql .= " ORDER BY categoria, nombre";

if ($buscar !== '') {
    $stmt_list = $conn->prepare($sql);
    $stmt_list->bind_param($types, ...$params);
    $stmt_list->execute();
    $result = $stmt_list->get_result();
} else {
    $result = $conn->query($sql);
}

// Obtener categorías para el select (Mantiene el código original)
$categorias = $conn->query("SELECT DISTINCT categoria FROM PRODUCTO ORDER BY categoria");
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Gestión de Productos | Casa Morada</title>
  <link rel="stylesheet" href="../styles.css" />
</head>
<body>
  <nav class="navegacion">
    <a href="../index.php">Tienda</a>
    <a href="gestion_clientes.php">Gestión Clientes</a>
    <a href="../config/mantenimiento.php">Mantenimiento</a>
    <a href="../config/logout.php">Cerrar Sesión</a>
  </nav>

  <div style="margin: 30px;">
    <h1>Gestión de Productos</h1>

    <?php if ($mensaje !== ''): ?>
      <div class="<?php echo $tipoMensaje === 'exito' ? 'mensaje-exito' : 'mensaje-error'; ?>" style="margin-bottom: 20px;">
        <?php echo htmlspecialchars($mensaje); ?>
      </div>
    <?php endif; ?>
    
    <!-- =========================== -->
    <!-- FORMULARIO DE CREACIÓN/EDICIÓN -->
    <!-- =========================== -->
    <?php if ($action === 'crear' || ($action === 'editar' && isset($producto_edicion))): ?>
      
      <div class="carrito" style="max-width: 500px; margin-bottom: 30px;">
        <h2><?php echo $action === 'crear' ? 'Registrar Nuevo Producto' : 'Modificar Producto #' . (int)$id_edicion; ?></h2>
        
        <form method="post" action="gestion_productos.php?action=<?php echo $action === 'editar' ? 'editar' : 'crear'; ?>" class="formulario-compra">
          <input type="hidden" name="accion" value="<?php echo $action === 'editar' ? 'actualizar' : 'crear'; ?>" />
          <?php if ($action === 'editar'): ?>
            <input type="hidden" name="id" value="<?php echo (int)$id_edicion; ?>" />
          <?php endif; ?>

          <div class="campo-formulario">
            <label for="nombre">Nombre *</label>
            <input type="text" id="nombre" name="nombre" 
                   value="<?php echo $action === 'editar' ? htmlspecialchars($producto_edicion['nombre']) : ''; ?>" required autofocus />
          </div>
          
          <div class="campo-formulario">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="3"><?php echo $action === 'editar' ? htmlspecialchars($producto_edicion['descripcion'] ?? '') : ''; ?></textarea>
          </div>
          
          <div class="campo-formulario">
            <label for="precio">Precio *</label>
            <input type="number" id="precio" name="precio" step="0.01" min="1" 
                   value="<?php echo $action === 'editar' ? (float)$producto_edicion['precio'] : ''; ?>" required />
          </div>
          
          <div class="campo-formulario">
            <label for="stock">Stock *</label>
            <input type="number" id="stock" name="stock" min="0" 
                   value="<?php echo $action === 'editar' ? (int)$producto_edicion['stock'] : ''; ?>" required />
          </div>
          
          <div class="campo-formulario">
            <label for="categoria">Categoría *</label>
            <select id="categoria" name="categoria" required>
              <option value="" disabled <?php echo $action === 'crear' ? 'selected' : ''; ?>>Seleccione una categoría...</option>
              <?php 
              $categorias->data_seek(0);
              while($cat = $categorias->fetch_assoc()): 
                $isSelected = ($action === 'editar' && isset($producto_edicion['categoria']) && $producto_edicion['categoria'] === $cat['categoria']) ? 'selected' : '';
              ?>
                <option value="<?php echo htmlspecialchars($cat['categoria']); ?>" <?php echo $isSelected; ?>>
                  <?php echo htmlspecialchars($cat['categoria']); ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          
          <div class="botones-formulario">
            <button type="submit"><?php echo $action === 'crear' ? 'Guardar Producto' : 'Actualizar Producto'; ?></button>
            <button type="button" onclick="location.href='?action=listar'" class="boton-cancelar">Cancelar</button>
          </div>
        </form>
      </div>

    <!-- =========================== -->
    <!-- TABLA DE CONSULTA / LISTADO -->
    <!-- =========================== -->
    <?php else: ?>
  
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <a href="?action=crear"><button>+ Nuevo Producto</button></a>
        
        <!-- BARRA DE BÚSQUEDA -->
        <form method="get" action="gestion_productos.php" style="display: flex; gap: 10px;">
          <input type="hidden" name="action" value="listar">
          
          <select name="criterio" style="padding: 5px; border-radius: 4px;">
            <option value="nombre" <?php echo $criterio === 'nombre' ? 'selected' : ''; ?>>Nombre</option>
            <option value="categoria" <?php echo $criterio === 'categoria' ? 'selected' : ''; ?>>Categoría</option>
            <option value="precio" <?php echo $criterio === 'precio' ? 'selected' : ''; ?>>Precio</option>
            <option value="stock" <?php echo $criterio === 'stock' ? 'selected' : ''; ?>>Stock</option>
          </select>

          <input type="text" name="buscar" placeholder="Ingrese su búsqueda..." 
                value="<?php echo htmlspecialchars($buscar); ?>" style="padding: 5px; width: 220px; border-radius: 4px;" />
                
          <button type="submit" style="cursor: pointer;">🔍 Buscar</button>
          
          <?php if ($buscar !== ''): ?>
            <a href="gestion_productos.php?action=listar">
              <button type="button" style="background-color: #757575;">Limpiar</button>
            </a>
          <?php endif; ?>
        </form>
      </div>
      
      <div class="tabla-container">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Categoría</th>
              <th>Precio</th>
              <th>Stock</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result->num_rows === 0): ?>
              <tr><td colspan="6" style="text-align: center;">No hay productos registrados</td></tr>
            <?php else: ?>
              <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?php echo (int)$row['id_producto']; ?></td>
                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                <td><?php echo htmlspecialchars($row['categoria']); ?></td>
                <td><?php echo formatearCLP((float)$row['precio']); ?></td>
                <td><?php echo (int)$row['stock']; ?></td>
                <td>
                  <a href="?action=editar&id=<?php echo (int)$row['id_producto']; ?>">Editar</a> |
                  <button onclick="eliminarProducto(<?php echo (int)$row['id_producto']; ?>, '<?php echo htmlspecialchars($row['nombre'], ENT_QUOTES); ?>')" 
                          style="background-color: #d32f2f; color: white; border: none; padding: 5px 10px; cursor: pointer;">
                    Eliminar
                  </button>
                </td>
              </tr>
              <?php endwhile; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      
      <!-- FORMULARIO OCULTO PARA ELIMINACIÓN -->
      <form id="form-eliminar" method="post" action="gestion_productos.php?action=listar" style="display: none;">
        <input type="hidden" name="accion" value="eliminar" />
        <input type="hidden" name="id" id="id-eliminar" value="" />
      </form>

      <script>
        function eliminarProducto(id, nombre) {
          if (confirm('¿Está seguro de eliminar el producto "' + nombre + '"?')) {
            document.getElementById('id-eliminar').value = id;
            document.getElementById('form-eliminar').submit();
          }
        }
      </script>

    <?php endif; ?>

  </div>
</body>
</html>