
<?php
/**
 * Ejercicio 3: Seleccionar y mostrar productos
 * Estudiante: René Mariscal
 * Semilla personal: 565
 */

require_once 'db_config.php';

$semilla = 565;
$computadora = gethostname();
$fechaHora = date('Y-m-d H:i:s');

try {
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=tienda_lab11;charset=' . DB_CHARSET;

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Consultar productos de nuestra semilla
    $stmt = $pdo->prepare(
        "SELECT id, nombre, precio, stock, semilla
         FROM productos
         WHERE semilla = :semilla
         ORDER BY precio ASC"
    );

    $stmt->execute([':semilla' => $semilla]);

    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular promedio y stock total en PHP
    $precios = [];
    $stockTotal = 0;

    foreach ($productos as $producto) {
        $precios[] = (float)$producto['precio'];
        $stockTotal += (int)$producto['stock'];
    }

    $promedio = count($precios) > 0
        ? array_sum($precios) / count($precios)
        : 0;

    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
    echo '<title>Ejercicio 3</title>';

    echo '<style>
        body {
            font-family: Arial, sans-serif;
        }

        table {
            border-collapse: collapse;
            width: 80%;
        }

        th, td {
            border: 1px solid #999;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #b7d7f0;
        }

        tr:nth-child(even) {
            background: #eeeeee;
        }
    </style>';

    echo '</head><body>';

    echo '<h2>Listado de productos</h2>';

    echo '<table>';
    echo '<tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Stock</th></tr>';

    foreach ($productos as $producto) {
        echo '<tr>';

        echo '<td>' . htmlspecialchars((string)$producto['id']) . '</td>';

        echo '<td>' . htmlspecialchars($producto['nombre']) . '</td>';

        echo '<td>B/. ' . number_format((float)$producto['precio'], 2, '.', '') . '</td>';

        echo '<td>' . htmlspecialchars((string)$producto['stock']) . '</td>';

        echo '</tr>';
    }

    echo '</table>';

    echo '<p>Total de productos: ' . count($productos) . '</p>';

    echo '<p>Precio promedio: B/. ' . number_format($promedio, 2, '.', '') . '</p>';

    echo '<p>Stock total: ' . $stockTotal . ' unidades</p>';

    echo '<p>Semilla usada: ' . $semilla . '</p>';
    echo '<p>Estudiante: René Mariscal</p>';

    echo '<p>Ejecutado en: ' . htmlspecialchars($computadora) . ' - ' . $fechaHora . '</p>';

    echo '</body></html>';

} catch (PDOException $e) {
    echo 'Error al consultar: ' . htmlspecialchars($e->getMessage());
}
