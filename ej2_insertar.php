<?php
/**
 * ej2_insertar.php — Ejercicio 2: Insertar datos con sentencia preparada
 * Lab 11 · Desarrollo Web · Lab 11
 */

require_once 'db_config.php';

// TODO: Reemplaza 0 con los últimos 3 dígitos de tu cédula
$semilla = 114; // ← CAMBIA ESTO (Tu semilla)

$computadora = gethostname();
$fechaHora   = date('Y-m-d H:i:s');

// ─── Cálculos derivados de la semilla ─────────────────────────────────────────
// TODO: Calcula cuántos productos insertar: (semilla % 5) + 3
$totalProductos = ($semilla % 5) + 3; // ← IMPLEMENTA LA FÓRMULA

try {
    $pdo = obtenerConexion();

    // TODO: Prepara la sentencia INSERT con placeholders
    $stmt = $pdo->prepare("INSERT INTO productos (nombre, precio, stock, semilla) 
                           VALUES (:nombre, :precio, :stock, :semilla)"); // ← REEMPLAZA con $pdo->prepare(...)

    $productosInsertados = [];

    for ($i = 0; $i < $totalProductos; $i++) {
        // TODO: Calcula los valores para cada producto usando la semilla:
        $nombre = "Producto_" . ($semilla * ($i + 1)); // ← IMPLEMENTA
        $precio = round($semilla * 1.5 + $i * 3.75, 2); // ← IMPLEMENTA
        $stock  = ($semilla + $i * 7) % 50 + 1; // ← IMPLEMENTA

        // TODO: Ejecuta la sentencia preparada con los valores calculados
        $stmt->execute([
            ':nombre'  => $nombre,
            ':precio'  => $precio,
            ':stock'   => $stock,
            ':semilla' => $semilla
        ]);

        // TODO: Guarda el id del último insertado
        $id = (int)$pdo->lastInsertId(); // ← usa $pdo->lastInsertId()

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
        <!-- Ajustado para coincidir EXACTAMENTE con el formato de la guía -->
        <h2>Productos insertados</h2>
        <table border="1">
            <tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Stock</th></tr>
            <?php foreach ($productosInsertados as $p): ?>
            <tr>
                <td><?= (int)$p['id'] ?></td>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <!-- TODO: Formatea precio como B/. XXXX.XX -->
                <td>B/. <?= number_format($p['precio'], 2) ?></td>
                <td><?= (int)$p['stock'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p>Total insertado: <strong><?= count($productosInsertados) ?> registros</strong></p>
        <p>Ejecutado en: <strong><?= htmlspecialchars($computadora) ?></strong>
           — <?= htmlspecialchars($fechaHora) ?></p>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    echo '<p style="color:red">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}