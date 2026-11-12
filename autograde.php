<?php
/**
 * autograde.php — Sistema de calificación automática
 * Lab 11 · Desarrollo Web · Acceso a BD en PHP
 *
 * USO LOCAL: php autograde.php [semilla]
 * USO CI:    Ejecutado por .github/workflows/autograde.yml
 *
 * IMPORTANTE: No modificar este archivo. El workflow lo compara con
 * la versión oficial en SarmientoEdu/Lab11-php:main.
 */

declare(strict_types=1);

// ══════════════════════════════════════════════════════════════════════════════
// CONFIGURACIÓN
// ══════════════════════════════════════════════════════════════════════════════

define('AUTOGRADE_VERSION', '1.0.0');
define('NOTA_MINIMA_PASS',   60);
define('TOLERANCIA_PRECIO',  0.01);
define('MIN_COMMITS',         2);
define('MIN_TIEMPO_TOTAL_MIN', 25);    // minutos entre 1er y último commit
define('MIN_TIEMPO_EJ_SEG',   180);    // 3 min mínimo entre commits de ejercicios
define('CAPTURAS_REQUERIDAS',  5);
define('MIN_RESOLUCION_W',    800);
define('MIN_RESOLUCION_H',    600);

// Semillas de prueba que cubren todas las ramas de la lógica
// (semilla % 5) + 3 → 3..7 productos | (semilla % 20)+5 → 5..24% | (semilla %10)+1 → 1..10
const SEMILLAS_PRUEBA = [
    100, 200, 300, 400, 500,   // stock/producto variados
    101, 251, 399, 450, 503,   // cubren residuos mod 5, 10, 20
    823, 999, 7,   55,  777,   // casos extremos y semilla ejemplo
];

// ══════════════════════════════════════════════════════════════════════════════
// HELPERS
// ══════════════════════════════════════════════════════════════════════════════

function log_msg(string $msg, string $nivel = 'INFO'): void {
    $ts = date('Y-m-d H:i:s');
    $linea = "[$ts][$nivel] $msg";
    echo $linea . PHP_EOL;
    // Para GitHub Actions: añadir al summary
    if (getenv('GITHUB_STEP_SUMMARY')) {
        file_put_contents(getenv('GITHUB_STEP_SUMMARY'), $linea . "\n", FILE_APPEND);
    }
}

function comparar_precio(float $a, float $b): bool {
    return abs($a - $b) <= TOLERANCIA_PRECIO;
}

