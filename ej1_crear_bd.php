<?php
/**
 * ej1_crear_bd.php — Ejercicio 1: Crear base de datos y tabla
 * Lab 11 · Desarrollo Web
 */

require_once 'db_config.php';

// Semilla personal (últimos 3 dígitos de la cédula)
$semilla = 259;

// Datos de control
$computadora = gethostname();
$fechaHora   = date('Y-m-d H:i:s');

try {
    // Conexión SIN dbname para poder crear la BD
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Crear la base de datos si no existe
    $pdo->exec('CREATE DATABASE IF NOT EXISTS tienda_lab11 DEFAULT CHARACTER SET utf8mb4');

    // Seleccionar la base de datos
    $pdo->exec('USE tienda_lab11');

    // Crear la tabla productos si no existe
    $sqlTabla = "CREATE TABLE IF NOT EXISTS productos (
        id      INT AUTO_INCREMENT PRIMARY KEY,
        nombre  VARCHAR(100)   NOT NULL,
        precio  DECIMAL(10,2)  NOT NULL,
        stock   INT            NOT NULL DEFAULT 0,
        semilla INT            NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sqlTabla);

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
            <p>Base de datos: <strong>tienda_lab11</strong></p>
            <p>Tabla: <strong>productos</strong></p>
            <p class="ok">Estado: Creada correctamente</p>
            <p>Semilla usada: <strong><?= (int)$semilla ?></strong></p>
            <p>Ejecutado en: <strong><?= htmlspecialchars($computadora) ?></strong>
               — <?= htmlspecialchars($fechaHora) ?></p>
        </div>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    echo '<p style="color:red">Error de conexión: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
