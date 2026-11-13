<?php
/**
 * ej5_eliminar.php — Ejercicio 5: Eliminar datos por umbral de stock
 * Lab 11 · Desarrollo Web · Taller #5
 */

require_once 'db_config.php';

// Semilla personal (últimos 3 dígitos de cédula)
$semilla = 511;

$computadora = gethostname();
$fechaHora   = date('Y-m-d H:i:s');

// Umbral: (semilla % 10) + 1
$umbral = ($semilla % 10) + 1;

try {
    $pdo = obtenerConexion();

    // ─── Paso 1: Busca qué se va a eliminar ───────────────────────────────────
    $stmtSelect = $pdo->prepare('SELECT id, nombre, stock FROM productos WHERE semilla = :semilla AND stock < :umbral ORDER BY id');
    $stmtSelect->execute([
        ':semilla' => $semilla,
        ':umbral'  => $umbral,
    ]);
    $aEliminar = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);

    if (empty($aEliminar)) {
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Ej5 - DELETE</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                .resumen { background: #fce4ec; padding: 15px; margin-top: 20px; border-radius: 6px; }
            </style>
        </head>
        <body>
            <h1>Ejercicio 5 — Eliminar Productos</h1>
            <p>Umbral de stock: <strong><?= $umbral ?></strong></p>
            <p>No hay productos con stock menor a <?= $umbral ?></p>
            <div class="resumen">
                <p>Ejecutado en: <strong><?= htmlspecialchars($computadora) ?></strong>
                   — <?= htmlspecialchars($fechaHora) ?></p>
            </div>
        </body>
        </html>
        <?php
    } else {
        // ─── Paso 2: Elimina ──────────────────────────────────────────────────
        $stmtDelete = $pdo->prepare('DELETE FROM productos WHERE semilla = :semilla AND stock < :umbral');
        $stmtDelete->execute([
            ':semilla' => $semilla,
            ':umbral'  => $umbral,
        ]);
        $totalEliminados = $stmtDelete->rowCount();

        // ─── Paso 3: Cuenta los restantes ────────────────────────────────────
        $stmtCount = $pdo->prepare('SELECT COUNT(*) FROM productos WHERE semilla = :semilla');
        $stmtCount->execute([':semilla' => $semilla]);
        $restantes = (int)$stmtCount->fetchColumn();

        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Ej5 - DELETE</title>
            <style>
                body   { font-family: Arial, sans-serif; margin: 40px; }
                table  { border-collapse: collapse; width: 70%; }
                th, td { border: 1px solid #ccc; padding: 8px; }
                th     { background: #c62828; color: white; }
                .resumen { background: #fce4ec; padding: 15px; margin-top: 20px; border-radius: 6px; }
            </style>
        </head>
        <body>
            <h1>Ejercicio 5 — Eliminar Productos</h1>
            <p>Umbral de stock: <strong><?= $umbral ?></strong>
               (se eliminan productos con stock &lt; <?= $umbral ?>)</p>
            <h2>Productos eliminados</h2>
            <table>
                <tr><th>ID</th><th>Nombre</th><th>Stock</th></tr>
                <?php foreach ($aEliminar as $p): ?>
                <tr>
                    <td><?= (int)$p['id'] ?></td>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= (int)$p['stock'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <div class="resumen">
                <p>Total eliminados: <strong><?= $totalEliminados ?></strong></p>
                <p>Productos restantes: <strong><?= $restantes ?></strong></p>
                <p>Ejecutado en: <strong><?= htmlspecialchars($computadora) ?></strong>
                   — <?= htmlspecialchars($fechaHora) ?></p>
            </div>
        </body>
        </html>
        <?php
    }

} catch (PDOException $e) {
    echo '<p style="color:red">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}