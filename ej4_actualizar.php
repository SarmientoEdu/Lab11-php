<?php
/**
 * ej4_actualizar.php — Ejercicio 4: Actualizar precios
 * Lab 11 · Desarrollo Web
 */

require_once 'db_config.php';

$semilla = 876; // ← los mismos últimos 3 dígitos de tu cédula que en los otros ejercicios

$computadora = gethostname();
$fechaHora   = date('Y-m-d H:i:s');

// porcentaje = (semilla % 20) + 5
$porcentaje = ($semilla % 20) + 5;
$factor     = 1 + $porcentaje / 100;

function dinero($valor): string {
    return 'B/. ' . number_format((float)$valor, 2, '.', '');
}

try {
    $pdo = obtenerConexion();

    // ANTES
    $stmtSelect = $pdo->prepare(
        "SELECT id, nombre, precio FROM productos WHERE semilla = ? ORDER BY id"
    );
    $stmtSelect->execute([$semilla]);
    $antes = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);

    // UPDATE
    $stmtUpdate = $pdo->prepare(
        "UPDATE productos SET precio = ROUND(precio * ?, 2) WHERE semilla = ?"
    );
    $stmtUpdate->execute([$factor, $semilla]);
    $filasActualizadas = $stmtUpdate->rowCount();

    // DESPUÉS (indexado por id)
    $stmtSelect->execute([$semilla]);
    $despues = [];
    foreach ($stmtSelect->fetchAll(PDO::FETCH_ASSOC) as $fila) {
        $despues[$fila['id']] = $fila['precio'];
    }
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
            tr:nth-child(even) td { background: #f5f5f5; }
            .nuevo { color: #2e7d32; font-weight: bold; }
        </style>
    </head>
    <body>
        <h1>Ejercicio 4 — Actualización de Precios</h1>
        <p>Estudiante: Ceballos Sergio | Semilla: <?= $semilla ?></p>

        <p>Incremento aplicado: <?= $porcentaje ?>%</p>

        <table>
            <tr><th>Nombre</th><th>Precio anterior</th><th>Precio nuevo</th></tr>
            <?php foreach ($antes as $prod): ?>
            <tr>
                <td><?= htmlspecialchars($prod['nombre']) ?></td>
                <td><?= dinero($prod['precio']) ?></td>
                <td class="nuevo"><?= dinero($despues[$prod['id']] ?? 0) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <p>Total filas actualizadas: <?= $filasActualizadas ?></p>
        <p>Ejecutado en: <?= htmlspecialchars($computadora) ?> - <?= htmlspecialchars($fechaHora) ?></p>
    </body>
    </html>
    <?php
} catch (PDOException $e) {
    echo '<p style="color:red">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}