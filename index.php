<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/**
 * RUTA DEL PROYECTO LARAVEL (fuera de public_html)
 * ------------------------------------------------------------------------------------
 * EJEMPLO 1 (relativa): si dejaste el proyecto en /home/usuario/maternidad
 * y tu public_html está en /home/usuario/domains/tu-dominio/public_html,
 * normalmente será un nivel arriba + carpeta:
 */
$projectDir = __DIR__ . '';

/**
 * EJEMPLO 2 (absoluta): si quieres ser 100% explícito, descomenta y ajusta:
 */
// $projectDir = '/home/u314482147/maternidad';

/**
 * Validar que exista el directorio del proyecto
 */
if (!is_dir($projectDir)) {
    http_response_code(500);
    echo "Error: No se encuentra el directorio del proyecto en: {$projectDir}";
    exit;
}

// Maintenance (usar la ruta del proyecto, NO la de public_html)
if (file_exists($maintenance = $projectDir . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Autoloader (vendor del proyecto, NO ../vendor por defecto)
$autoload = $projectDir . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    http_response_code(500);
    echo "Error: No se encuentra vendor/autoload.php. "
       . "Asegúrate de haber ejecutado 'composer install' o de haber subido la carpeta vendor al servidor. "
       . "Ruta buscada: {$autoload}";
    exit;
}
require $autoload;

// Bootstrap de Laravel (usar el bootstrap del proyecto)
$appBootstrap = $projectDir . '/bootstrap/app.php';
if (!file_exists($appBootstrap)) {
    http_response_code(500);
    echo "Error: No se encuentra bootstrap/app.php en {$appBootstrap}";
    exit;
}

/** @var Application $app */
$app = require_once $appBootstrap;

// Manejar la solicitud
$app->handleRequest(Request::capture());
