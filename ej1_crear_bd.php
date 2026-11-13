
<?php
/**
 * Ejercicio 1: Crear base de datos y tabla
 * Estudiante: René Mariscal
 * Semilla personal: 565
 */

require_once 'db_config.php';

$semilla = 565;
$computadora = gethostname();
$fechaHora = date('Y-m-d H:i:s');

try {
    // Conexión sin seleccionar una base de datos
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=' . DB_CHARSET;

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Crear base de datos
    $pdo->exec("CREATE DATABASE IF NOT EXISTS tienda_lab11
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    $pdo->exec("USE tienda_lab11");

    // Crear tabla productos
    $sql = "CREATE TABLE IF NOT EXISTS productos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        precio DECIMAL(10,2) NOT NULL,
        stock INT NOT NULL DEFAULT 0,
        semilla INT NOT NULL
    )";

    $pdo->exec($sql);

    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
    echo '<title>Ejercicio 1</title></head><body>';

    echo '<h2>Creación de base de datos</h2>';
    echo '<p>Base de datos: tienda_lab11</p>';
    echo '<p>Tabla: productos</p>';
    echo '<p>Estado: Creada correctamente</p>';
    echo '<p>Semilla usada: ' . $semilla . '</p>';
    echo '<p>Estudiante: René Mariscal</p>';
    echo '<p>Ejecutado en: ' . htmlspecialchars($computadora) . ' - ' . $fechaHora . '</p>';

    echo '</body></html>';

} catch (PDOException $e) {
    echo 'Error: ' . htmlspecialchars($e->getMessage());
}
