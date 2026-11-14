# Lab 11 Acceso a base de datos en PHP

Curso: Desarrollo Web, UTP. Profesor: SarmientoEdu.

Estudiante: Alejandro Sáenz. Semilla: **343**.
Repositorio base: https://github.com/SarmientoEdu/Lab11-php
Rama de trabajo: `lab11-saenz-alejandro`.

## Ejecución local

1. Iniciar Apache y MySQL de XAMPP con PHP 8 o superior y la extensión `pdo_mysql`.
2. Colocar esta carpeta dentro de `xampp/htdocs/Lab11-php`.
3. Revisar `db_config.php`: por defecto usa `127.0.0.1:3306`, usuario `root`, contraseña vacía y base `tienda_lab11`. Se pueden proporcionar `DB_HOST`, `DB_PORT`, `DB_USER` y `DB_PASS` como variables de entorno. No guardar credenciales de producción en el repositorio.
4. Abrir los ejercicios en el orden indicado. Las páginas muestran el nombre del estudiante, la semilla, el equipo y la fecha real de ejecución en hora de Panamá.

| Orden | Página | Operación |
| --- | --- | --- |
| 1 | `ej1_crear_bd.php` | Crea `tienda_lab11` y la tabla `productos` si no existen. Conserva los registros existentes. |
| 2 | `ej2_insertar.php` | Inserta un lote calculado mediante la semilla y sentencias preparadas. Cada ejecución agrega un lote nuevo. |
| 3 | `ej3_listar.php` | Consulta únicamente la semilla correspondiente, ordena por precio y calcula en PHP el promedio y el stock total. |
| 4 | `ej4_actualizar.php` | Aplica el porcentaje a los precios actuales. Cada ejecución vuelve a aplicar el incremento. |
| 5 | `ej5_eliminar.php` | Consulta y elimina exclusivamente los productos de esa semilla con stock estrictamente menor al umbral; verifica el conteo restante. |

La clase `Productos` separa las consultas PDO de la salida HTML. Las inserciones, actualizaciones y eliminaciones usan transacciones y revierten sus cambios si ocurre un error de base de datos.

## Resultados de una ejecución inicial con la semilla 343

- Se insertan 6 productos, con precios desde B/. 514.50 hasta B/. 533.25.
- El listado inicial muestra un promedio de B/. 523.88 y un stock total de 119 unidades.
- El incremento es del 8 %. El primer precio pasa de B/. 514.50 a B/. 555.66.
- El umbral es 4: se elimina `Producto_686`, cuyo stock es 1, y quedan 5 productos.

Estos valores se calculan a partir de la semilla y los registros de la base. Las páginas no presentan resultados fijos.

## Evidencias y validación

Las cinco capturas reales están en `capturas/ej1.jpg` a `capturas/ej5.jpg`, a una resolución de 1280 × 800. El enunciado y el evaluador admiten JPG y PNG.

Se probaron los cinco ejercicios con PHP 8.2 y una base local MariaDB 11.4 mediante PDO MySQL: 540 comprobaciones independientes, con 16 semillas, incluyendo 343. Las verificaciones contrastaron la salida HTML con los registros persistidos, el redondeo monetario, la separación entre semillas, el caso sin productos y el límite estricto del stock.

El evaluador oficial se conserva sin cambios. Su ejecución reinicia la base `tienda_lab11`, por lo que debe utilizarse sobre datos de prueba. La ejecución local obtuvo 100/100 en los ejercicios y capturas; sus observaciones de integridad se deben revisar aparte en el reporte real y posteriormente en GitHub Actions.