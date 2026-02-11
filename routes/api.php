<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ApiOrdemServicoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public Routes
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Ordem de Serviço API
    Route::get('/ordens-servico', [ApiOrdemServicoController::class, 'index']);
    Route::get('/ordens-servico/{id}', [ApiOrdemServicoController::class, 'show']);

    // Add more API routes here as needed (Vehicles, Clients, etc.)
});
