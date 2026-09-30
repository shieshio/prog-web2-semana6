<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/sesion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validar($_POST['csrf'] ?? null)) {
    http_response_code(403);
    echo "<p class='mensaje-error'>Petición rechazada (token inválido).</p>";
    exit;
}

$producto = filtro($_POST['producto'] ?? '');
$calificacion = filter_input(INPUT_POST, 'calificacion', FILTER_VALIDATE_INT,
                             ['options' => ['min_range' => 1, 'max_range' => 5]]);
$comentario = filtro($_POST['comentario'] ?? '');
$num_pedido = filter_input(INPUT_POST, 'num_pedido', FILTER_VALIDATE_INT);

if ($producto === '' || $comentario === ''
    || $calificacion === null || $calificacion === false
    || $num_pedido === null || $num_pedido === false) {
    echo "<p class='mensaje-error'>Datos de reseña inválidos.</p>";
    echo "<a href='seguimiento.php'>Volver</a>";
    exit;
}

class Resena {
    private string $producto;
    private int $calificacion;
    private string $comentario;
    private string $fecha;
    private int $numPedido;

    public function __construct(int $numPedido, string $prod, int $calif, string $coment) {
        $this->numPedido = $numPedido;
        $this->producto = $prod;
        $this->calificacion = $calif;
        $this->comentario = $coment;
        $this->fecha = date('Y-m-d');
    }

    public function mostrarResena(): string {
        return "Producto: {$this->producto}<br>"
             . "Calificación: ⭐{$this->calificacion}/5<br>"
             . "Comentario: {$this->comentario}<br>"
             . "Fecha: {$this->fecha}";
    }

    public function toArray(): array {
        return [
            'producto' => $this->producto,
            'calificacion' => $this->calificacion,
            'comentario' => $this->comentario,
            'fecha' => $this->fecha,
        ];
    }
}

$resena = new Resena($num_pedido, $producto, $calificacion, $comentario);

$_SESSION['resenas'][] = [
    'num_pedido' => $num_pedido,
    'resena' => $resena->toArray(),
];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reseña Enviada</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <nav class="navegacion">
        <a href="../index.php">Tienda</a>
        <a href="seguimiento.php">Seguimiento de Pedidos</a>
    </nav>

    <div class="mensaje-exito">
        ¡Tu reseña ha sido enviada con éxito!<br><br>
        <?php echo $resena->mostrarResena(); ?>
    </div>

    <br>
    <a href="../index.php"><button>Volver a la Tienda</button></a>
</body>
</html>