
<?php
/**
 * Ejercicio 4: Actualizar precios
 * Estudiante: René Mariscal
 * Semilla personal: 565
 */

require_once 'db_config.php';

$semilla = 565;
$porcentaje = ($semilla % 20) + 5;

$computadora = gethostname();
$fechaHora = date('Y-m-d H:i:s');

try {
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=tienda_lab11;charset=' . DB_CHARSET;

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Iniciar transacción para actualizar los registros
    $pdo->beginTransaction();

    // Consultar precios anteriores
    $consulta = $pdo->prepare(
        "SELECT id, nombre, precio
         FROM productos
         WHERE semilla = :semilla
         ORDER BY id ASC"
    );

    $consulta->execute([':semilla' => $semilla]);

    $productos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    // Preparar actualización
    $actualizar = $pdo->prepare(
        "UPDATE productos
         SET precio = :precio
         WHERE id = :id AND semilla = :semilla"
    );

    $comparacion = [];
    $totalActualizados = 0;

    foreach ($productos as $producto) {

        $precioAnterior = (float)$producto['precio'];

        $precioNuevo = round(
            $precioAnterior * (1 + $porcentaje / 100),
            2
        );

        $actualizar->execute([
            ':precio' => $precioNuevo,
            ':id' => $producto['id'],
            ':semilla' => $semilla
        ]);

        $totalActualizados += $actualizar->rowCount();

        $comparacion[] = [
            'nombre' => $producto['nombre'],
            'anterior' => $precioAnterior,
            'nuevo' => $precioNuevo
        ];
    }

    // Confirmar las actualizaciones
    $pdo->commit();

    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
    echo '<title>Ejercicio 4</title></head><body>';

    echo '<h2>Actualización de precios</h2>';

    echo '<p>Incremento aplicado: ' . $porcentaje . '%</p>';

    echo '<table border="1" cellpadding="6">';

    echo '<tr>
            <th>Nombre</th>
            <th>Precio anterior</th>
            <th>Precio nuevo</th>
          </tr>';

    foreach ($comparacion as $fila) {

        echo '<tr>';

        echo '<td>' . htmlspecialchars($fila['nombre']) . '</td>';

        echo '<td>B/. ' . number_format($fila['anterior'], 2, '.', '') . '</td>';

        echo '<td>B/. ' . number_format($fila['nuevo'], 2, '.', '') . '</td>';

        echo '</tr>';
    }

    echo '</table>';

    echo '<p>Total filas actualizadas: ' . $totalActualizados . '</p>';

    echo '<p>Semilla usada: ' . $semilla . '</p>';
    echo '<p>Estudiante: René Mariscal</p>';

    echo '<p>Ejecutado en: ' . htmlspecialchars($computadora) . ' - ' . $fechaHora . '</p>';

    echo '</body></html>';

} catch (PDOException $e) {

    // Deshacer cambios si ocurre un error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo 'Error al actualizar: ' . htmlspecialchars($e->getMessage());
}

