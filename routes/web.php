<?php

use App\Http\Controllers\OrdemServicoController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\HomePage;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\laravel_example\UserManagement;

use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\VehiclesController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\VehicleHistoryController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\InventoryAlertController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServicePackageController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\ReportController;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;


/*
|--------------------------------------------------------------------------
| Email Verification
|--------------------------------------------------------------------------
*/

Route::view('/welcome', view: 'content.font-pages.landing-page')->name('welcome');

Route::get('/email/verify', function () {
    return view('content.authentications.auth-verify-email-basic');
})->middleware('auth')->name('verification.notice');


Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect()->route('dashboard');
})->middleware(['auth', 'signed'])->name('verification.verify');


Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');
// locale
Route::get('/lang/{locale}', [LanguageController::class, 'swap']);
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');

// authentication
Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // Main Page Route
    Route::get('/', [HomePage::class, 'index'])->name('dashboard');
    Route::get('/ordens-servico', [OrdemServicoController::class, 'index'])->name('ordens-servico');
    Route::get('/ordens-servico/create', [OrdemServicoController::class, 'create'])->name('ordens-servico.create');
    Route::get('/ordens-servico/data', [OrdemServicoController::class, 'dataBase'])->name('ordens-servico.data');
    Route::post('/ordens-servico', [OrdemServicoController::class, 'store'])->name('ordens-servico.store');
    Route::post('/ordens-servico/{id}/status', [OrdemServicoController::class, 'updateStatus'])->name('ordens-servico.status');
    Route::get('/api/clients/{id}/vehicles', [OrdemServicoController::class, 'getVehiclesByClient']);

    // Clients
    Route::get('/clients', [ClientsController::class, 'index'])->name('clients');
    Route::get('/clients-list', [ClientsController::class, 'dataBase'])->name('clients-list');

    // Vehicles
    Route::get('/vehicles', [VehiclesController::class, 'index'])->name('vehicles');
    Route::get('/vehicles-list', [VehiclesController::class, 'dataBase'])->name('vehicles-list');
    Route::get('/vehicles-list/{id}/edit', [VehiclesController::class, 'edit'])->name('vehicles-list.edit');
    Route::post('/vehicles-list', [VehiclesController::class, 'store'])->name('vehicles-list.store');
    Route::delete('/vehicles-list/{id}', [VehiclesController::class, 'destroy'])->name('vehicles-list.destroy');

    // Vehicle History
    Route::get('/vehicle-history', [VehicleHistoryController::class, 'index'])->name('vehicle-history');
    Route::get('/vehicle-history/search', [VehicleHistoryController::class, 'search'])->name('vehicle-history.search');
    Route::get('/vehicle-history/timeline/{vehicleId}', [VehicleHistoryController::class, 'getTimeline'])->name('vehicle-history.timeline');
    Route::post('/vehicle-history', [VehicleHistoryController::class, 'store'])->name('vehicle-history.store');

    // Inventory
    Route::get('/inventory/items', [InventoryItemController::class, 'index'])->name('inventory.items');
    Route::get('/inventory/items-list', [InventoryItemController::class, 'dataBase'])->name('inventory.items-list');
    Route::post('/inventory/items', [InventoryItemController::class, 'store'])->name('inventory.items.store');
    Route::get('/inventory/items/{id}/edit', [InventoryItemController::class, 'edit'])->name('inventory.items.edit');
    Route::put('/inventory/items/{id}', [InventoryItemController::class, 'update'])->name('inventory.items.update');
    Route::delete('/inventory/items/{id}', [InventoryItemController::class, 'destroy'])->name('inventory.items.destroy');

    Route::get('/inventory/suppliers', [SupplierController::class, 'index'])->name('inventory.suppliers');
    Route::get('/inventory/suppliers-list', [SupplierController::class, 'dataBase'])->name('inventory.suppliers-list');
    Route::post('/inventory/suppliers', [SupplierController::class, 'store'])->name('inventory.suppliers.store');
    Route::get('/inventory/suppliers/{id}/edit', [SupplierController::class, 'edit'])->name('inventory.suppliers.edit');
    Route::put('/inventory/suppliers/{id}', [SupplierController::class, 'update'])->name('inventory.suppliers.update');
    Route::delete('/inventory/suppliers/{id}', [SupplierController::class, 'destroy'])->name('inventory.suppliers.destroy');

    // Stock Movements
    Route::get('/inventory/stock-in', [StockMovementController::class, 'stockIn'])->name('inventory.stock-in');
    Route::get('/inventory/stock-out', [StockMovementController::class, 'stockOut'])->name('inventory.stock-out');
    Route::get('/inventory/adjustments', [StockMovementController::class, 'stockAdjustment'])->name('inventory.adjustments');
    Route::get('/inventory/critical-stock', [InventoryAlertController::class, 'index'])->name('inventory.critical-stock');
    Route::get('/inventory/critical-stock-list', [InventoryAlertController::class, 'dataBase'])->name('inventory.critical-stock-list');
    Route::post('/inventory/movements', [StockMovementController::class, 'store'])->name('inventory.movements.store');
    Route::get('/inventory/movements-history', [StockMovementController::class, 'history'])->name('inventory.movements.history');

    // Services
    Route::get('/services/table', [ServiceController::class, 'index'])->name('services.table');
    Route::get('/services/list', [ServiceController::class, 'dataBase'])->name('services.list');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::get('/services/{id}/edit', [ServiceController::class, 'edit'])->name('services.edit');
    Route::put('/services/{id}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{id}', [ServiceController::class, 'destroy'])->name('services.destroy');

    // Service Packages
    Route::get('/services/packages', [ServicePackageController::class, 'index'])->name('services.packages');
    Route::get('/services/packages-list', [ServicePackageController::class, 'dataBase'])->name('services.packages-list');
    Route::post('/services/packages', [ServicePackageController::class, 'store'])->name('services.packages.store');
    Route::get('/services/packages/{id}/edit', [ServicePackageController::class, 'edit'])->name('services.packages.edit');
    Route::put('/services/packages/{id}', [ServicePackageController::class, 'update'])->name('services.packages.update');
    Route::delete('/services/packages/{id}', [ServicePackageController::class, 'destroy'])->name('services.packages.destroy');

    // Finance
    Route::get('/finance/accounts-receivable', [FinanceController::class, 'receivables'])->name('finance.receivables');
    Route::get('/finance/accounts-payable', [FinanceController::class, 'payables'])->name('finance.payables');
    Route::get('/finance/data', [FinanceController::class, 'dataBase'])->name('finance.data');
    Route::post('/finance/transactions', [FinanceController::class, 'store'])->name('finance.transactions.store');
    Route::post('/finance/transactions/{id}/pay', [FinanceController::class, 'markAsPaid'])->name('finance.transactions.pay');
    Route::delete('/finance/transactions/{id}', [FinanceController::class, 'destroy'])->name('finance.transactions.destroy');
    
    Route::get('/finance/cash-flow', [FinanceController::class, 'cashFlow'])->name('finance.cash-flow');
    
    Route::get('/finance/payment-methods', [PaymentMethodController::class, 'index'])->name('finance.payment-methods');
    Route::get('/finance/payment-methods-list', [PaymentMethodController::class, 'dataBase'])->name('finance.payment-methods.list');
    Route::post('/finance/payment-methods', [PaymentMethodController::class, 'store'])->name('finance.payment-methods.store');
    Route::delete('/finance/payment-methods/{id}', [PaymentMethodController::class, 'destroy'])->name('finance.payment-methods.destroy');

    Route::get('/finance/reports', [FinancialReportController::class, 'index'])->name('finance.reports');
    Route::get('/finance/reports/chart-data', [FinancialReportController::class, 'getChartData']);

    // Reports
    Route::get('/reports/os-status', [ReportController::class, 'osStatus'])->name('reports.os-status');
    Route::get('/reports/os-status-data', [ReportController::class, 'getOsStatusData']);
    Route::get('/reports/consumed-stock', [ReportController::class, 'consumedStock'])->name('reports.consumed-stock');
    Route::get('/reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('/reports/mechanic-performance', [ReportController::class, 'mechanicPerformance'])->name('reports.mechanic-performance');
    Route::get('/reports/average-time', [ReportController::class, 'averageTime'])->name('reports.average-time');

    // Settings

    Route::get('/settings/user-management', [UserManagement::class, 'index'])->name('pages-user-management');
    Route::get('/user-list', [UserManagement::class, 'dataBase'])->name('user-list');
    Route::get('/user-list/{id}/edit', [UserManagement::class, 'edit'])->name('user-list.edit');
    Route::post('/user-list', [UserManagement::class, 'store'])->name('user-list.store');
    Route::put('/user-list/{id}', [UserManagement::class, 'update'])->name('user-list.update');
    Route::delete('/user-list/{id}', [UserManagement::class, 'destroy'])->name('user-list.destroy');
    Route::post('/app/user/suspend/account', [UserManagement::class, 'suspendUser'])->name('user-management.suspend');

    //settings billing
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
});


Route::get('/teste-email', function () {
    Mail::raw('Teste de email via Gmail', function ($message) {
        $message->to('grafit933@gmail.com')
            ->subject('Teste Laravel Gmail');
    });

    return 'Email enviado';
});


Route::resource('inventories', App\Http\Controllers\InventoryController::class);
