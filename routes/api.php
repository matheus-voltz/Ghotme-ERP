<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ApiOrdemServicoController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ClientVehicleController;
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

    // Agenda / Calendário
    Route::get('/calendar/events', [App\Http\Controllers\CalendarController::class, 'fetchEvents']);

    // Perfil e Configurações
    Route::post('/update-push-token', function(Request $request) {
        $request->validate(['token' => 'required|string']);
        $request->user()->update(['expo_push_token' => $request->token]);
        return response()->json(['message' => 'Token atualizado!']);
    });

    // Ordem de Serviço API
    Route::get('/ordens-servico', [ApiOrdemServicoController::class, 'index']);
    Route::get('/ordens-servico/{id}', [ApiOrdemServicoController::class, 'show']);

    // Chat Routes
    Route::get('/chat/contacts', [ChatController::class, 'contacts']);
    Route::get('/chat/messages/{userId}', [ChatController::class, 'messages']);
    Route::post('/chat/messages', [ChatController::class, 'send']);

    // OS & Client/Vehicle Routes
    Route::get('/clients', [ClientVehicleController::class, 'getClients']);
    Route::get('/clients/{clientId}/vehicles', [ClientVehicleController::class, 'getVehicles']);
    Route::post('/os', [ClientVehicleController::class, 'store']);

    // Add more API routes here as needed (Vehicles, Clients, etc.)
});
