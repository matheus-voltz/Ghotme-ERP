<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ApiOrdemServicoController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ClientVehicleController;
use App\Http\Controllers\Api\ApiBudgetController;
use App\Http\Controllers\Api\ApiInventoryController;
use App\Http\Controllers\Api\ApiChecklistController;
use App\Http\Controllers\Api\ApiTechnicalChecklistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/two-factor', [AuthController::class, 'loginTwoFactor']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/user/profile-photo', [AuthController::class, 'updateProfilePhoto']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Dashboard
    Route::get('/dashboard/stats', [ApiOrdemServicoController::class, 'getDashboardStats']);

    // Clientes & Veículos
    Route::post('/clients-list', [ClientVehicleController::class, 'storeClient']);
    Route::post('/vehicles', [ClientVehicleController::class, 'storeVehicle']);
    Route::get('/clients', [ClientVehicleController::class, 'getClients']);
    Route::get('/clients/{id}/vehicles', [ClientVehicleController::class, 'getClientVehicles']);

    // Ordem de Serviço
    Route::post('/os', [ApiOrdemServicoController::class, 'store']);
    Route::get('/os/{id}', [ApiOrdemServicoController::class, 'show']);
    
    // Checklist
    Route::post('/checklist/visual', [ApiChecklistController::class, 'storeVisual']);
    Route::post('/os/technical-checklist', [ApiTechnicalChecklistController::class, 'store']);
    Route::get('/os/{osId}/technical-checklist', [ApiTechnicalChecklistController::class, 'index']);

    // Chat
    Route::get('/chat/contacts', [ChatController::class, 'getContacts']);
    Route::get('/chat/messages/{userId}', [ChatController::class, 'getMessages']);
    Route::post('/chat/messages', [ChatController::class, 'sendMessage']);

    // Push Token
    Route::post('/user/push-token', [AuthController::class, 'updatePushToken']);
});