/** Reinicia la base de datos para evitar residuos entre pruebas multi-semilla y calificación */
function limpiar_base_datos(): void {
    if (file_exists('db_config.php')) {
        require_once 'db_config.php';
    }
    $host = defined('DB_HOST') ? DB_HOST : (getenv('DB_HOST') ?: '127.0.0.1');
    $port = defined('DB_PORT') ? DB_PORT : (getenv('DB_PORT') ?: '3306');
    $user = defined('DB_USER') ? DB_USER : (getenv('DB_USER') ?: 'root');
    $pass = defined('DB_PASS') ? DB_PASS : (getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

    try {
        $pdo = new PDO(
            sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $host, $port),
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $pdo->exec('DROP DATABASE IF EXISTS tienda_lab11');
    } catch (PDOException $e) {
        log_msg('⚠️  No se pudo limpiar la BD antes de evaluar: ' . $e->getMessage(), 'WARN');
    }
}

/** Calcula los datos esperados para una semilla dada */
function datos_esperados(int $semilla): array {
    $n = ($semilla % 5) + 3;
    $pct = ($semilla % 20) + 5;
    $umbral = ($semilla % 10) + 1;
    $productos = [];
    for ($i = 0; $i < $n; $i++) {
        $nombre = 'Producto_' . ($semilla * ($i + 1));
        $precio = round($semilla * 1.5 + $i * 3.75, 2);
        $stock  = ($semilla + $i * 7) % 50 + 1;
        $productos[] = compact('nombre', 'precio', 'stock');
    }
    $productosOrdenados = $productos;
    usort($productosOrdenados, fn($a, $b) => $a['precio'] <=> $b['precio']);
    $precios = array_column($productos, 'precio');
    $promedio = count($precios) > 0 ? array_sum($precios) / count($precios) : 0;
    $stockTotal = array_sum(array_column($productos, 'stock'));
    // Tras update
    $productosActualizados = array_map(function($p) use ($pct) {
        $p['precio_nuevo'] = round($p['precio'] * (1 + $pct / 100), 2);
        return $p;
    }, $productos);
    // Para delete (con precios ya actualizados)
    $aEliminar = array_filter($productos, fn($p) => $p['stock'] < $umbral);
    return [
        'n'                      => $n,
        'pct'                    => $pct,
        'umbral'                 => $umbral,
        'productos'              => $productos,
        'productos_ordenados'    => $productosOrdenados,
        'promedio'               => $promedio,
        'stock_total'            => $stockTotal,
        'productos_actualizados' => $productosActualizados,
        'a_eliminar_count'       => count($aEliminar),
        'restantes_count'        => count($productos) - count($aEliminar),
    ];
}

/**
 * Captura la salida de un archivo PHP para una semilla de PRUEBA dada.
 */
function capturar_salida(string $archivo, int $semilla): string {
    if (!file_exists($archivo)) {
        return '';
    }
    $codigoOriginal = file_get_contents($archivo);
    $codigoPrueba = preg_replace(
        '/\$semilla\s*=\s*\d+\s*;/',
        '$semilla = ' . $semilla . ';',
        $codigoOriginal,
        1
    );
    $rutaTemporal = dirname($archivo) . '/' . uniqid('.autograde_tmp_', true) . '_' . basename($archivo);
    file_put_contents($rutaTemporal, $codigoPrueba);

    $cmd = 'php ' . escapeshellarg($rutaTemporal) . ' 2>&1';
    $output = shell_exec($cmd);

    @unlink($rutaTemporal);
    return $output ?? '';
}

/** Extrae texto plano de salida HTML */
function texto_de_html(string $html): string {
    $sin_tags = strip_tags($html);
    return preg_replace('/\s+/', ' ', trim($sin_tags));
}

// ══════════════════════════════════════════════════════════════════════════════
// VERIFICADORES DE EJERCICIOS
// ══════════════════════════════════════════════════════════════════════════════

function verificar_ej1(int $semilla, array $datos): array {
    $resultado = ['pts' => 0, 'max' => 20, 'detalle' => [], 'hardcode' => false];
    $salida = capturar_salida('ej1_crear_bd.php', $semilla);
    $texto  = texto_de_html($salida);

    // Verificar base de datos y tabla mencionadas
    if (stripos($texto, 'tienda_lab11') !== false) {
        $resultado['pts'] += 4;
        $resultado['detalle'][] = '✅ Nombre BD correcto';
    } else {
        $resultado['detalle'][] = '❌ No se menciona tienda_lab11 en la salida';
    }
    if (stripos($texto, 'productos') !== false) {
        $resultado['pts'] += 3;
        $resultado['detalle'][] = '✅ Nombre tabla correcto';
    } else {
        $resultado['detalle'][] = '❌ No se menciona la tabla productos';
    }
    if (stripos($texto, 'correctamente') !== false || stripos($texto, 'creada') !== false) {
        $resultado['pts'] += 3;
        $resultado['detalle'][] = '✅ Mensaje de éxito presente';
    } else {
        $resultado['detalle'][] = '❌ Falta mensaje de estado';
    }
    if (stripos($texto, (string)$semilla) !== false) {
        $resultado['pts'] += 5;
        $resultado['detalle'][] = '✅ Semilla visible en salida';
    } else {
        $resultado['detalle'][] = '❌ Semilla no visible en salida';
        $resultado['hardcode'] = true;
    }
    if (stripos($texto, 'ejecutado en') !== false || stripos($texto, gethostname()) !== false) {
        $resultado['pts'] += 5;
        $resultado['detalle'][] = '✅ Nombre de computadora y timestamp presentes';
    } else {
        $resultado['detalle'][] = '❌ Falta nombre de computadora o timestamp';
    }
    return $resultado;
}

function verificar_ej2(int $semilla, array $datos): array {
    $resultado = ['pts' => 0, 'max' => 20, 'detalle' => [], 'hardcode' => false];
    $salida = capturar_salida('ej2_insertar.php', $semilla);
    $texto  = texto_de_html($salida);

    $n = $datos['n'];
    // Verificar número de registros
    if (preg_match('/total\s+insertado[:\s]+(\d+)\s+registro/i', $texto, $m)) {
        if ((int)$m[1] === $n) {
            $resultado['pts'] += 8;
            $resultado['detalle'][] = "✅ Total insertado correcto: $n";
        } else {
            $resultado['detalle'][] = "❌ Total insertado: {$m[1]}, esperado: $n";
            $resultado['hardcode'] = true;
        }
    } else {
        $resultado['detalle'][] = "❌ No se encontró 'Total insertado' en la salida";
    }
    // Verificar primer producto
    $primerNombre = 'Producto_' . ($semilla * 1);
    if (stripos($texto, $primerNombre) !== false) {
        $resultado['pts'] += 4;
        $resultado['detalle'][] = "✅ Primer producto correcto: $primerNombre";
    } else {
        $resultado['detalle'][] = "❌ No se encontró $primerNombre";
        $resultado['hardcode'] = true;
    }
    // Verificar precio del primer producto
    $precio1 = number_format($datos['productos'][0]['precio'], 2);
    if (strpos($texto, $precio1) !== false) {
        $resultado['pts'] += 4;
        $resultado['detalle'][] = "✅ Precio inicial correcto: $precio1";
    } else {
        $resultado['detalle'][] = "❌ No se encontró precio $precio1";
        $resultado['hardcode'] = true;
    }
    // Verificar timestamp
    if (preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $texto)) {
        $resultado['pts'] += 4;
        $resultado['detalle'][] = '✅ Timestamp presente';
    } else {
        $resultado['detalle'][] = '❌ Falta timestamp';
    }
    return $resultado;
}

