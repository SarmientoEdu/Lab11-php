<?php
/**
 * ej4_actualizar.php — Ejercicio 4: Actualizar precios
 * Lab 11 · Desarrollo Web
 */

require_once 'db_config.php';

$semilla = 259;

$computadora = gethostname();
$fechaHora   = date('Y-m-d H:i:s');

// Porcentaje de incremento
$porcentaje = ($semilla % 20) + 5;

try {
    $pdo = obtenerConexion();

    // Antes
    $stmtAntes = $pdo->prepare(
        'SELECT id, nombre, precio FROM productos WHERE semilla = :s ORDER BY id'
    );
    $stmtAntes->execute([':s' => $semilla]);
    $antes = $stmtAntes->fetchAll(PDO::FETCH_ASSOC);

    // Update con el incremento
    $stmtUpd = $pdo->prepare(
        'UPDATE productos
         SET precio = ROUND(precio * (1 + :pct / 100), 2)
         WHERE semilla = :s'
    );
    $stmtUpd->execute([':pct' => $porcentaje, ':s' => $semilla]);
    $filasActualizadas = $stmtUpd->rowCount();

    // Despues
    $stmtDespues = $pdo->prepare(
        'SELECT id, nombre, precio FROM productos WHERE semilla = :s ORDER BY id'
    );
    $stmtDespues->execute([':s' => $semilla]);
    $despues = $stmtDespues->fetchAll(PDO::FETCH_ASSOC);

    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
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
        <p>Incremento aplicado: <strong><?= (int)$porcentaje ?>%</strong></p>
        <table>
            <tr><th>Nombre</th><th>Precio anterior</th><th>Precio nuevo</th></tr>
            <?php
            foreach ($antes as $i => $prod):
                $precioNuevo = isset($despues[$i]) ? $despues[$i]['precio'] : 0;
            ?>
            <tr>
                <td><?= htmlspecialchars($prod['nombre']) ?></td>
                <td>B/. <?= number_format((float)$prod['precio'], 2) ?></td>
                <td class="nuevo">B/. <?= number_format((float)$precioNuevo, 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p>Total filas actualizadas: <strong><?= (int)$filasActualizadas ?></strong></p>
        <p>Ejecutado en: <strong><?= htmlspecialchars($computadora) ?></strong>
           — <?= htmlspecialchars($fechaHora) ?></p>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    echo '<p style="color:red">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
