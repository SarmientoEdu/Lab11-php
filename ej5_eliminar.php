
<?php
/**
 * Ejercicio 5: Eliminar datos y confirmar resultados
 * Estudiante: René Mariscal
 * Semilla personal: 565
 */

require_once 'db_config.php';

$semilla = 565;
$umbral = ($semilla % 10) + 1;

$computadora = gethostname();
$fechaHora = date('Y-m-d H:i:s');

try {
    $conexion = new PDO(
        "mysql:host=" . DB_HOST .
        ";port=" . DB_PORT .
        ";dbname=tienda_lab11;charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Iniciar transacción para mantener la consistencia
    $conexion->beginTransaction();

    // Buscar productos que se eliminarán
    $consulta = $conexion->prepare(
        "SELECT id, nombre, stock
         FROM productos
         WHERE semilla = :semilla AND stock < :umbral
         ORDER BY id ASC"
    );

    $consulta->bindValue(':semilla', $semilla, PDO::PARAM_INT);
    $consulta->bindValue(':umbral', $umbral, PDO::PARAM_INT);
    $consulta->execute();

    $productos = $consulta->fetchAll();

    // Eliminar productos
    $eliminar = $conexion->prepare(
        "DELETE FROM productos
         WHERE semilla = :semilla AND stock < :umbral"
    );

    $eliminar->bindValue(':semilla', $semilla, PDO::PARAM_INT);
    $eliminar->bindValue(':umbral', $umbral, PDO::PARAM_INT);
    $eliminar->execute();

    $totalEliminados = $eliminar->rowCount();

    // Contar productos restantes
    $conteo = $conexion->prepare(
        "SELECT COUNT(*)
         FROM productos
         WHERE semilla = :semilla"
    );

    $conteo->execute([':semilla' => $semilla]);
    $restantes = (int)$conteo->fetchColumn();

    // Confirmar los cambios realizados
    $conexion->commit();

    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
    echo '<title>Ejercicio 5</title></head><body>';

    echo '<h2>Eliminación de productos</h2>';
    echo '<p>Umbral de stock: ' . $umbral . '</p>';

    if (count($productos) > 0) {

        echo '<p>Productos eliminados:</p>';
        echo "<table border='1' cellpadding='8'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Stock</th></tr>";

        foreach ($productos as $producto) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars((string)$producto['id']) . "</td>";
            echo "<td>" . htmlspecialchars($producto['nombre']) . "</td>";
            echo "<td>" . htmlspecialchars((string)$producto['stock']) . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<p>No hay productos con stock menor a $umbral</p>";
    }

    echo "<p>Total eliminados: $totalEliminados</p>";
    echo "<p>Productos restantes: $restantes</p>";

    echo '<p>Semilla usada: ' . $semilla . '</p>';
    echo '<p>Estudiante: René Mariscal</p>';

    echo '<p>Ejecutado en: ' .
         htmlspecialchars($computadora) . ' - ' .
         $fechaHora . '</p>';

    echo '</body></html>';

} catch (PDOException $error) {

    // Deshacer los cambios si ocurre un error
    if (isset($conexion) && $conexion->inTransaction()) {
        $conexion->rollBack();
    }

    echo "Error: " . htmlspecialchars($error->getMessage());
}
