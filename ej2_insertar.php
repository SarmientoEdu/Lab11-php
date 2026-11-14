<?php
/**
 * ej2_insertar.php — Ejercicio 2: Insertar datos con sentencia preparada
 * Lab 11 · Desarrollo Web
 */

require_once 'db_config.php';

$semilla = 876; // ← los mismos últimos 3 dígitos de tu cédula que en el Ej1

$computadora = gethostname();
$fechaHora   = date('Y-m-d H:i:s');

// ─── Cálculos derivados de la semilla ─────────────────────────────────────────
$totalProductos = ($semilla % 5) + 3;

try {
    $pdo = obtenerConexion();

    // Evita duplicados si recargas la página: borra lo previo de esta semilla
    $stmtLimpiar = $pdo->prepare("DELETE FROM productos WHERE semilla = ?");
    $stmtLimpiar->execute([$semilla]);

    // Sentencia preparada para el INSERT
    $stmt = $pdo->prepare(
        "INSERT INTO productos (nombre, precio, stock, semilla) VALUES (?, ?, ?, ?)"
    );

    $productosInsertados = [];

    for ($i = 0; $i < $totalProductos; $i++) {
        $nombre = "Producto_" . ($semilla * ($i + 1));
        $precio = round($semilla * 1.5 + $i * 3.75, 2);
        $stock  = ($semilla + $i * 7) % 50 + 1;

        $stmt->execute([$nombre, $precio, $stock, $semilla]);

        $id = (int)$pdo->lastInsertId();

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
        <p>Estudiante: Ceballos Sergio | Semilla: <?= $semilla ?></p>

        <h2>Productos insertados</h2>
        <table border="1">
            <tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Stock</th></tr>
            <?php foreach ($productosInsertados as $p): ?>
            <tr>
                <td><?= (int)$p['id'] ?></td>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= number_format($p['precio'], 2, '.', '') ?></td>
                <td><?= (int)$p['stock'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p>Total insertado: <?= count($productosInsertados) ?> registros</p>
        <p>Ejecutado en: <?= htmlspecialchars($computadora) ?> - <?= htmlspecialchars($fechaHora) ?></p>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    echo '<p style="color:red">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}