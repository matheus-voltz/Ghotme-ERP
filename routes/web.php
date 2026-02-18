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
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\IntegrationSettingController;
use App\Http\Controllers\PrintTemplateController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\VehicleChecklistController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\PublicBudgetController;
use App\Http\Controllers\SystemErrorController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\NotificationController;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;


use App\Http\Controllers\VehicleLookupController;

/*
|--------------------------------------------------------------------------
| Email Verification
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Landing Page
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('content.font-pages.landing-page');
})->name('welcome');


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

// Public Budget Approval
Route::get('/view-budget/{uuid}', [PublicBudgetController::class, 'show'])->name('public.budget.show');
Route::get('/view-budget/{uuid}/checkout', [PublicBudgetController::class, 'checkout'])->name('public.budget.checkout');
Route::post('/view-budget/{uuid}/approve', [PublicBudgetController::class, 'approve'])->name('public.budget.approve');
Route::post('/view-budget/{uuid}/reject', [PublicBudgetController::class, 'reject'])->name('public.budget.reject');

// Public Checklist View
Route::get('/view-checklist/{id}', [VehicleChecklistController::class, 'show'])->name('public.checklist.show');
Route::post('/ordens-servico/checklist/{id}/send-email', [VehicleChecklistController::class, 'sendEmail'])->name('ordens-servico.checklist.send-email');

// Customer Portal
Route::get('/portal/{uuid}', [CustomerPortalController::class, 'index'])->name('customer.portal.index');
Route::get('/portal/os/{uuid}', [CustomerPortalController::class, 'showOrder'])->name('customer.portal.order');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    App\Http\Middleware\CheckTrialStatus::class,
])->group(function () {

    // Main Page Route
    Route::get('/dashboard', [HomePage::class, 'index'])->name('dashboard');
    Route::get('/ordens-servico', [OrdemServicoController::class, 'index'])->name('ordens-servico');
    Route::get('/ordens-servico/create', [OrdemServicoController::class, 'create'])->name('ordens-servico.create');
    Route::get('/ordens-servico/data', [OrdemServicoController::class, 'dataBase'])->name('ordens-servico.data');
    Route::post('/ordens-servico', [OrdemServicoController::class, 'store'])->name('ordens-servico.store');
    Route::post('/ordens-servico/{id}/status', [OrdemServicoController::class, 'updateStatus'])->name('ordens-servico.status');
    Route::get('/ordens-servico/{id}/edit', [OrdemServicoController::class, 'edit'])->name('ordens-servico.edit');
    Route::put('/ordens-servico/{id}', [OrdemServicoController::class, 'update'])->name('ordens-servico.update');
    Route::get('/api/get-vehicles/{clientId}', [OrdemServicoController::class, 'getVehiclesByClient']);

    Route::get('/ordens-servico/checklist', [VehicleChecklistController::class, 'index'])->name('ordens-servico.checklist');
    Route::get('/ordens-servico/checklist/create', [VehicleChecklistController::class, 'create'])->name('ordens-servico.checklist.create');
    Route::post('/ordens-servico/checklist', [VehicleChecklistController::class, 'store'])->name('ordens-servico.checklist.store');
    Route::get('/ordens-servico/checklist/{id}', [VehicleChecklistController::class, 'show'])->name('ordens-servico.checklist.show');

    // Budgets
    Route::get('/budgets/{id}/quick-view', [BudgetController::class, 'quickView'])->name('budgets.quick-view');
    Route::get('/budgets/pending', [BudgetController::class, 'index'])->name('budgets.pending');
    Route::get('/budgets/approved', [BudgetController::class, 'index'])->name('budgets.approved');
    Route::get('/budgets/rejected', [BudgetController::class, 'index'])->name('budgets.rejected');
    Route::get('/budgets/send-whatsapp', [BudgetController::class, 'index'])->name('budgets.send-whatsapp');
    Route::get('/budgets/create', [BudgetController::class, 'create'])->name('budgets.create');
    Route::get('/budgets/data', [BudgetController::class, 'dataBase'])->name('budgets.data');
    Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');
    Route::post('/budgets/{id}/status', [BudgetController::class, 'updateStatus'])->name('budgets.status');
    Route::post('/budgets/{id}/convert', [BudgetController::class, 'convertToOS'])->name('budgets.convert');
    Route::get('/budgets/{id}/whatsapp', [BudgetController::class, 'sendWhatsApp'])->name('budgets.whatsapp');

    // Clients
    Route::get('/clients/{id}/quick-view', [ClientsController::class, 'quickView'])->name('clients.quick-view');
    Route::get('/clients', [ClientsController::class, 'index'])->name('clients');
    Route::get('/clients-list', [ClientsController::class, 'dataBase'])->name('clients-list');
    Route::post('/clients-list', [ClientsController::class, 'store'])->name('clients-list.store');
    Route::get('/clients-list/{id}/edit', [ClientsController::class, 'edit'])->name('clients-list.edit');
    Route::put('/clients-list/{id}', [ClientsController::class, 'update'])->name('clients-list.update');
    Route::delete('/clients-list/{id}', [ClientsController::class, 'destroy'])->name('clients-list.destroy');

    // Vehicles
    Route::get('/vehicles/{id}/dossier', [VehiclesController::class, 'getDossier'])->name('vehicles.dossier');
    Route::get('/vehicles', [VehiclesController::class, 'index'])->name('vehicles');
    Route::get('/vehicles-list', [VehiclesController::class, 'dataBase'])->name('vehicles-list');
    Route::get('/vehicles-list/{id}/edit', [VehiclesController::class, 'edit'])->name('vehicles-list.edit');
    Route::post('/vehicles-list', [VehiclesController::class, 'store'])->name('vehicles-list.store');
    Route::delete('/vehicles-list/{id}', [VehiclesController::class, 'destroy'])->name('vehicles-list.destroy');
    Route::get('/api/vehicle-lookup/{placa}', [VehicleLookupController::class, 'lookup'])->name('vehicles.lookup');

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

    // Accounting & Fiscal
    Route::get('/accounting', [App\Http\Controllers\AccountingController::class, 'index'])->name('accounting.index');
    Route::get('/accounting/export-xml', [App\Http\Controllers\AccountingController::class, 'exportXml'])->name('accounting.export');
    Route::get('/fiscal/emit-invoice', [App\Http\Controllers\TaxInvoiceController::class, 'createFromOS'])->name('tax.invoice.create');
    
    // Reports
    Route::get('/reports/profitability', [App\Http\Controllers\ProfitabilityReportController::class, 'index'])->name('reports.profitability');
    Route::get('/reports/os-status', [ReportController::class, 'osStatus'])->name('reports.os-status');
    Route::get('/reports/os-status-data', [ReportController::class, 'getOsStatusData']);
    Route::get('/reports/consumed-stock', [ReportController::class, 'consumedStock'])->name('reports.consumed-stock');
    Route::get('/reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('/reports/mechanic-performance', [ReportController::class, 'mechanicPerformance'])->name('reports.mechanic-performance');
    Route::get('/reports/cost-per-service', [ReportController::class, 'costPerService'])->name('reports.cost-per-service');
    Route::get('/reports/average-time', [ReportController::class, 'averageTime'])->name('reports.average-time');
    Route::get('/reports/average-time-per-service', [ReportController::class, 'averageTimePerService'])->name('reports.average-time-per-service');

    // Settings
    Route::get('/settings/company-data', [CompanySettingController::class, 'index'])->name('settings.company-data');
    Route::post('/settings/company-data', [CompanySettingController::class, 'update'])->name('settings.company-data.update');

    // Custom Checklist
    Route::get('/settings/custom-checklist', [ChecklistController::class, 'index'])->name('settings.custom-checklist');
    Route::get('/settings/custom-checklist-list', [ChecklistController::class, 'dataBase'])->name('settings.custom-checklist.list');
    Route::post('/settings/custom-checklist', [ChecklistController::class, 'store'])->name('settings.custom-checklist.store');
    Route::delete('/settings/custom-checklist/{id}', [ChecklistController::class, 'destroy'])->name('settings.custom-checklist.destroy');

    // OS Settings
    Route::get('/settings/os-settings', [AppSettingController::class, 'index'])->name('settings.os-settings');
    Route::post('/settings/os-settings', [AppSettingController::class, 'update'])->name('settings.os-settings.update');

    // Integrations
    Route::get('/settings/integrations', [IntegrationSettingController::class, 'index'])->name('settings.integrations');
    Route::post('/settings/integrations', [IntegrationSettingController::class, 'update'])->name('settings.integrations.update');

    // Print Templates
    Route::get('/settings/print-templates', [PrintTemplateController::class, 'index'])->name('settings.print-templates');
    Route::get('/settings/print-templates/{id}/edit', [PrintTemplateController::class, 'edit'])->name('settings.print-templates.edit');
    Route::post('/settings/print-templates/{id}', [PrintTemplateController::class, 'update'])->name('settings.print-templates.update');

    // System Errors
    Route::get('/settings/system-errors', [SystemErrorController::class, 'index'])->name('settings.system-errors');
    Route::get('/settings/system-errors/{id}', [SystemErrorController::class, 'show'])->name('settings.system-errors.show');
    Route::delete('/settings/system-errors/clear', [SystemErrorController::class, 'destroyAll'])->name('settings.system-errors.clear');

    Route::get('/settings/user-management', [UserManagement::class, 'index'])->name('pages-user-management');

    // Team Management
    Route::get('/settings/team', [App\Http\Controllers\TeamController::class, 'index'])->name('team-management');
    Route::get('/settings/team/data', [App\Http\Controllers\TeamController::class, 'dataBase'])->name('team-management.data');
    Route::post('/settings/team', [App\Http\Controllers\TeamController::class, 'store'])->name('team-management.store');
    Route::get('/settings/team/{id}/edit', [App\Http\Controllers\TeamController::class, 'edit'])->name('team-management.edit');
    Route::delete('/settings/team/{id}', [App\Http\Controllers\TeamController::class, 'destroy'])->name('team-management.destroy');

    // New Team Route for "Equipe > Colaboradores"
    Route::get('/team/employees', [App\Http\Controllers\TeamController::class, 'index'])->name('team.employees');

    Route::get('/user-list', [UserManagement::class, 'dataBase'])->name('user-list');
    Route::get('/user-list/{id}/edit', [UserManagement::class, 'edit'])->name('user-list.edit');
    Route::post('/user-list', [UserManagement::class, 'store'])->name('user-list.store');
    Route::put('/user-list/{id}', [UserManagement::class, 'update'])->name('user-list.update');
    Route::delete('/user-list/{id}', [UserManagement::class, 'destroy'])->name('user-list.destroy');
    Route::post('/app/user/suspend/account', [UserManagement::class, 'suspendUser'])->name('user-management.suspend');

    //settings billing
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/generate-payment', [SettingsController::class, 'generatePayment'])->name('settings.generate-payment');
    Route::post('/settings/select-plan', [SettingsController::class, 'selectPlan'])->name('settings.select-plan');
    Route::post('/settings/update-profile', [SettingsController::class, 'updateProfile'])->name('settings.update-profile');

    // Support
    Route::get('/support/chat-whatsapp', [SupportController::class, 'chatWhatsapp'])->name('support.whatsapp');
    Route::get('/support/chat', [SupportController::class, 'chat'])->name('support.chat');
    Route::get('/calendar', [App\Http\Controllers\CalendarController::class, 'index'])->name('calendar');
    Route::get('/kanban', [App\Http\Controllers\KanbanController::class, 'index'])->name('kanban');
    Route::get('/kanban/data', [App\Http\Controllers\KanbanController::class, 'fetch'])->name('kanban.fetch');
    Route::post('/kanban/add-board', [App\Http\Controllers\KanbanController::class, 'addBoard'])->name('kanban.add-board');
    Route::post('/kanban/add-item', [App\Http\Controllers\KanbanController::class, 'addItem'])->name('kanban.add-item');
    Route::post('/kanban/move-item', [App\Http\Controllers\KanbanController::class, 'moveItem'])->name('kanban.move-item');
    Route::put('/kanban/update-item/{id}', [App\Http\Controllers\KanbanController::class, 'updateItem'])->name('kanban.update-item');
    Route::delete('/kanban/delete-item/{id}', [App\Http\Controllers\KanbanController::class, 'deleteItem'])->name('kanban.delete-item');

    Route::get('/calendar/events', [App\Http\Controllers\CalendarController::class, 'fetchEvents'])->name('calendar.fetch');
    Route::post('/calendar/events', [App\Http\Controllers\CalendarController::class, 'store'])->name('calendar.store');
    Route::put('/calendar/events/{id}', [App\Http\Controllers\CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/events/{id}', [App\Http\Controllers\CalendarController::class, 'destroy'])->name('calendar.destroy');

    Route::get('/support/knowledge-base', [SupportController::class, 'knowledgeBase'])->name('support.knowledge-base');
    Route::get('/support/open-ticket', [SupportController::class, 'openTicket'])->name('support.open-ticket');
    Route::post('/support/open-ticket', [SupportController::class, 'sendTicket'])->name('support.open-ticket.send');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.individual.mark-as-read');

    // Mechanic Dashboard
    Route::get('/mechanic', [App\Http\Controllers\MechanicDashboardController::class, 'index'])->name('mechanic.dashboard');
    Route::get('/mechanic/os/{uuid}', [App\Http\Controllers\MechanicDashboardController::class, 'show'])->name('mechanic.os.show');
    Route::post('/mechanic/timer/{itemId}', [App\Http\Controllers\MechanicDashboardController::class, 'toggleTimer'])->name('mechanic.timer.toggle');
    Route::post('/mechanic/complete/{itemId}', [App\Http\Controllers\MechanicDashboardController::class, 'completeItem'])->name('mechanic.item.complete');

    // TESTE DE NOTIFICAÇÃO PUSH REAL
    Route::get('/push-test/{token}', function(\Illuminate\Http\Request $request, $token) {
        $fullToken = "ExponentPushToken[" . $token . "]";
        $res = \App\Helpers\Helpers::sendExpoNotification(
            $fullToken, 
            "Ghotme ERP 🚀", 
            "Novo orçamento #1059 recebido agora mesmo!"
        );
        return response()->json([
            'status' => 'Enviado',
            'token_usado' => $fullToken,
            'resposta_expo' => $res
        ]);
    });
});


Route::get('/teste-email', function () {
    Mail::raw('Teste de email via Gmail', function ($message) {
        $message->to('grafit933@gmail.com')
            ->subject('Teste Laravel Gmail');
    });

    return 'Email enviado';
});


// Asaas Webhook
Route::post('/webhook/asaas', [WebhookController::class, 'asaas']);

// Route::resource('inventories', App\Http\Controllers\InventoryController::class);
