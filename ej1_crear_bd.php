<?php
/**
 * ej1_crear_bd.php — Ejercicio 1: Crear base de datos y tabla
 * Lab 11 · Desarrollo Web
 */

require_once 'db_config.php';

// ─── Semilla personal ─────────────────────────────────────────────────────────
$semilla = 876; // ← últimos 3 dígitos de tu cédula (usa el mismo en todos los ejercicios)

// ─── Datos de control personales (NO modificar estas líneas) ─────────────────
$computadora   = gethostname();
$fechaHora     = date('Y-m-d H:i:s');

try {
    // Conexión SIN dbname, porque la base de datos aún puede no existir
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Crear la base de datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS tienda_lab11 DEFAULT CHARACTER SET utf8mb4");

    // Seleccionar la base de datos
    $pdo->exec("USE tienda_lab11");

    // Crear la tabla si no existe
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS productos (
            id      INT AUTO_INCREMENT PRIMARY KEY,
            nombre  VARCHAR(100)   NOT NULL,
            precio  DECIMAL(10,2)  NOT NULL,
            stock   INT            NOT NULL DEFAULT 0,
            semilla INT            NOT NULL
        )
    ");

    // ─── Salida HTML ──────────────────────────────────────────────────────────
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Ej1 - Crear BD</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .info { background: #f0f4ff; padding: 15px; border-radius: 6px; }
        </style>
    </head>
    <body>
        <h1>Ejercicio 1 — Crear Base de Datos</h1>
        <p>Estudiante: Ceballos Sergio | Semilla: <?= $semilla ?></p>
        <div class="info">
            <p>Base de datos: tienda_lab11</p>
            <p>Tabla: productos</p>
            <p>Estado: Creada correctamente</p>
            <p>Semilla usada: <?= $semilla ?></p>
            <p>Ejecutado en: <?= htmlspecialchars($computadora) ?> - <?= htmlspecialchars($fechaHora) ?></p>
        </div>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    echo '<p style="color:red">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}