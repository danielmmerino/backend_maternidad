<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UsuarioController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí defines todas las rutas de tu API.
| Por defecto, las rutas en este archivo estarán bajo el prefijo /api
| y usarán el middleware "api" definido en App\Http\Kernel.php.
|
| Ejemplo:
|  POST  http://localhost:8000/api/usuarios
|
*/

// Endpoint de prueba opcional
Route::get('/ping', function () {
    return response()->json(['message' => 'API funcionando ✅']);
});

// Información de la aplicación (GET)
Route::get('/info', function () {
    return 'Aplicacion desarrollada por Daniel Merino - 2026 registro 12345';
});

// Crear usuario (POST)
Route::post('/usuarios', [UsuarioController::class, 'store']);

// Login de usuario (POST)
Route::post('/login', [AuthController::class, 'login']);
