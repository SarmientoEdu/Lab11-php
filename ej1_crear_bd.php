<?php
/**
 * ej1_crear_bd.php — Ejercicio 1: Crear base de datos y tabla
 * Lab 11 · Desarrollo Web · Taller #5
 *
 * INSTRUCCIONES:
 * 1. Reemplaza TU_SEMILLA con los últimos 3 dígitos de tu cédula.
 * 2. Implementa las secciones marcadas con TODO.
 * 3. No uses mysqli_* ni frameworks.
 * 4. Usa PDO con try/catch para el manejo de errores.
 */

// ─── Semilla personal ─────────────────────────────────────────────────────────
// TODO: Reemplaza 0 con los últimos 3 dígitos de tu cédula (solo número)
$semilla = 0; // ← CAMBIA ESTO

// ─── Datos de control personales (NO modificar estas líneas) ─────────────────
$computadora   = gethostname();
$fechaHora     = date('Y-m-d H:i:s');

// ─── Conexión para crear la base de datos ─────────────────────────────────────
// NOTA: Para crear la BD necesitamos conectarnos SIN especificar dbname
// TODO: Crear una conexión PDO a localhost sin especificar la base de datos
//       DSN: 'mysql:host=localhost;charset=utf8mb4'
//       Captura PDOException y muestra el mensaje de error

try {
    // TODO: Crear objeto PDO (sin dbname en el DSN)
    $pdo = null; // ← REEMPLAZA null con la conexión correcta

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
            <p>Base de datos: <strong>???</strong></p>
            <p>Tabla: <strong>???</strong></p>
            <p class="ok">Estado: Creada correctamente</p>
            <p>Semilla usada: <strong>???</strong></p>
            <p>Ejecutado en: <strong><?= htmlspecialchars($computadora) ?></strong>
               — <?= htmlspecialchars($fechaHora) ?></p>
        </div>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    // TODO: Muestra el mensaje de error de forma segura
    echo '<p style="color:red">Error de conexión: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
