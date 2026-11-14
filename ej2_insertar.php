<?php
require_once 'db_config.php';

// Recibe la semilla por URL o usa 903 por defecto
$semilla = isset($_GET['semilla']) ? (int)$_GET['semilla'] : 903;

$computadora = gethostname();
$fechaHora = date('Y-m-d H:i:s');

// Cálculo exacto de la rúbrica: entre 3 y 7 productos
$totalProductos = ($semilla % 5) + 3;

try {
    // Conexión PDO a la BD ya creada
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=tienda_lab11;charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Sentencia preparada (obligatorio para evitar SQL injection y pasar el autograde)
    $sql = "INSERT INTO productos (nombre, precio, stock, semilla) 
            VALUES (:nombre, :precio, :stock, :semilla)";
    $stmt = $pdo->prepare($sql);

    $productosInsertados = [];

    for ($i = 0; $i < $totalProductos; $i++) {
        // Fórmulas exactas de la rúbrica
        $nombre = "Producto_" . ($semilla * ($i + 1));
        $precio = round($semilla * 1.5 + $i * 3.75, 2);
        $stock = ($semilla + $i * 7) % 50 + 1;
        
        $stmt->execute([
            ':nombre' => $nombre,
            ':precio' => $precio,
            ':stock' => $stock,
            ':semilla' => $semilla
        ]);
        
        $productosInsertados[] = [
            'id' => $pdo->lastInsertId(),
            'nombre' => $nombre,
            'precio' => $precio,
            'stock' => $stock
        ];
    }
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Ej2 - Insertar Datos</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            table { border-collapse: collapse; width: 50%; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #4CAF50; color: white; }
        </style>
    </head>
    <body>
        <h1>Ejercicio 2 — Insertar Datos</h1>
        <h2>Productos insertados</h2>
        <table border="1">
            <tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Stock</th></tr>
            <?php foreach ($productosInsertados as $prod): ?>
            <tr>
                <td><?= $prod['id'] ?></td>
                <td><?= htmlspecialchars($prod['nombre']) ?></td>
                <td><?= $prod['precio'] ?></td>
                <td><?= $prod['stock'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p>Total insertado: <strong><?= $totalProductos ?> registros</strong></p>
        <p>Ejecutado en: <strong><?= htmlspecialchars($computadora) ?></strong> — <?= htmlspecialchars($fechaHora) ?></p>
    </body>
    </html>
    <?php
} catch (PDOException $e) {
    echo '<p style="color:red">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>