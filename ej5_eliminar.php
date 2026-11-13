<?php
/**
 * ej5_eliminar.php — Ejercicio 5: Eliminar datos por umbral de stock
 * Lab 11 · Desarrollo Web · Taller #5
 */

require_once 'db_config.php';

$semilla = 242; // semilla actual
$estudiante = 'Apellido Nombre'; // ← PON AQUÍ TU NOMBRE (se ve en la captura)

$computadora = gethostname();
$fechaHora   = date('Y-m-d H:i:s');

$umbral = ($semilla % 10) + 1;

try {
    $pdo = obtenerConexion();

    // ─── Paso 1: Busca qué se va a eliminar ───────────────────────────────────
    $sel = $pdo->prepare("SELECT id, nombre, stock FROM productos WHERE semilla = ? AND stock < ?");
    $sel->execute([$semilla, $umbral]);
    $aEliminar = $sel->fetchAll(PDO::FETCH_ASSOC);

    // ─── Paso 2: Elimina (solo si hay algo) ───────────────────────────────────
    $totalEliminados = 0;
    if (!empty($aEliminar)) {
        $del = $pdo->prepare("DELETE FROM productos WHERE semilla = ? AND stock < ?");
        $del->execute([$semilla, $umbral]);
        $totalEliminados = $del->rowCount();
    }

    // ─── Paso 3: Cuenta los restantes ────────────────────────────────────────
    $cnt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE semilla = ?");
    $cnt->execute([$semilla]);
    $restantes = (int)$cnt->fetchColumn();

    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Ej5 - DELETE - <?= htmlspecialchars($estudiante) ?></title>
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
        <h3>Estudiante: <?= htmlspecialchars($estudiante) ?> · Semilla: <?= $semilla ?></h3>

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
                    <td><?= (int)$p['id'] ?></td>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= (int)$p['stock'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <div class="resumen">
            <p>Total eliminados: <strong><?= $totalEliminados ?></strong></p>
            <p>Productos restantes: <strong><?= $restantes ?></strong></p>
            <p>Semilla usada: <strong><?= $semilla ?></strong></p>
            <p>Ejecutado en: <strong><?= htmlspecialchars($computadora) ?></strong>
               — <?= htmlspecialchars($fechaHora) ?></p>
        </div>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    echo '<p style="color:red">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}