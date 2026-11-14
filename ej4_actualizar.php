<?php
/**
 * ej4_actualizar.php — Ejercicio 4: Actualizar precios
 * Lab 11 · Desarrollo Web
 */

require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/Productos.php';

$semilla = 343;

$computadora = gethostname();
$fechaHora   = date('Y-m-d H:i:s');

$porcentaje = ($semilla % 20) + 5;

try {
    $pdo = obtenerConexion();

    $repositorio = new Productos($pdo);
    $resultado = $repositorio->actualizarPrecios($semilla, $porcentaje);
    $antes = $resultado['antes'];
    $despuesPorId = array_column($resultado['despues'], null, 'id');
    $filasActualizadas = $resultado['filasActualizadas'];

    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Ej4 - UPDATE</title>
        <style>
            body   { font-family: Arial, sans-serif; margin: 40px; }
            table  { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #ccc; padding: 8px; }
            th     { background: #e65100; color: white; }
            .nuevo { color: #2e7d32; font-weight: bold; }
        </style>
    </head>
    <body>
        <h1>Ejercicio 4 — Actualización de Precios</h1>
        <p>Estudiante: <strong>Alejandro Sáenz</strong> | Semilla usada: <strong><?= $semilla ?></strong></p>
        <p>Incremento aplicado: <strong><?= $porcentaje ?>%</strong></p>
        <table>
            <tr><th>Nombre</th><th>Precio anterior</th><th>Precio nuevo</th></tr>
            <?php
            foreach ($antes as $prod):
                $precioNuevo = $despuesPorId[$prod['id']]['precio'];
            ?>
            <tr>
                <td><?= htmlspecialchars($prod['nombre']) ?></td>
                <td>B/. <?= number_format((float)$prod['precio'], 2) ?></td>
                <td class="nuevo">B/. <?= number_format((float)$precioNuevo, 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p>Total filas actualizadas: <strong><?= $filasActualizadas ?></strong></p>
        <p>Ejecutado en: <strong><?= htmlspecialchars($computadora) ?></strong>
           — <?= htmlspecialchars($fechaHora) ?></p>
        <p>Cada ejecución aplica el incremento a los precios actuales.</p>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    http_response_code(500);
    echo '<p style="color:red">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
