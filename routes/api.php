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

// iFood Webhook (Public, handle security in controller)
Route::post('/webhooks/ifood', [\App\Http\Controllers\IFoodController::class, 'handleWebhook']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/niche-config', [App\Http\Controllers\Api\NicheConfigController::class, 'index']);
    Route::post('/user/profile-photo', [AuthController::class, 'updateProfilePhoto']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Dashboard
    Route::get('/dashboard/stats', [ApiOrdemServicoController::class, 'getDashboardStats']);
    Route::get('/watch/dashboard', [ApiOrdemServicoController::class, 'getWatchDashboard']);
    Route::get('/reports/{type}', [\App\Http\Controllers\Api\ApiReportController::class, 'show']);

    // Clientes & Veículos
    Route::post('/clients-list', [ClientVehicleController::class, 'storeClient']);
    Route::post('/vehicles', [ClientVehicleController::class, 'storeVehicle']);
    Route::get('/clients', [ClientVehicleController::class, 'getClients']);
    Route::get('/clients/{id}/vehicles', [ClientVehicleController::class, 'getClientVehicles']);

    // Ordem de Serviço
    Route::get('/os', [ApiOrdemServicoController::class, 'index']);
    Route::post('/os', [ApiOrdemServicoController::class, 'store']);
    Route::get('/os/{id}', [ApiOrdemServicoController::class, 'show']);
    Route::delete('/os/{id}', [ApiOrdemServicoController::class, 'destroy']);
    Route::patch('/os/{id}/status', [ApiOrdemServicoController::class, 'updateStatus']);
    Route::patch('/os/{id}/password', [ApiOrdemServicoController::class, 'updatePassword']);
    Route::post('/os/{id}/pix/generate', [ApiOrdemServicoController::class, 'generatePix']);
    Route::get('/os/{id}/pix/status', [ApiOrdemServicoController::class, 'checkPixStatus']);
    Route::get('/budgets/pending', [App\Http\Controllers\Api\ApiBudgetController::class, 'getPending']);
    Route::post('/budgets/{id}/approve', [App\Http\Controllers\Api\ApiBudgetController::class, 'approve']);
    Route::post('/budgets/{id}/reject', [App\Http\Controllers\Api\ApiBudgetController::class, 'reject']);

    Route::post('/os/items/{itemId}/toggle-timer', [ApiOrdemServicoController::class, 'toggleTimer']);
    Route::post('/os/items/{itemId}/complete', [ApiOrdemServicoController::class, 'completeItem']);

    // Checklist
    Route::get('/checklist/visual/{osId}', [ApiChecklistController::class, 'getVisual']);
    Route::post('/checklist/visual', [ApiChecklistController::class, 'storeVisual']);
    Route::post('/os/technical-checklist', [ApiTechnicalChecklistController::class, 'store']);
    Route::get('/os/{osId}/technical-checklist', [ApiTechnicalChecklistController::class, 'index']);

    // Inventory
    Route::get('/inventory/items-list', [ApiInventoryController::class, 'index']);
    Route::post('/inventory/items', [ApiInventoryController::class, 'store']);
    Route::get('/inventory/menu', [ApiInventoryController::class, 'menu']);

    // Chat
    Route::get('/chat/contacts', [ChatController::class, 'contacts']);
    Route::get('/chat/unread-count', [ChatController::class, 'unreadCount']);
    Route::get('/chat/messages/{userId}', [ChatController::class, 'messages']);
    Route::post('/chat/messages', [ChatController::class, 'send']);

    // Calendar
    Route::get('/calendar/events', [\App\Http\Controllers\CalendarController::class, 'fetchEvents']);
    Route::post('/calendar/events', [\App\Http\Controllers\CalendarController::class, 'store']);
    Route::put('/calendar/events/{id}', [\App\Http\Controllers\CalendarController::class, 'update']);
    Route::delete('/calendar/events/{id}', [\App\Http\Controllers\CalendarController::class, 'destroy']);

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\Api\ApiNotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\ApiNotificationController::class, 'markAsRead']);
    Route::get('/notifications/preferences', [\App\Http\Controllers\Api\ApiNotificationController::class, 'preferences']);
    Route::post('/notifications/preferences', [\App\Http\Controllers\Api\ApiNotificationController::class, 'updatePreferences']);

    // Push Token
    Route::post('/user/push-token', [AuthController::class, 'updatePushToken']);

    // AI Consultant (Mobile)
    Route::get('/ai-consultant/chats', [\App\Http\Controllers\Api\ApiAiConsultantController::class, 'index']);
    Route::post('/ai-consultant/chats', [\App\Http\Controllers\Api\ApiAiConsultantController::class, 'store']);
    Route::post('/ai-consultant/chats/{id}/send', [\App\Http\Controllers\Api\ApiAiConsultantController::class, 'send']);
    Route::get('/ai-consultant/chats/{id}/messages', [\App\Http\Controllers\Api\ApiAiConsultantController::class, 'messages']);

    // PIX Payment
    Route::post('/pix/generate', [\App\Http\Controllers\Api\ApiPixPaymentController::class, 'generate']);
    Route::get('/pix/status/{paymentId}', [\App\Http\Controllers\Api\ApiPixPaymentController::class, 'status']);
});