function verificar_ej3(int $semilla, array $datos): array {
    $resultado = ['pts' => 0, 'max' => 20, 'detalle' => [], 'hardcode' => false];
    $salida = capturar_salida('ej3_listar.php', $semilla);
    $texto  = texto_de_html($salida);

    // Verificar conteo de productos
    if (stripos($texto, 'total de productos') !== false &&
        preg_match('/total de productos[:\s]+(\d+)/i', $texto, $m) &&
        (int)$m[1] === $datos['n']) {
        $resultado['pts'] += 5;
        $resultado['detalle'][] = "✅ Total productos correcto: {$datos['n']}";
    } else {
        $resultado['detalle'][] = "❌ Total productos incorrecto o no encontrado";
        $resultado['hardcode'] = true;
    }
    // Verificar precio promedio (tolerancia ±0.01)
    $promedioEsperado = $datos['promedio'];
    if (preg_match('/precio\s+promedio[:\s]+b\/\.\s*([\d.,]+)/i', $texto, $m)) {
        $precioEncontrado = (float) str_replace(',', '', $m[1]);
        if (comparar_precio($precioEncontrado, $promedioEsperado)) {
            $resultado['pts'] += 7;
            $resultado['detalle'][] = "✅ Precio promedio correcto: " . number_format($promedioEsperado, 2);
        } else {
            $resultado['detalle'][] = "❌ Precio promedio incorrecto. Encontrado: $precioEncontrado, Esperado: " . number_format($promedioEsperado, 2);
            $resultado['hardcode'] = true;
        }
    } else {
        $resultado['detalle'][] = "❌ No se encontró precio promedio en formato B/. XXXX.XX";
    }
    // Verificar stock total
    if (preg_match('/stock\s+total[:\s]+(\d+)/i', $texto, $m) &&
        (int)$m[1] === $datos['stock_total']) {
        $resultado['pts'] += 4;
        $resultado['detalle'][] = "✅ Stock total correcto: {$datos['stock_total']}";
    } else {
        $resultado['detalle'][] = "❌ Stock total incorrecto";
        $resultado['hardcode'] = true;
    }
    // Verificar timestamp
    if (preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $texto)) {
        $resultado['pts'] += 4;
        $resultado['detalle'][] = '✅ Timestamp presente';
    } else {
        $resultado['detalle'][] = '❌ Falta timestamp';
    }
    return $resultado;
}

function verificar_ej4(int $semilla, array $datos): array {
    $resultado = ['pts' => 0, 'max' => 20, 'detalle' => [], 'hardcode' => false];
    $salida = capturar_salida('ej4_actualizar.php', $semilla);
    $texto  = texto_de_html($salida);

    // Verificar porcentaje
    if (preg_match('/incremento\s+aplicado[:\s]+(\d+)%/i', $texto, $m) &&
        (int)$m[1] === $datos['pct']) {
        $resultado['pts'] += 6;
        $resultado['detalle'][] = "✅ Porcentaje correcto: {$datos['pct']}%";
    } else {
        $resultado['detalle'][] = "❌ Porcentaje no encontrado o incorrecto. Esperado: {$datos['pct']}%";
        $resultado['hardcode'] = true;
    }
    // Verificar precio nuevo del primer producto
    $precioNuevo = round($datos['productos'][0]['precio'] * (1 + $datos['pct'] / 100), 2);
    if (strpos($texto, number_format($precioNuevo, 2)) !== false) {
        $resultado['pts'] += 6;
        $resultado['detalle'][] = "✅ Precio nuevo correcto para primer producto";
    } else {
        $resultado['detalle'][] = "❌ Precio nuevo no encontrado. Esperado: " . number_format($precioNuevo, 2);
        $resultado['hardcode'] = true;
    }
    // Verificar filas actualizadas
    if (preg_match('/total\s+filas\s+actualizadas[:\s]+(\d+)/i', $texto, $m) &&
        (int)$m[1] === $datos['n']) {
        $resultado['pts'] += 4;
        $resultado['detalle'][] = "✅ Filas actualizadas correcto: {$datos['n']}";
    } else {
        $resultado['detalle'][] = "❌ Filas actualizadas no encontrado o incorrecto";
    }
    // Verificar timestamp
    if (preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $texto)) {
        $resultado['pts'] += 4;
        $resultado['detalle'][] = '✅ Timestamp presente';
    } else {
        $resultado['detalle'][] = '❌ Falta timestamp';
    }
    return $resultado;
}

