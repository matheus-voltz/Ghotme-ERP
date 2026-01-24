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
    
    // Clients
    Route::get('/clients', [ClientsController::class, 'index'])->name('clients');

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
