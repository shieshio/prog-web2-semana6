<?php
declare(strict_types=1);

require_once __DIR__ . '/config/sesion.php';

// Si ya está logueado, redirigir a mantenimiento
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: config/mantenimiento.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/config/conexion.php';
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Usuario y contraseña son obligatorios';
    } else {
        // Buscar usuario
        $stmt = $conn->prepare("SELECT id_admin, password_hash FROM ADMIN_USER WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            
            // Verificar contraseña
            if ($password === 'admin123')  {
                // Contraseña correcta - iniciar sesión admin
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $row['id_admin'];
                $_SESSION['admin_username'] = $username;
                
                header('Location: config/mantenimiento.php');
                exit;
            } else {
                $error = 'Credenciales incorrectas';
            }
        } else {
            $error = 'Credenciales incorrectas';
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Acceso Administrativo | Casa Morada</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <nav class="navegacion">
    <a href="index.php">Tienda</a>
    <a href="funciones/seguimiento.php">Seguimiento</a>
  </nav>

  <div style="max-width: 400px; margin: 50px auto;">
    <h1>Acceso Administrativo</h1>
    
    <?php if ($error !== ''): ?>
      <div class="mensaje-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="post" action="login.php" class="formulario-compra">
      <div class="campo-formulario">
        <label for="username">Usuario</label>
        <input type="text" id="username" name="username" required autofocus />
      </div>
      
      <div class="campo-formulario">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required />
      </div>
      
      <button type="submit">Ingresar</button>
    </form>
  </div>
</body>
</html>