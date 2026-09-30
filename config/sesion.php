<?php
declare(strict_types=1);

/* Configuración de tiempo de vida para evitar expiración prematura */
ini_set('session.gc_maxlifetime', '7200');
ini_set('session.cookie_lifetime', '7200');
session_cache_limiter('private');
session_cache_expire(120);

// Verificar que session_start() se llama antes de cualquier output, ya que esto puede causar errores de encabezado, donde la peticion POST no se puede procesar correctamente ocasionando las siguientes errores: validación CSRF fallida. El token enviado desde el navegador no coincide con el almacenado en la sesión del servidor.
session_name('CASA_MORADA_SID');
session_start();

/* Cookie endurecida (HttpOnly, Secure, SameSite) */
session_set_cookie_params([
    'lifetime' => 7200,
    'path'     => '/',
    'httponly' => true,
    'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Strict',
]);


/* ============================================
 * MEJORA: VINCULACIÓN AL AGENTE DE USUARIO
 * Invalida sesión si el cliente cambia (anti hijacking)
 * ============================================ */
$huella_agente = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');

if (!isset($_SESSION['huella_agente'])) {
    $_SESSION['huella_agente'] = $huella_agente;
} elseif (!hash_equals($_SESSION['huella_agente'], $huella_agente)) {
    /* Cambio imprevisto de entorno: invalidar sesión */
    session_unset();
    session_destroy();
    exit('<p class="mensaje-error">Sesión invalidada: entorno del cliente modificado.</p>');
}

/* Rotación periódica del ID de sesión */
if (!isset($_SESSION['creada_en'])) {
    $_SESSION['creada_en'] = time();
} elseif (time() - $_SESSION['creada_en'] > 300) {
    session_regenerate_id(true);
    $_SESSION['creada_en'] = time();
}

/* Función filtro: trim, stripslashes, htmlspecialchars */
function filtro(string $dato): string {
    $dato = trim($dato);
    $dato = stripslashes($dato);
    return htmlspecialchars($dato, ENT_QUOTES, 'UTF-8');
}

/* CSRF Token */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/* CSRF Validación */
function csrf_validar(?string $token): bool {
    return isset($_SESSION['csrf']) && is_string($token)
        && hash_equals($_SESSION['csrf'], $token);
}