function verificar_ej5(int $semilla, array $datos): array {
    $resultado = ['pts' => 0, 'max' => 20, 'detalle' => [], 'hardcode' => false];
    $salida = capturar_salida('ej5_eliminar.php', $semilla);
    $texto  = texto_de_html($salida);

    // Verificar umbral
    if (preg_match('/umbral\s+de\s+stock[:\s]+(\d+)/i', $texto, $m) &&
        (int)$m[1] === $datos['umbral']) {
        $resultado['pts'] += 5;
        $resultado['detalle'][] = "✅ Umbral correcto: {$datos['umbral']}";
    } else {
        $resultado['detalle'][] = "❌ Umbral incorrecto o no encontrado. Esperado: {$datos['umbral']}";
        $resultado['hardcode'] = true;
    }
    // Verificar eliminados
    $elimCount = $datos['a_eliminar_count'];
    if ($elimCount === 0) {
        if (stripos($texto, 'no hay productos') !== false) {
            $resultado['pts'] += 7;
            $resultado['detalle'][] = "✅ Mensaje correcto cuando no hay que eliminar";
        } else {
            $resultado['detalle'][] = "❌ Falta mensaje 'No hay productos con stock menor a N'";
        }
    } else {
        if (preg_match('/total\s+eliminados[:\s]+(\d+)/i', $texto, $m) &&
            (int)$m[1] === $elimCount) {
            $resultado['pts'] += 7;
            $resultado['detalle'][] = "✅ Total eliminados correcto: $elimCount";
        } else {
            $resultado['detalle'][] = "❌ Total eliminados incorrecto. Esperado: $elimCount";
            $resultado['hardcode'] = true;
        }
    }
    // Verificar restantes
    if (preg_match('/productos\s+restantes[:\s]+(\d+)/i', $texto, $m) &&
        (int)$m[1] === $datos['restantes_count']) {
        $resultado['pts'] += 4;
        $resultado['detalle'][] = "✅ Restantes correcto: {$datos['restantes_count']}";
    } else {
        $resultado['detalle'][] = "❌ Restantes incorrecto. Esperado: {$datos['restantes_count']}";
    }
    // Verificar timestamp
    if (preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $texto)) {
        $resultado['pts'] += 4;
        $resultado['detalle'][] = '✅ Timestamp presente';
    } else {
        $resultado['detalle'][] = '❌ Falta timestamp';
    }
    return $resultado;
}

// ══════════════════════════════════════════════════════════════════════════════
// ANÁLISIS DE GIT Y COMMITS
// ══════════════════════════════════════════════════════════════════════════════

