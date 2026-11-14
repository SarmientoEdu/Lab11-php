<?php
/**
 * ej3_listar.php — Ejercicio 3: Seleccionar y mostrar datos
 * Lab 11 · Desarrollo Web
 */

require_once 'db_config.php';

$semilla = 876; // ← los mismos últimos 3 dígitos de tu cédula que en los otros ejercicios

$computadora = gethostname();
$fechaHora   = date('Y-m-d H:i:s');

function dinero($valor): string {
    return 'B/. ' . number_format((float)$valor, 2, '.', '');
}

try {
    $pdo = obtenerConexion();

    // SELECT preparado, ordenado por precio ascendente
    $stmt = $pdo->prepare(
        "SELECT id, nombre, precio, stock FROM productos WHERE semilla = :semilla ORDER BY precio ASC"
    );
    $stmt->execute([':semilla' => $semilla]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cálculos en PHP (no en SQL)
    $total          = count($productos);
    $precios        = array_map('floatval', array_column($productos, 'precio'));
    $precioPromedio = $total > 0 ? array_sum($precios) / $total : 0;
    $stockTotal     = (int)array_sum(array_column($productos, 'stock'));

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
        <p>Estudiante: Ceballos Sergio | Semilla: <?= $semilla ?></p>

        <table>
            <tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Stock</th></tr>
            <?php foreach ($productos as $p): ?>
            <tr>
                <td><?= (int)$p['id'] ?></td>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= dinero($p['precio']) ?></td>
                <td><?= (int)$p['stock'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <div class="resumen">
            <p>Total de productos: <?= $total ?></p>
            <p>Precio promedio: <?= dinero($precioPromedio) ?></p>
            <p>Stock total: <?= $stockTotal ?> unidades</p>
            <p>Ejecutado en: <?= htmlspecialchars($computadora) ?> - <?= htmlspecialchars($fechaHora) ?></p>
        </div>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    echo '<p style="color:red">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}