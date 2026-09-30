<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/sesion.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../config/conexion.php';

$mensaje = '';
$tipoMensaje = '';
$action = $_GET['action'] ?? 'listar';
$id_edicion = null;
$cliente_edicion = null;  // ← INICIALIZADO

// ========================================
// OPERACIONES POST (Crear, Actualizar, Eliminar)
// ========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // REGISTRAR NUEVO CLIENTE
    if (isset($_POST['accion']) && $_POST['accion'] === 'crear') {
        $nombre    = trim($_POST['nombre'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        
        if ($nombre === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensaje = 'Nombre obligatorio y email inválido';
            $tipoMensaje = 'error';
        } else {
            $check = $conn->prepare("SELECT id_cliente FROM CLIENTE WHERE email = ?");
            $check->bind_param('s', $email);
            $check->execute();
            $check->get_result();
            
            if ($check->num_rows > 0) {
                $mensaje = 'El email ya está registrado';
                $tipoMensaje = 'error';
            } else {
                $stmt = $conn->prepare("INSERT INTO CLIENTE (nombre, email, direccion) VALUES (?, ?, ?)");
                $stmt->bind_param('sss', $nombre, $email, $direccion);
                
                if ($stmt->execute()) {
                    $mensaje = 'Cliente registrado correctamente';
                    $tipoMensaje = 'exito';
                    $action = 'listar';
                } else {
                    $mensaje = 'Error al registrar: ' . $stmt->error;
                    $tipoMensaje = 'error';
                }
                $stmt->close();
            }
            $check->close();
        }
    }
    
    // ACTUALIZAR CLIENTE EXISTENTE
    elseif (isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nombre    = trim($_POST['nombre'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        
        if ($id === null || $id === false || $nombre === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensaje = 'Datos inválidos';
            $tipoMensaje = 'error';
        } else {
            $check = $conn->prepare("SELECT id_cliente FROM CLIENTE WHERE email = ? AND id_cliente != ?");
            $check->bind_param('si', $email, $id);
            $check->execute();
            $check->get_result();
            
            if ($check->num_rows > 0) {
                $mensaje = 'El email ya está registrado por otro cliente';
                $tipoMensaje = 'error';
            } else {
                $update = $conn->prepare("UPDATE CLIENTE SET nombre=?, email=?, direccion=? WHERE id_cliente=?");
                $update->bind_param('sssi', $nombre, $email, $direccion, $id);
                
                if ($update->execute()) {
                    $mensaje = 'Cliente actualizado correctamente';
                    $tipoMensaje = 'exito';
                    $action = 'listar';
                } else {
                    $mensaje = 'Error al actualizar';
                    $tipoMensaje = 'error';
                }
                $update->close();
            }
            $check->close();
        }
    }
    
    // ELIMINAR CLIENTE
    elseif (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        
        if ($id !== null && $id !== false) {
            $stmt = $conn->prepare("DELETE FROM CLIENTE WHERE id_cliente = ?");
            $stmt->bind_param('i', $id);
            
            if ($stmt->execute()) {
                $mensaje = 'Cliente eliminado correctamente';
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
// OBTENER DATOS PARA EDICIÓN (GET)
// ========================================

if ($action === 'editar') {
    $id_edicion = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if ($id_edicion !== null && $id_edicion !== false) {
        $stmt = $conn->prepare("SELECT id_cliente, nombre, email, direccion FROM CLIENTE WHERE id_cliente = ?");
        $stmt->bind_param('i', $id_edicion);
        $stmt->execute();
        $cliente_edicion = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($cliente_edicion === null) {
            header('Location: gestion_clientes.php?action=listar');
            exit;
        }
    } else {
        header('Location: gestion_clientes.php?action=listar');
        exit;
    }
}

// ========================================
// OBTENER LISTA DE CLIENTES (PARA CONSULTAR)
// ========================================

$result = $conn->query("SELECT id_cliente, nombre, email, direccion FROM CLIENTE ORDER BY id_cliente DESC");
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Gestión de Clientes | Casa Morada</title>
  <link rel="stylesheet" href="../styles.css" />
</head>
<body>
  <nav class="navegacion">
    <a href="../index.php">Tienda</a>
    <a href="gestion_productos.php">Gestión Productos</a>
    <a href="../config/mantenimiento.php">Mantenimiento</a>
    <a href="../config/logout.php">Cerrar Sesión</a>
  </nav>

  <div style="margin: 30px;">
    <h1>Gestión de Clientes</h1>
    
    <?php if ($mensaje !== ''): ?>
      <div class="<?php echo $tipoMensaje === 'exito' ? 'mensaje-exito' : 'mensaje-error'; ?>" style="margin-bottom: 20px;">
        <?php echo htmlspecialchars($mensaje); ?>
      </div>
    <?php endif; ?>

    <!-- =========================== -->
    <!-- FORMULARIO DE CREACIÓN/EDICIÓN -->
    <!-- =========================== -->
    <?php if ($action === 'crear' || ($action === 'editar' && $cliente_edicion !== null)): ?>
      
      <div class="carrito" style="max-width: 500px; margin-bottom: 30px;">
        <h2><?php echo $action === 'crear' ? 'Registrar Nuevo Cliente' : 'Modificar Cliente #' . (int)$id_edicion; ?></h2>
        
        <form method="post" action="gestion_clientes.php" class="formulario-compra">
          <input type="hidden" name="accion" value="<?php echo $action === 'editar' ? 'actualizar' : 'crear'; ?>" />
          <?php if ($action === 'editar'): ?>
            <input type="hidden" name="id" value="<?php echo (int)$id_edicion; ?>" />
          <?php endif; ?>

          <div class="campo-formulario">
            <label for="nombre">Nombre *</label>
            <input type="text" id="nombre" name="nombre" 
                   value="<?php echo $action === 'editar' && $cliente_edicion ? htmlspecialchars($cliente_edicion['nombre']) : ''; ?>" required autofocus />
          </div>
          
          <div class="campo-formulario">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" 
                   value="<?php echo $action === 'editar' && $cliente_edicion ? htmlspecialchars($cliente_edicion['email']) : ''; ?>" required />
          </div>
          
          <div class="campo-formulario">
            <label for="direccion">Dirección</label>
            <input type="text" id="direccion" name="direccion" 
                   value="<?php echo $action === 'editar' && $cliente_edicion ? htmlspecialchars($cliente_edicion['direccion'] ?? '') : ''; ?>" />
          </div>
          
          <div class="botones-formulario">
            <button type="submit"><?php echo $action === 'crear' ? 'Guardar Cliente' : 'Actualizar Cliente'; ?></button>
            <button type="button" onclick="location.href='?action=listar'" class="boton-cancelar">Cancelar</button>
          </div>
        </form>
      </div>

    <!-- =========================== -->
    <!-- TABLA DE CONSULTA / LISTADO -->
    <!-- =========================== -->
    <?php else: ?>
      
      <a href="?action=crear"><button style="margin-bottom: 20px;">+ Nuevo Cliente</button></a>
      
      <div class="tabla-container">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Email</th>
              <th>Dirección</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result->num_rows === 0): ?>
              <tr><td colspan="5" style="text-align: center;">No hay clientes registrados</td></tr>
            <?php else: ?>
              <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?php echo (int)$row['id_cliente']; ?></td>
                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['direccion'] ?? '-'); ?></td>
                <td>
                  <a href="?action=editar&id=<?php echo (int)$row['id_cliente']; ?>">Editar</a> |
                  <button onclick="eliminarCliente(<?php echo (int)$row['id_cliente']; ?>, '<?php echo htmlspecialchars($row['nombre'], ENT_QUOTES); ?>')" 
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
      <form id="form-eliminar" method="post" action="gestion_clientes.php?action=listar" style="display: none;">
        <input type="hidden" name="accion" value="eliminar" />
        <input type="hidden" name="id" id="id-eliminar" value="" />
      </form>

      <script>
        function eliminarCliente(id, nombre) {
          if (confirm('¿Está seguro de eliminar el cliente "' + nombre + '"?')) {
            document.getElementById('id-eliminar').value = id;
            document.getElementById('form-eliminar').submit();
          }
        }
      </script>

    <?php endif; ?>

  </div>
</body>
</html>