<?php
/**
 * db_config.php — Configuración de conexión PDO
 * Lab 11 · Desarrollo Web · Lab 11
 *
 * INSTRUCCIONES DE SEGURIDAD:
 * - Este archivo NUNCA debe subirse con credenciales reales de producción.
 * - En XAMPP local, MySQL no tiene contraseña por defecto.
 * - Incluir con require_once 'db_config.php'; desde cada ejercicio.
 */

// ─── Parámetros de conexión ───────────────────────────────────────────────────
define('DB_HOST',    getenv('DB_HOST') ?: '127.0.0.1'); // TCP/IP (compatible con XAMPP y CI)
define('DB_PORT',    getenv('DB_PORT') ?: '3306');
define('DB_USER',    getenv('DB_USER') ?: 'root');
define('DB_PASS',    getenv('DB_PASS') !== false ? getenv('DB_PASS') : ''); // Sin contraseña por defecto
define('DB_NAME',    getenv('DB_NAME') ?: 'tienda_lab11');
define('DB_CHARSET', 'utf8mb4');

/**
 * Retorna una conexión PDO configurada.
 * Lanza PDOException si la conexión falla.
 *
 * @return PDO
 */
function obtenerConexion(): PDO {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
    );
    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    return new PDO($dsn, DB_USER, DB_PASS, $opciones);
}