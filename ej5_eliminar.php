<?php
/** Ejercicio 5: eliminar productos de la semilla cuyo stock es menor al umbral. */
require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/Productos.php';

$semilla = 343;

$computadora = gethostname();
$fechaHora   = date('Y-m-d H:i:s');
$umbral = ($semilla % 10) + 1;

try {
    $pdo = obtenerConexion();
    $repositorio = new Productos($pdo);
    $resultado = $repositorio->eliminarStockBajo($semilla, $umbral);
    $aEliminar = $resultado['eliminados'];
    $totalEliminados = $resultado['totalEliminados'];
    $restantes = $resultado['restantes'];
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Ej5 - DELETE</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
            th { background: #c62828; color: white; }
            .resumen { background: #fce4ec; padding: 15px; margin-top: 20px; border-radius: 6px; }
        </style>
    </head>
    <body>
        <h1>Ejercicio 5 — Eliminar Productos</h1>
        <p>Estudiante: <strong>Alejandro Sáenz</strong> | Semilla usada: <strong><?= $semilla ?></strong></p>
        <p>Umbral de stock: <strong><?= $umbral ?></strong>
            (se eliminan productos con stock &lt; <?= $umbral ?>)</p>

        <?php if (empty($aEliminar)): ?>
            <p>No hay productos con stock menor a <?= $umbral ?></p>
        <?php else: ?>
            <h2>Productos eliminados</h2>
            <table>
                <tr><th>ID</th><th>Nombre</th><th>Stock</th></tr>
                <?php foreach ($aEliminar as $p): ?>
                <tr>
                    <td><?= (int) $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['nombre'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td><?= (int) $p['stock'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <div class="resumen">
            <p>Total eliminados: <strong><?= $totalEliminados ?></strong></p>
            <p>Productos restantes: <strong><?= $restantes ?></strong></p>
            <p>Ejecutado en: <strong><?= htmlspecialchars($computadora) ?></strong>
                — <?= htmlspecialchars($fechaHora) ?></p>
        </div>
    </body>
    </html>
    <?php
} catch (PDOException $e) {
    http_response_code(500);
    echo '<p style="color:red">Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
}