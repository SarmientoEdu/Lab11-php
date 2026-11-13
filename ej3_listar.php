<?php
/**
 * ej3_listar.php — Ejercicio 3: Seleccionar y mostrar datos
 * Lab 11 · Desarrollo Web · Taller #5
 */

require_once 'db_config.php';

$semilla = 242; // ← CAMBIA 823 por los últimos 3 dígitos de tu cédula

$computadora = gethostname();
$fechaHora   = date('Y-m-d H:i:s');

try {
    $pdo = obtenerConexion();

    $stmt = $pdo->prepare("SELECT * FROM productos WHERE semilla = :semilla ORDER BY precio ASC");
    $stmt->execute([':semilla' => $semilla]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $precios        = array_column($productos, 'precio');
    $precioPromedio = count($precios) > 0 ? array_sum($precios) / count($precios) : 0;
    $stockTotal     = array_sum(array_column($productos, 'stock'));

    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Ej3 - SELECT</title>
        <style>
            body   { font-family: Arial, sans-serif; margin: 40px; }
            table  { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #ccc; padding: 8px; }
            th     { background: #2e7d32; color: white; }
            tr:nth-child(even) td { background: #f5f5f5; }
            .resumen { background: #e8f5e9; padding: 15px; margin-top: 20px; border-radius: 6px; }
        </style>
    </head>
    <body>
        <h1>Ejercicio 3 — Listado de Productos</h1>
        <table>
            <tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Stock</th></tr>
            <?php foreach ($productos as $p): ?>
            <tr>
                <td><?= (int)$p['id'] ?></td>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td>B/. <?= number_format((float)$p['precio'], 2, '.', '') ?></td>
                <td><?= (int)$p['stock'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <div class="resumen">
            <p>Total de productos: <strong><?= count($productos) ?></strong></p>
            <p>Precio promedio: <strong>B/. <?= number_format($precioPromedio, 2, '.', '') ?></strong></p>
            <p>Stock total: <strong><?= $stockTotal ?> unidades</strong></p>
            <p>Semilla usada: <strong><?= $semilla ?></strong></p>
            <p>Ejecutado en: <strong><?= htmlspecialchars($computadora) ?></strong>
               — <?= htmlspecialchars($fechaHora) ?></p>
        </div>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    echo '<p style="color:red">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}