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
// TODO: Reemplaza 0 con los últimos 3 dígitos de tu cédula (solo número)
$semilla = 356; // ← CAMBIA ESTO

// ─── Datos de control personales (NO modificar estas líneas) ─────────────────
$computadora   = gethostname();
$fechaHora     = date('Y-m-d H:i:s');

// ─── Conexión para crear la base de datos ─────────────────────────────────────
// NOTA: Para crear la BD necesitamos conectarnos SIN especificar dbname
try {
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    // TODO: Crear objeto PDO sin dbname usando las constantes de db_config.php:
    //       $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=' . DB_CHARSET;
    //       $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS tienda_lab11 DEFAULT CHARACTER SET utf8mb4");
    $pdo->exec("USE tienda_lab11");
    $pdo->exec("CREATE TABLE IF NOT EXISTS productos (
        id      INT AUTO_INCREMENT PRIMARY KEY,
        nombre  VARCHAR(100) NOT NULL,
        precio  DECIMAL(10,2) NOT NULL,
        stock   INT NOT NULL DEFAULT 0,
        semilla INT NOT NULL
    )"); // ← REEMPLAZA null con la conexión correcta

    // TODO: Ejecutar SQL para crear la base de datos si no existe
    // SQL: CREATE DATABASE IF NOT EXISTS tienda_lab11 DEFAULT CHARACTER SET utf8mb4

    // TODO: Seleccionar la base de datos recién creada
    // SQL: USE tienda_lab11

    // TODO: Ejecutar SQL para crear la tabla productos si no existe
    // La tabla debe tener: id (PK AI), nombre VARCHAR(100), precio DECIMAL(10,2),
    //                      stock INT DEFAULT 0, semilla INT
    // Todas las columnas excepto id deben ser NOT NULL

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
            <!-- TODO: Reemplaza los valores ??? con las variables correctas -->
            <p>Base de datos: <strong>tienda_lab11</strong></p>
            <p>Tabla: <strong>productos</strong></p>
            <p class="ok">Estado: Creada correctamente</p>
            <p>Semilla usada: <strong><?= $semilla ?></strong></p>
            <p>Ejecutado en: <strong><?= htmlspecialchars($computadora) ?></strong>
             — <?= htmlspecialchars($fechaHora) ?></p>
            <p>Estudiante: Rodriguez Camilo</p>
        </div>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    // TODO: Muestra el mensaje de error de forma segura
    echo '<p style="color:red">Error de conexión: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
