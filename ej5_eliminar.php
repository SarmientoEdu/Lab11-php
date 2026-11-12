<?php
/**
 * ej5_eliminar.php — Ejercicio 5: Eliminar datos por umbral de stock
 * Lab 11 · Desarrollo Web
 */

require_once 'db_config.php';

$semilla = 259;

$computadora = gethostname();
$fechaHora   = date('Y-m-d H:i:s');

// Umbral de stock
$umbral = ($semilla % 10) + 1;

try {
    $pdo = obtenerConexion();

    // Paso 1: productos a eliminar
    $stmtSel = $pdo->prepare(
        'SELECT id, nombre, stock
         FROM productos
         WHERE semilla = :s AND stock < :u'
    );
    $stmtSel->execute([':s' => $semilla, ':u' => $umbral]);
    $aEliminar = $stmtSel->fetchAll(PDO::FETCH_ASSOC);

    if (empty($aEliminar)) {
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Ej5 - DELETE</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                .info { background: #fce4ec; padding: 15px; border-radius: 6px; }
            </style>
        </head>
        <body>
            <h1>Ejercicio 5 — Eliminar Productos</h1>
            <div class="info">
                <p>Umbral de stock: <strong><?= (int)$umbral ?></strong></p>
                <p>No hay productos con stock menor a <?= (int)$umbral ?></p>
                <p>Productos restantes: <strong>0</strong></p>
                <p>Ejecutado en: <strong><?= htmlspecialchars($computadora) ?></strong>
                   — <?= htmlspecialchars($fechaHora) ?></p>
            </div>
        </body>
        </html>
        <?php
    } else {
        // Paso 2: eliminar
        $stmtDel = $pdo->prepare(
            'DELETE FROM productos WHERE semilla = :s AND stock < :u'
        );
        $stmtDel->execute([':s' => $semilla, ':u' => $umbral]);
        $totalEliminados = $stmtDel->rowCount();

        // Paso 3: contar restantes
        $stmtCnt = $pdo->prepare(
            'SELECT COUNT(*) FROM productos WHERE semilla = :s'
        );
        $stmtCnt->execute([':s' => $semilla]);
        $restantes = (int)$stmtCnt->fetchColumn();

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
            <p>Umbral de stock: <strong><?= (int)$umbral ?></strong>
               (se eliminan productos con stock &lt; <?= (int)$umbral ?>)</p>
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
                <p>Total eliminados: <strong><?= (int)$totalEliminados ?></strong></p>
                <p>Productos restantes: <strong><?= (int)$restantes ?></strong></p>
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
