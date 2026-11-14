<?php
/**
 * ej2_insertar.php — Ejercicio 2: Insertar datos con sentencia preparada
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
    $productosInsertados = $repositorio->insertar($semilla);

    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
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
        <p>Estudiante: <strong>Alejandro Sáenz</strong> | Semilla usada: <strong><?= $semilla ?></strong></p>
        <table>
            <tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Stock</th></tr>
            <?php foreach ($productosInsertados as $p): ?>
            <tr>
                <td><?= (int)$p['id'] ?></td>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td>B/. <?= number_format($p['precio'], 2) ?></td>
                <td><?= (int)$p['stock'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p>Total insertado: <strong><?= count($productosInsertados) ?> registros</strong></p>
        <p>Ejecutado en: <strong><?= htmlspecialchars($computadora) ?></strong>
           — <?= htmlspecialchars($fechaHora) ?></p>
        <p>Cada ejecución agrega un lote de productos. Para consultar los guardados, abra el ejercicio 3.</p>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    http_response_code(500);
    echo '<p style="color:red">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
