<?php
declare(strict_types=1);

require_once __DIR__ . '/conexion.php';

function obtenerCatalogo(): array {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id_producto, nombre, descripcion, precio, stock, categoria 
                            FROM PRODUCTO 
                            ORDER BY categoria, nombre");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $catalogo = [];
    while ($row = $result->fetch_assoc()) {
        $catalogo[] = [
            'id' => (int)$row['id_producto'],
            'nombre' => $row['nombre'],
            'descripcion' => $row['descripcion'] ?? '',
            'precio' => (float)$row['precio'],
            'stock' => (int)$row['stock'],
            'categoria' => $row['categoria'],
        ];
    }
    $stmt->close();
    return $catalogo;
}

function buscarProducto(array $catalogo, int $id): ?array {
    foreach ($catalogo as $p) {
        if ($p['id'] === $id) {
            return $p;
        }
    }
    return null;
}

function calcularDescuento(float $subtotal): float {
    if ($subtotal >= 1000000) return $subtotal * 0.15;
    if ($subtotal >= 500000)  return $subtotal * 0.10;
    if ($subtotal >= 200000)  return $subtotal * 0.05;
    return 0.0;
}

function totalCarrito(array $carrito): array {
    $subtotal = 0.0;
    $unidades = 0;
    foreach ($carrito as $linea) {
        $subtotal += $linea['precio'] * $linea['unidades'];
        $unidades += $linea['unidades'];
    }
    $descuento = calcularDescuento($subtotal);
    return [
        'subtotal' => $subtotal,
        'descuento' => $descuento,
        'total' => $subtotal - $descuento,
        'unidades' => $unidades
    ];
}

function formatearCLP(float $monto): string {
    return '$' . number_format($monto, 0, ',', '.');
}