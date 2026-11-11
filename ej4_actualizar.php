<?php
/**
 * ej4_actualizar.php — Ejercicio 4: Actualizar precios
 * Lab 11 · Desarrollo Web · Taller #5
 */

require_once 'db_config.php';

// TODO: Reemplaza 0 con los últimos 3 dígitos de tu cédula
$semilla = 0; // ← CAMBIA ESTO

$computadora = gethostname();
$fechaHora   = date('Y-m-d H:i:s');

// TODO: Calcula el porcentaje de incremento: (semilla % 20) + 5
$porcentaje = 0; // ← IMPLEMENTA LA FÓRMULA

try {
    $pdo = obtenerConexion();

    // ─── ANTES: obtén los precios actuales ────────────────────────────────────
    // TODO: SELECT id, nombre, precio WHERE semilla = ? ORDER BY id
    $antes = []; // ← fetchAll(PDO::FETCH_ASSOC)

    // ─── UPDATE: aplica el incremento ─────────────────────────────────────────
    // TODO: Prepara un UPDATE que calcule:
    //   nuevo_precio = precio * (1 + porcentaje / 100)
    //   WHERE semilla = :semilla
    // Usa sentencia preparada PDO
    $filasActualizadas = 0; // ← $stmt->rowCount()

    // ─── DESPUÉS: obtén los precios nuevos ────────────────────────────────────
    // TODO: Mismo SELECT de antes pero ahora los valores son los nuevos
    $despues = []; // ← fetchAll(PDO::FETCH_ASSOC)

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
        <p>Incremento aplicado: <strong><?= $porcentaje ?>%</strong></p>
        <table>
            <tr><th>Nombre</th><th>Precio anterior</th><th>Precio nuevo</th></tr>
            <?php
            // TODO: Combina $antes y $despues para mostrar la comparación
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
        <p>Total filas actualizadas: <strong><?= $filasActualizadas ?></strong></p>
        <p>Ejecutado en: <strong><?= htmlspecialchars($computadora) ?></strong>
           — <?= htmlspecialchars($fechaHora) ?></p>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    echo '<p style="color:red">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
