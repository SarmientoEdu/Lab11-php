
<?php
/**
 * Ejercicio 2: Insertar datos en la tabla productos
 * Estudiante: René Mariscal
 * Semilla personal: 565
 */

require_once 'db_config.php';

$semilla = 565;
$total = ($semilla % 5) + 3;

$computadora = gethostname();
$fechaHora = date('Y-m-d H:i:s');

try {
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=tienda_lab11;charset=' . DB_CHARSET;

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Iniciar transacción para mantener los datos consistentes
    $pdo->beginTransaction();

    // Eliminar registros anteriores de la misma semilla para evitar duplicados
    $limpiar = $pdo->prepare(
        "DELETE FROM productos WHERE semilla = :semilla"
    );

    $limpiar->execute([
        ':semilla' => $semilla
    ]);

    // Preparar la consulta SQL
    $sql = "INSERT INTO productos (nombre, precio, stock, semilla)
            VALUES (:nombre, :precio, :stock, :semilla)";

    $stmt = $pdo->prepare($sql);

    $insertados = [];

    // Insertar los productos calculados con la semilla
    for ($i = 0; $i < $total; $i++) {

        $nombre = 'Producto_' . ($semilla * ($i + 1));

        $precio = round($semilla * 1.5 + $i * 3.75, 2);

        $stock = ($semilla + $i * 7) % 50 + 1;

        $stmt->execute([
            ':nombre' => $nombre,
            ':precio' => $precio,
            ':stock' => $stock,
            ':semilla' => $semilla
        ]);

        $insertados[] = [
            'id' => $pdo->lastInsertId(),
            'nombre' => $nombre,
            'precio' => $precio,
            'stock' => $stock
        ];
    }

    // Confirmar las inserciones
    $pdo->commit();

    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
    echo '<title>Ejercicio 2</title></head><body>';

    echo '<h2>Productos insertados</h2>';

    echo '<table border="1" cellpadding="6">';
    echo '<tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Stock</th></tr>';

    foreach ($insertados as $producto) {

        echo '<tr>';

        echo '<td>' . htmlspecialchars((string)$producto['id']) . '</td>';

        echo '<td>' . htmlspecialchars($producto['nombre']) . '</td>';

        echo '<td>' . number_format($producto['precio'], 2, '.', '') . '</td>';

        echo '<td>' . $producto['stock'] . '</td>';

        echo '</tr>';
    }

    echo '</table>';

    echo '<p>Total insertado: ' . count($insertados) . ' registros</p>';

    echo '<p>Semilla usada: ' . $semilla . '</p>';
    echo '<p>Estudiante: René Mariscal</p>';

    echo '<p>Ejecutado en: ' . htmlspecialchars($computadora) . ' - ' . $fechaHora . '</p>';

    echo '</body></html>';

} catch (PDOException $e) {

    // Deshacer cambios si ocurre un error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo 'Error al insertar: ' . htmlspecialchars($e->getMessage());
}
