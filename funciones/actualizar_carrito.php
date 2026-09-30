<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/sesion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validar($_POST['csrf'] ?? null)) {
    http_response_code(403);
    exit('<p class="mensaje-error">Petición rechazada (token inválido).</p>');
}

$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'vaciar':
        unset($_SESSION['carrito']);
        header('Location: ../index.php?vaciado=1#carrito');
        break;

    case 'eliminar':
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id !== null && $id !== false && isset($_SESSION['carrito'][$id])) {
            unset($_SESSION['carrito'][$id]);
            header('Location: ../index.php?eliminado=1#carrito');
        }
        break;

    case 'actualizar':
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $unidades = filter_input(INPUT_POST, 'unidades', FILTER_VALIDATE_INT,
                                ['options' => ['min_range' => 1, 'max_range' => 20]]);
        if ($id !== null && $id !== false && $unidades !== null && $id !== false
            && isset($_SESSION['carrito'][$id])) {
            $_SESSION['carrito'][$id]['unidades'] = $unidades;
            header('Location: ../index.php?actualizado=1#carrito');
        }
        break;  
default:  // acción desconocida o validación fallida
    header('Location: ../index.php');
}
exit;

