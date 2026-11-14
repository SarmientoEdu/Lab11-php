<?php

/** Acceso a los productos mediante sentencias preparadas PDO. */
class Productos
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listar(int $semilla): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nombre, precio, stock FROM productos
             WHERE semilla = :semilla ORDER BY precio ASC, id ASC'
        );
        $stmt->execute(['semilla' => $semilla]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertar(int $semilla): array
    {
        $total = ($semilla % 5) + 3;
        $insertados = [];
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO productos (nombre, precio, stock, semilla)
                 VALUES (:nombre, :precio, :stock, :semilla)'
            );

            for ($i = 0; $i < $total; $i++) {
                $nombre = 'Producto_' . ($semilla * ($i + 1));
                $precio = round($semilla * 1.5 + $i * 3.75, 2);
                $stock = ($semilla + $i * 7) % 50 + 1;
                $stmt->execute([
                    'nombre' => $nombre,
                    'precio' => number_format($precio, 2, '.', ''),
                    'stock' => $stock,
                    'semilla' => $semilla,
                ]);
                $id = (int) $this->pdo->lastInsertId();
                $insertados[] = compact('id', 'nombre', 'precio', 'stock');
            }

            $this->pdo->commit();
            return $insertados;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
