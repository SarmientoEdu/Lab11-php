<?php
/**
 * ej3_listar.php — Ejercicio 3: Seleccionar y mostrar datos
 * Lab 11 · Desarrollo Web
 */

require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/Productos.php';

$semilla = 343;

$computadora = gethostname();
$fechaHora   = date('Y-m-d H:i:s');

try {
    $pdo = obtenerConexion();

    $repositorio = new Productos($pdo);
    $productos = $repositorio->listar($semilla);

    $precios = array_column($productos, 'precio');
    $precioPromedio = count($productos) > 0 ? array_sum($precios) / count($productos) : 0;
    $stockTotal = array_sum(array_column($productos, 'stock'));

    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
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
        <p>Estudiante: <strong>Alejandro Sáenz</strong> | Semilla usada: <strong><?= $semilla ?></strong></p>
        <table>
            <tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Stock</th></tr>
            <?php foreach ($productos as $p): ?>
            <tr>
                <td><?= (int)$p['id'] ?></td>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td>B/. <?= number_format((float)$p['precio'], 2) ?></td>
                <td><?= (int)$p['stock'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <div class="resumen">
            <p>Total de productos: <strong><?= count($productos) ?></strong></p>
            <p>Precio promedio: <strong>B/. <?= number_format($precioPromedio, 2) ?></strong></p>
            <p>Stock total: <strong><?= $stockTotal ?> unidades</strong></p>
            <p>Ejecutado en: <strong><?= htmlspecialchars($computadora) ?></strong>
               — <?= htmlspecialchars($fechaHora) ?></p>
        </div>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    http_response_code(500);
    echo '<p style="color:red">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
