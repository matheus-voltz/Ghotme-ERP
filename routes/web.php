<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\HomePage;
use Illuminate\Support\Facades\Mail;

use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;



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
// Order Service Page Routes
Route::get('/allorders', [App\Http\Controllers\Allorders::class, 'index'])->name('allorders');
Route::get('/newOS', [App\Http\Controllers\newOs::class, 'index'])->name('newOS');
Route::get('/waitingOS', [App\Http\Controllers\waitingOs::class, 'index'])->name('waitingOS');
Route::get('/runningOS', [App\Http\Controllers\runningOs::class, 'index'])->name('runningOS');
Route::get('/endOS', [App\Http\Controllers\endOs::class, 'index'])->name('endOS');
Route::get('/canceledOS', [App\Http\Controllers\canceledOs::class, 'index'])->name('canceledOS');
Route::get('/checklist', [App\Http\Controllers\checklist::class, 'index'])->name('checklist');

//order service detail page route

// Quotes (Create and status)
Route::get('/quotes/create', [App\Http\Controllers\quotes\CreateQuote::class, 'index'])->name('quotes-create');
Route::get('/quotes/awaiting-approval', [App\Http\Controllers\quotes\AwaitingApproval::class, 'index'])->name('quotes-awaiting-approval');
Route::get('/quotes/approved', [App\Http\Controllers\quotes\Approved::class, 'index'])->name('quotes-approved');
Route::get('/quotes/rejected', [App\Http\Controllers\quotes\Rejected::class, 'index'])->name('quotes-rejected');
// Send quote by WhatsApp (expects quote id)
Route::get('/quotes/{id}/send-whatsapp', [App\Http\Controllers\quotes\SendWhatsapp::class, 'send'])->name('quotes-send-whatsapp');

//clients
Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('users-clients');

// routes/web.php ou api.php
Route::get('/users/datatable', [UserController::class, 'datatable'])
    ->name('users.datatable');

});


Route::get('/teste-email', function () {
    Mail::raw('Teste de email via Gmail', function ($message) {
        $message->to('grafit933@gmail.com')
                ->subject('Teste Laravel Gmail');
    });

    return 'Email enviado';
});