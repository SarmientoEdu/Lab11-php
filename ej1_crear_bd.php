<?php
/**
 * ej1_crear_bd.php — Ejercicio 1: Crear base de datos y tabla
 * Lab 11 · Desarrollo Web · Lab11
 *
 * INSTRUCCIONES:
 * 1. Reemplaza TU_SEMILLA con los últimos 3 dígitos de tu cédula.
 * 2. Implementa las secciones marcadas con TODO.
 * 3. No uses mysqli_* ni frameworks.
 * 4. Usa PDO con try/catch para el manejo de errores.
 */

require_once 'db_config.php';

// ─── Semilla personal ─────────────────────────────────────────────────────────
$semilla = 511;

// ─── Datos de control personales (NO modificar estas líneas) ─────────────────
$computadora   = gethostname();
$fechaHora     = date('Y-m-d H:i:s');

// ─── Conexión para crear la base de datos ─────────────────────────────────────
try {
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $nombreBD    = DB_NAME;
    $nombreTabla = 'productos';

    // Crear la base de datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$nombreBD` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // Seleccionar la base de datos recién creada
    $pdo->exec("USE `$nombreBD`");

    // Crear la tabla productos si no existe
    $sqlTabla = "CREATE TABLE IF NOT EXISTS `$nombreTabla` (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nombre VARCHAR(100) NOT NULL,
        precio DECIMAL(10,2) NOT NULL,
        stock INT NOT NULL DEFAULT 0,
        semilla INT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $pdo->exec($sqlTabla);

    // ─── Salida HTML ──────────────────────────────────────────────────────────
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Ej1 - Crear BD</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .ok  { color: green; font-weight: bold; }
            .info { background: #f0f4ff; padding: 15px; border-radius: 6px; }
        </style>
    </head>
    <body>
        <h1>Ejercicio 1 — Crear Base de Datos</h1>
        <div class="info">
            <p>Base de datos: <strong><?= htmlspecialchars($nombreBD) ?></strong></p>
            <p>Tabla: <strong><?= htmlspecialchars($nombreTabla) ?></strong></p>
            <p class="ok">Estado: Creada correctamente</p>
            <p>Semilla usada: <strong><?= htmlspecialchars((string)$semilla) ?></strong></p>
            <p>Ejecutado en: <strong><?= htmlspecialchars($computadora) ?></strong>
               — <?= htmlspecialchars($fechaHora) ?></p>
        </div>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    echo '<p style="color:red">Error de conexión: ' . htmlspecialchars($e->getMessage()) . '</p>';
}