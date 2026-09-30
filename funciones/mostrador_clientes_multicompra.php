<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
?>
<h2>Clientes con más de dos compras registradas (Consulta Avanzada)</h2>
<ul>
<?php
// INNER JOIN + GROUP BY + HAVING (S6F: consultas avanzadas)
$sql = "SELECT c.nombre, c.email, COUNT(co.id_compra) AS numero_compras
        FROM CLIENTE c
        INNER JOIN COMPRA co ON c.id_cliente = co.id_cliente
        GROUP BY c.id_cliente, c.nombre, c.email
        HAVING COUNT(co.id_compra) > 2
        ORDER BY numero_compras DESC";
$resultado = $conn->query($sql);

if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($fila["nombre"])
           . " (" . htmlspecialchars($fila["email"]) . ") — "
           . (int)$fila["numero_compras"] . " compras</li>";
    }
} else {
    echo "<li>No hay clientes con más de dos compras</li>";
}
$conn->close();
?>
</ul>

