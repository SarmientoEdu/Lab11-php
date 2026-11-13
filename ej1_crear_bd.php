<?php
require_once 'db_config.php';

$semilla = isset($_GET['semilla']) ? (int)$_GET['semilla'] : 903;

$computadora   = gethostname();
$fechaHora     = date('Y-m-d H:i:s');

try {
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS tienda_lab11 DEFAULT CHARACTER SET utf8mb4");
    $pdo->exec("USE tienda_lab11");

    $pdo->exec("CREATE TABLE IF NOT EXISTS productos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        precio DECIMAL(10,2) NOT NULL,
        stock INT NOT NULL DEFAULT 0,
        semilla INT NOT NULL
    )");
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
            <p>Semilla usada: <strong><?= $semilla ?></strong></p>
            <p>Ejecutado en: <strong><?= htmlspecialchars($computadora) ?></strong>
               — <?= htmlspecialchars($fechaHora) ?></p>
        </div>
    </body>
    </html>
    <?php
} catch (PDOException $e) {
    echo '<p style="color:red">Error de conexión: ' . htmlspecialchars($e->getMessage()) . '</p>';
}