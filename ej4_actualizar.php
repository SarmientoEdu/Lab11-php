<?php
/**
 * ej4_actualizar.php — Ejercicio 4: Actualizar precios
 * Lab 11 · Desarrollo Web · Taller #5
 */

require_once 'db_config.php';

// Recibe la semilla por URL o usa 903 por defecto (evita hardcode)
$semilla = isset($_GET['semilla']) ? (int)$_GET['semilla'] : 903;

$computadora = gethostname();
$fechaHora   = date('Y-m-d H:i:s');

// Calcula el porcentaje de incremento: (semilla % 20) + 5
$porcentaje = ($semilla % 20) + 5;

try {
    $pdo = obtenerConexion();

    // ─── ANTES: obtén los precios actuales ────────────────────────────────────
    $stmtAntes = $pdo->prepare("SELECT id, nombre, precio FROM productos WHERE semilla = :semilla ORDER BY id");
    $stmtAntes->execute([':semilla' => $semilla]);
    $antes = $stmtAntes->fetchAll(PDO::FETCH_ASSOC);

    // ─── UPDATE: aplica el incremento ─────────────────────────────────────────
    // El cálculo se hace directamente en SQL como pide la plantilla
    $stmtUpdate = $pdo->prepare("UPDATE productos SET precio = precio * (1 + :porcentaje / 100) WHERE semilla = :semilla");
    $stmtUpdate->execute([
        ':porcentaje' => $porcentaje,
        ':semilla'    => $semilla
    ]);
    $filasActualizadas = $stmtUpdate->rowCount();

    // ─── DESPUÉS: obtén los precios nuevos ────────────────────────────────────
    $stmtDespues = $pdo->prepare("SELECT id, nombre, precio FROM productos WHERE semilla = :semilla ORDER BY id");
    $stmtDespues->execute([':semilla' => $semilla]);
    $despues = $stmtDespues->fetchAll(PDO::FETCH_ASSOC);

    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Ej4 - UPDATE</title>
        <style>
            body   { font-family: Arial, sans-serif; margin: 40px; }
            table  { border-collapse: collapse; width: 100%; max-width: 600px; }
            th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
            th     { background: #e65100; color: white; }
            .nuevo { color: #2e7d32; font-weight: bold; }
        </style>
    </head>
    <body>
        <h1>Ejercicio 4 — Actualización de Precios</h1>
        <p>Incremento aplicado: <strong><?= $porcentaje ?>%</strong></p>
        <table>
            <tr><th>Nombre</th><th>Precio anterior</th><th>Precio nuevo</th></tr>
            <?php
            // Combina $antes y $despues para mostrar la comparación
            foreach ($antes as $i => $prod):
                $precioNuevo = isset($despues[$i]) ? (float)$despues[$i]['precio'] : 0;
            ?>
            <tr>
                <td><?= htmlspecialchars($prod['nombre']) ?></td>
                <td>B/. <?= number_format((float)$prod['precio'], 2) ?></td>
                <td class="nuevo">B/. <?= number_format($precioNuevo, 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p>Total filas actualizadas: <strong><?= $filasActualizadas ?></strong></p>
        <p>Ejecutado en: <strong><?= htmlspecialchars($computadora) ?></strong>
           — <?= htmlspecialchars($fechaHora) ?></p>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    echo '<p style="color:red">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>