function analizar_commits(): array {
    $resultado = [
        'banderas' => [],
        'commits_data' => [],
        'tiempo_total_min' => 0,
        'autores' => [],
        'issue_datos' => [],
    ];

    $logRaw = shell_exec('git log --format="%H|%ae|%an|%at|%s" --no-merges 2>/dev/null') ?? '';
    if (empty(trim($logRaw))) {
        $resultado['banderas'][] = 'commits_insuficientes';
        return $resultado;
    }
    $lineas = array_filter(explode("\n", trim($logRaw)));
    $commits = [];
    foreach ($lineas as $l) {
        $partes = explode('|', $l, 5);
        if (count($partes) < 5) continue;
        $commits[] = [
            'hash'      => substr($partes[0], 0, 8),
            'email'     => $partes[1],
            'autor'     => $partes[2],
            'timestamp' => (int)$partes[3],
            'mensaje'   => $partes[4],
        ];
    }
    $resultado['commits_data'] = $commits;

    // Verificar cantidad
    if (count($commits) < MIN_COMMITS) {
        $resultado['banderas'][] = 'commits_insuficientes';
    }

    // Verificar tiempo total
    if (count($commits) >= 2) {
        $timestamps = array_column($commits, 'timestamp');
        sort($timestamps);
        $tiempoTotalSeg = max($timestamps) - min($timestamps);
        $resultado['tiempo_total_min'] = round($tiempoTotalSeg / 60, 1);
        if ($tiempoTotalSeg < MIN_TIEMPO_TOTAL_MIN * 60) {
            $resultado['banderas'][] = 'commits_insuficientes';
        }
    }

    // Verificar autor único
    $autores = array_unique(array_column($commits, 'email'));
    $resultado['autores'] = $autores;
    if (count($autores) > 1) {
        $resultado['banderas'][] = 'autor_multiple';
    }
    if (in_array('', $autores) || in_array('user@example.com', $autores) || in_array('noreply@github.com', $autores)) {
        $resultado['banderas'][] = 'control_ausente';
    }

    // Verificar prefijos por ejercicio
    $prefijos = ['ej1', 'ej2', 'ej3', 'ej4', 'ej5'];
    $commitsPorEj = [];
    foreach ($commits as $c) {
        foreach ($prefijos as $pref) {
            if (stripos($c['mensaje'], $pref . ':') === 0) {
                $commitsPorEj[$pref][] = $c;
            }
        }
    }

    // Verificar tiempo mínimo entre commits de ejercicios consecutivos
    $ordenCronologico = $commits;
    usort($ordenCronologico, fn($a, $b) => $a['timestamp'] - $b['timestamp']);
    for ($i = 1; $i < count($ordenCronologico); $i++) {
        $diff = $ordenCronologico[$i]['timestamp'] - $ordenCronologico[$i-1]['timestamp'];
        if ($diff < MIN_TIEMPO_EJ_SEG && $diff > 0) {
            $msg = $ordenCronologico[$i]['mensaje'];
            $ejercicio = 'desconocido';
            foreach ($prefijos as $pref) {
                if (stripos($msg, $pref . ':') === 0) {
                    $ejercicio = strtoupper($pref);
                    break;
                }
            }
            $resultado['issue_datos'][] = [
                'hash_anterior'  => $ordenCronologico[$i-1]['hash'],
                'hash_actual'    => $ordenCronologico[$i]['hash'],
                'msg_anterior'   => $ordenCronologico[$i-1]['mensaje'],
                'msg_actual'     => $msg,
                'diff_segundos'  => $diff,
                'ejercicio'      => $ejercicio,
            ];
        }
    }

    return $resultado;
}

function verificar_capturas(): array {
    $resultado = ['pts_ok' => true, 'detalle' => [], 'banderas' => []];
    $capturasOk = 0;
    for ($i = 1; $i <= CAPTURAS_REQUERIDAS; $i++) {
        $posibles = ["capturas/ej$i.png", "capturas/ej$i.jpg", "capturas/ej$i.jpeg"];
        $encontrada = false;
        foreach ($posibles as $ruta) {
            if (file_exists($ruta)) {
                $size = getimagesize($ruta);
                if ($size && $size[0] >= MIN_RESOLUCION_W && $size[1] >= MIN_RESOLUCION_H) {
                    $resultado['detalle'][] = "✅ Captura ej$i encontrada ({$size[0]}x{$size[1]})";
                    $capturasOk++;
                    $encontrada = true;
                } else {
                    $resultado['detalle'][] = "⚠️  Captura ej$i con resolución baja o no legible";
                    $encontrada = true;
                }
                break;
            }
        }
        if (!$encontrada) {
            $resultado['detalle'][] = "❌ Captura ej$i no encontrada en capturas/ej$i.png";
        }
    }
    if ($capturasOk < CAPTURAS_REQUERIDAS) {
        $resultado['pts_ok'] = false;
        $resultado['banderas'][] = 'capturas_incompletas';
    }
    return $resultado;
}

// ══════════════════════════════════════════════════════════════════════════════
// DETECCIÓN ANTIPLAGIO MULTI-SEMILLA
// ══════════════════════════════════════════════════════════════════════════════

function detectar_hardcode(): array {
    $archivos = ['ej1_crear_bd.php','ej2_insertar.php','ej3_listar.php','ej4_actualizar.php','ej5_eliminar.php'];
    $salidas = [];
    $semilasPrueba = SEMILLAS_PRUEBA;
    foreach ($archivos as $arch) {
        $resultados_por_semilla = [];
        foreach ($semilasPrueba as $s) {
            $out = capturar_salida($arch, $s);
            $resultados_por_semilla[$s] = md5(texto_de_html($out));
        }
        // Si todos los hashes son iguales → hardcode
        if (count(array_unique($resultados_por_semilla)) === 1 && !empty(trim(array_values($resultados_por_semilla)[0]))) {
            $salidas[$arch] = true; // hardcode detectado
        } else {
            $salidas[$arch] = false;
        }
    }
    return $salidas;
}

