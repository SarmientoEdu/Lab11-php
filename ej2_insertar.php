\<?php
/**
 * ej2_insertar.php — Ejercicio 2: Insertar datos con sentencia preparada
 * Lab 11 · Desarrollo Web · Taller #5
 */

require_once 'db_config.php';

$semilla = 242; // ← CAMBIA 823 por los últimos 3 dígitos de tu cédula

$computadora = gethostname();
$fechaHora   = date('Y-m-d H:i:s');

// ─── Cálculos derivados de la semilla ─────────────────────────────────────────
$totalProductos = ($semilla % 5) + 3;

try {
    $pdo = obtenerConexion();

    // Evita duplicados si ejecutas el archivo más de una vez
    $pdo->prepare("DELETE FROM productos WHERE semilla = ?")->execute([$semilla]);

    $stmt = $pdo->prepare(
        "INSERT INTO productos (nombre, precio, stock, semilla) VALUES (?, ?, ?, ?)"
    );

    $productosInsertados = [];

    for ($i = 0; $i < $totalProductos; $i++) {
        $nombre = "Producto_" . ($semilla * ($i + 1));
        $precio = round($semilla * 1.5 + $i * 3.75, 2);
        $stock  = ($semilla + $i * 7) % 50 + 1;

        $stmt->execute([$nombre, $precio, $stock, $semilla]);

        $id = $pdo->lastInsertId();

        $productosInsertados[] = compact('id', 'nombre', 'precio', 'stock');
    }

    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Ej2 - INSERT</title>
        <style>
            body  { font-family: Arial, sans-serif; margin: 40px; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
            th { background: #4a90d9; color: white; }
        </style>
    </head>
    <body>
        <h1>Ejercicio 2 — Productos Insertados</h1>
        <table>
            <tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Stock</th></tr>
            <?php foreach ($productosInsertados as $p): ?>
            <tr>
                <td><?= (int)$p['id'] ?></td>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td>B/. <?= number_format($p['precio'], 2, '.', '') ?></td>
                <td><?= (int)$p['stock'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p>Total insertado: <strong><?= count($productosInsertados) ?> registros</strong></p>
        <p>Semilla usada: <strong><?= $semilla ?></strong></p>
        <p>Ejecutado en: <strong><?= htmlspecialchars($computadora) ?></strong>
           — <?= htmlspecialchars($fechaHora) ?></p>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    echo '<p style="color:red">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}