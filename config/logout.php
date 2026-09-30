<?php
declare(strict_types=1);

// 1. Inicializar/Reanudar la sesión para poder destruirla
require_once __DIR__ . '/sesion.php';

// 2. Vaciar el arreglo global
$_SESSION = [];
session_unset();

// 3. Destrucción ABSOLUTA de la cookie usando sus parámetros originales
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Destruir el archivo físico de sesión en el servidor
session_destroy();

// 5. Prevenir que el navegador guarde la página de logout en caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 6. Manejo dinámico de redirección (El parámetro opcional)
// Se espera que los botones de logout envíen un GET. Ej: logout.php?redirect=admin
$redirect = filter_input(INPUT_GET, 'redirect', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if ($redirect === 'admin') {
    // Si viene del panel de administración, lo mandamos al login
    header('Location: ../login.php');
} else {
    // Comportamiento por defecto (clientes regulares)
    header('Location: ../index.php');
}
exit;