function detectar_estructuras_no_vistas(string $archivo): array {
    if (!file_exists($archivo)) return [];
    $codigo = file_get_contents($archivo);
    // Ignorar comentarios para evitar falsos positivos en docblocks
    $codigoSinComentarios = preg_replace(['!/\*.*?\*/!s', '!//.*$!m', '!#.*$!m'], '', $codigo);
    $banderas = [];
    $prohibidos = [
        'mysqli_'        => 'uso de mysqli_ (prohibido, solo PDO)',
        'laravel'        => 'referencia a framework Laravel',
        'symfony'        => 'referencia a Symfony',
        'eval('          => 'uso de eval() prohibido',
        'shell_exec('    => 'uso de shell_exec() prohibido (excepto autograde)',
        'Session::'      => 'uso de sesiones (no visto aún)',
        '$_SESSION'      => 'uso de $_SESSION (no visto aún)',
    ];
    foreach ($prohibidos as $patron => $descripcion) {
        if (stripos($codigoSinComentarios, $patron) !== false) {
            $banderas[] = "$archivo: $descripcion";
        }
    }
    // Bloquear exec() de sistema operativo, pero permitir $pdo->exec()
    if (preg_match('/(?<!->)\bexec\s*\(/i', $codigoSinComentarios)) {
        $banderas[] = "$archivo: uso de exec() prohibido";
    }
    return $banderas;
}

// ══════════════════════════════════════════════════════════════════════════════
// CREACIÓN DE ISSUES EN GITHUB
// ══════════════════════════════════════════════════════════════════════════════

function crear_issue_tiempo(array $datos): void {
    if (!getenv('GITHUB_REPOSITORY') || !getenv('GITHUB_TOKEN')) return;
    $repo = getenv('GITHUB_REPOSITORY');
    foreach ($datos as $item) {
        $titulo = escapeshellarg("[Autograde] Tiempo sospechoso en {$item['ejercicio']} (commit {$item['hash_actual']})");
        $cuerpo = escapeshellarg(
            "## Alerta de tiempo entre commits\n\n" .
            "El tiempo entre dos commits fue inusualmente corto.\n\n" .
            "| Campo | Valor |\n|---|---|\n" .
            "| Commit anterior | `{$item['hash_anterior']}` — {$item['msg_anterior']} |\n" .
            "| Commit actual   | `{$item['hash_actual']}` — {$item['msg_actual']} |\n" .
            "| Diferencia      | {$item['diff_segundos']} segundos |\n" .
            "| Ejercicio       | {$item['ejercicio']} |\n\n" .
            "> Este es un indicio automático. El profesor evaluará el contexto."
        );
        shell_exec("gh issue create --repo $repo --title $titulo --body $cuerpo 2>/dev/null");
    }
}

// ══════════════════════════════════════════════════════════════════════════════
// VERIFICACIÓN DE INTEGRIDAD DEL AUTOGRADE
// ══════════════════════════════════════════════════════════════════════════════

function verificar_integridad_autograde(): array {
    $resultado = ['modificado' => false, 'detalle' => ''];
    $urlOficial = 'https://raw.githubusercontent.com/SarmientoEdu/Lab11-php/main/autograde.php';
    $urlWorkflow = 'https://raw.githubusercontent.com/SarmientoEdu/Lab11-php/main/.github/workflows/autograde.yml';

    $ctx = stream_context_create(['http' => ['timeout' => 10]]);
    $oficial = @file_get_contents($urlOficial, false, $ctx);
    $local   = file_get_contents(__FILE__);

    if ($oficial === false) {
        $resultado['detalle'] = 'No se pudo descargar la versión oficial; se usa local como referencia';
    } elseif (md5($oficial) !== md5($local)) {
        $resultado['modificado'] = true;
        $resultado['detalle'] = 'autograde.php fue modificado respecto a la versión oficial';
    }

    $oficialWf = @file_get_contents($urlWorkflow, false, $ctx);
    $localWf   = file_exists('.github/workflows/autograde.yml')
                 ? file_get_contents('.github/workflows/autograde.yml')
                 : '';
    if ($oficialWf !== false && md5($oficialWf) !== md5($localWf)) {
        $resultado['modificado'] = true;
        $resultado['detalle'] .= ' | autograde.yml fue modificado';
    }

    return $resultado;
}

// ══════════════════════════════════════════════════════════════════════════════
// EJECUCIÓN PRINCIPAL
// ══════════════════════════════════════════════════════════════════════════════

