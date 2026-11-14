<?php
/**
 * ej5_eliminar.php — Ejercicio 5: Eliminar datos por umbral de stock
 * Lab 11 · Desarrollo Web
 */

require_once 'db_config.php';

$semilla = 876; // ← los mismos últimos 3 dígitos de tu cédula que en los otros ejercicios

$computadora = gethostname();
$fechaHora   = date('Y-m-d H:i:s');

// umbral = (semilla % 10) + 1
$umbral = ($semilla % 10) + 1;

try {
    $pdo = obtenerConexion();

    // Paso 1: qué se va a eliminar
    $stmtSelect = $pdo->prepare(
        "SELECT id, nombre, stock FROM productos WHERE semilla = ? AND stock < ? ORDER BY id"
    );
    $stmtSelect->execute([$semilla, $umbral]);
    $aEliminar = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);

    // Paso 2: eliminar (solo si hay algo)
    $totalEliminados = 0;
    if (!empty($aEliminar)) {
        $stmtDelete = $pdo->prepare("DELETE FROM productos WHERE semilla = ? AND stock < ?");
        $stmtDelete->execute([$semilla, $umbral]);
        $totalEliminados = $stmtDelete->rowCount();
    }

    // Paso 3: contar restantes
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE semilla = ?");
    $stmtCount->execute([$semilla]);
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
            tr:nth-child(even) td { background: #f5f5f5; }
            .resumen { background: #fce4ec; padding: 15px; margin-top: 20px; border-radius: 6px; }
        </style>
    </head>
    <body>
        <h1>Ejercicio 5 — Eliminar Productos</h1>
        <p>Estudiante: Ceballos Sergio | Semilla: <?= $semilla ?></p>

        <p>Umbral de stock: <?= $umbral ?></p>

        <?php if (empty($aEliminar)): ?>
            <p>No hay productos con stock menor a <?= $umbral ?></p>
        <?php else: ?>
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
        <?php endif; ?>

        <div class="resumen">
            <p>Total eliminados: <?= $totalEliminados ?></p>
            <p>Productos restantes: <?= $restantes ?></p>
            <p>Ejecutado en: <?= htmlspecialchars($computadora) ?> - <?= htmlspecialchars($fechaHora) ?></p>
        </div>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    echo '<p style="color:red">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}