function main(): void {
    log_msg('═══════ Autograde Lab11 v' . AUTOGRADE_VERSION . ' ═══════');
    log_msg('Computadora: ' . gethostname());
    log_msg('Fecha/Hora:  ' . date('Y-m-d H:i:s'));

    $semillaEstudiante = 0;
    foreach (['ej1_crear_bd.php', 'ej2_insertar.php', 'ej3_listar.php', 'ej4_actualizar.php', 'ej5_eliminar.php'] as $arch) {
        if (file_exists($arch)) {
            $codigo = file_get_contents($arch);
            if (preg_match('/\$semilla\s*=\s*(\d+)\s*;/', $codigo, $m) && (int)$m[1] > 0) {
                $semillaEstudiante = (int)$m[1];
                break;
            }
        }
    }
    log_msg("Semilla detectada del código: $semillaEstudiante");

    // ── Integridad del autograde ──────────────────────────────────────────────
    $integridad = verificar_integridad_autograde();
    $banderas = [];
    if ($integridad['modificado']) {
        $banderas[] = 'autograde_modificado';
        log_msg('⚠️  ' . $integridad['detalle'], 'WARN');
    } else {
        log_msg('✅ Integridad del autograde: OK');
    }

    // ── Análisis de commits ───────────────────────────────────────────────────
    $gitInfo = analizar_commits();
    $banderas = array_merge($banderas, $gitInfo['banderas']);
    if (!empty($gitInfo['issue_datos'])) {
        log_msg('⚠️  Tiempo sospechoso detectado entre commits — creando issue', 'WARN');
        crear_issue_tiempo($gitInfo['issue_datos']);
    }

    // ── Capturas ──────────────────────────────────────────────────────────────
    $capturaInfo = verificar_capturas();
    $banderas = array_merge($banderas, $capturaInfo['banderas']);
    foreach ($capturaInfo['detalle'] as $d) log_msg($d);

    // ── Estructuras no vistas ─────────────────────────────────────────────────
    $archivos = ['ej1_crear_bd.php','ej2_insertar.php','ej3_listar.php','ej4_actualizar.php','ej5_eliminar.php'];
    $estructurasNoVistas = [];
    foreach ($archivos as $a) {
        $ev = detectar_estructuras_no_vistas($a);
        $estructurasNoVistas = array_merge($estructurasNoVistas, $ev);
    }
    if (!empty($estructurasNoVistas)) {
        $banderas[] = 'estructura_no_vista';
        foreach ($estructurasNoVistas as $ev) log_msg("⚠️  $ev", 'WARN');
    }

    // ── Hardcode multi-semilla ────────────────────────────────────────────────
    log_msg('Verificando hardcode con 15 semillas...');
    $hardcodeDetectado = detectar_hardcode();
    $hayHardcode = in_array(true, $hardcodeDetectado, true);
    if ($hayHardcode) {
        $banderas[] = 'hardcode';
        foreach ($hardcodeDetectado as $arch => $hc) {
            if ($hc) log_msg("⚠️  Hardcode detectado en $arch", 'WARN');
        }
    }

    // ── Reiniciar BD antes de evaluar la entrega real del estudiante ──────────
    limpiar_base_datos();

    // ── Ejercicios (con la semilla real del estudiante) ───────────────────────
    if ($semillaEstudiante === 0) {
        log_msg('⚠️  Semilla = 0 — el estudiante no configuró su cédula', 'WARN');
        $banderas[] = 'hardcode';
    }

    $datos = datos_esperados($semillaEstudiante > 0 ? $semillaEstudiante : 823);

    log_msg('─── Ejercicio 1 ───');
    $r1 = verificar_ej1($semillaEstudiante, $datos);
    foreach ($r1['detalle'] as $d) log_msg($d);
    if ($r1['hardcode']) $banderas[] = 'hardcode';

    log_msg('─── Ejercicio 2 ───');
    $r2 = verificar_ej2($semillaEstudiante, $datos);
    foreach ($r2['detalle'] as $d) log_msg($d);
    if ($r2['hardcode']) $banderas[] = 'hardcode';

    log_msg('─── Ejercicio 3 ───');
    $r3 = verificar_ej3($semillaEstudiante, $datos);
    foreach ($r3['detalle'] as $d) log_msg($d);
    if ($r3['hardcode']) $banderas[] = 'hardcode';

    log_msg('─── Ejercicio 4 ───');
    $r4 = verificar_ej4($semillaEstudiante, $datos);
    foreach ($r4['detalle'] as $d) log_msg($d);
    if ($r4['hardcode']) $banderas[] = 'hardcode';

    log_msg('─── Ejercicio 5 ───');
    $r5 = verificar_ej5($semillaEstudiante, $datos);
    foreach ($r5['detalle'] as $d) log_msg($d);
    if ($r5['hardcode']) $banderas[] = 'hardcode';

    // ── Penalización por capturas ─────────────────────────────────────────────
    $penalizacionCapturas = 0;
    if (!$capturaInfo['pts_ok']) {
        $presentes = count(array_filter($capturaInfo['detalle'], fn($d) => strpos($d, '✅') !== false));
        $faltantes = CAPTURAS_REQUERIDAS - $presentes;
        $penalizacionCapturas = $faltantes * 4;
    }

    // ── Nota total ────────────────────────────────────────────────────────────
    $notaBruta = $r1['pts'] + $r2['pts'] + $r3['pts'] + $r4['pts'] + $r5['pts'];
    $notaTotal = max(0, $notaBruta - $penalizacionCapturas);

    // ── Nivel de observación de plagio ────────────────────────────────────────
    $banderasUnicas = array_unique($banderas);
    $nBanderas = count($banderasUnicas);
    if ($nBanderas === 0) {
        $observacion = 'NINGUNA';
    } elseif ($nBanderas === 1 && in_array($banderasUnicas[0], ['capturas_incompletas','control_ausente'])) {
        $observacion = 'BAJA';
    } elseif ($nBanderas <= 2) {
        $observacion = 'MEDIA';
    } else {
        $observacion = 'ALTA';
    }

    // ── Resultado JSON ────────────────────────────────────────────────────────
    $resultado = [
        'nota_total'            => $notaTotal,
        'nota_bruta'            => $notaBruta,
        'penalizacion_capturas' => $penalizacionCapturas,
        'ejercicios'            => [
            'ej1' => ['pts' => $r1['pts'], 'max' => $r1['max']],
            'ej2' => ['pts' => $r2['pts'], 'max' => $r2['max']],
            'ej3' => ['pts' => $r3['pts'], 'max' => $r3['max']],
            'ej4' => ['pts' => $r4['pts'], 'max' => $r4['max']],
            'ej5' => ['pts' => $r5['pts'], 'max' => $r5['max']],
        ],
        'observacion_plagio'    => $observacion,
        'banderas'              => $banderasUnicas,
        'tiempo_total_min'      => $gitInfo['tiempo_total_min'],
        'autores_detectados'    => $gitInfo['autores'],
        'semilla_estudiante'    => $semillaEstudiante,
        'computadora'           => gethostname(),
        'fecha_ejecucion'       => date('Y-m-d H:i:s'),
        'version_autograde'     => AUTOGRADE_VERSION,
    ];

    file_put_contents('resultado_autograde.json', json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // ── Resumen final ─────────────────────────────────────────────────────────
    log_msg('═══════════════════════════════════════════');
    log_msg(sprintf('NOTA FINAL: %d/100', $notaTotal));
    log_msg(sprintf(
        'Desglose: Ej1=%d Ej2=%d Ej3=%d Ej4=%d Ej5=%d (penalización capturas: -%d)',
        $r1['pts'], $r2['pts'], $r3['pts'], $r4['pts'], $r5['pts'], $penalizacionCapturas
    ));
    log_msg("Observación de plagio: $observacion");
    if (!empty($banderasUnicas)) {
        log_msg('Banderas: ' . implode(', ', $banderasUnicas));
        log_msg('ADVERTENCIA: Las observaciones son indicios, no pruebas de plagio.');
        log_msg('El profesor evaluará cada caso con criterio pedagógico.');
    }
    log_msg('Computadora: ' . gethostname() . ' — ' . date('Y-m-d H:i:s'));
    log_msg('═══════════════════════════════════════════');

    // Escribir GitHub Step Summary
    if (getenv('GITHUB_STEP_SUMMARY')) {
        $summary = "## 📊 Resultado Autograde Lab11\n\n";
        $summary .= "| Campo | Valor |\n|---|---|\n";
        $summary .= "| **Nota total** | **$notaTotal / 100** |\n";
        $summary .= "| Ej1 | {$r1['pts']}/20 |\n";
        $summary .= "| Ej2 | {$r2['pts']}/20 |\n";
        $summary .= "| Ej3 | {$r3['pts']}/20 |\n";
        $summary .= "| Ej4 | {$r4['pts']}/20 |\n";
        $summary .= "| Ej5 | {$r5['pts']}/20 |\n";
        $summary .= "| Observación plagio | $observacion |\n";
        if (!empty($banderasUnicas)) {
            $summary .= "| Banderas | " . implode(', ', $banderasUnicas) . " |\n";
        }
        $summary .= "\n> Ejecutado en: " . gethostname() . " — " . date('Y-m-d H:i:s') . "\n";
        $summary .= "\n> ⚠️ Las observaciones de plagio son indicios, no pruebas definitivas.\n";
        file_put_contents(getenv('GITHUB_STEP_SUMMARY'), $summary, FILE_APPEND);
    }

    // Fallar el job si nota < mínimo
    if ($notaTotal < NOTA_MINIMA_PASS) {
        exit(1);
    }
}

main();
