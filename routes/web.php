<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GmailController;

Route::view('/', 'dashboard')->name('dashboard');

// Gmail Authentication
Route::prefix('gmail')->group(function () {
    Route::get('/auth', [AuthController::class, 'redirectToGoogle'])->name('gmail.auth');
    Route::get('/callback', [AuthController::class, 'handleGoogleCallback'])->name('gmail.callback');

    // Gmail Features (Inbox, Compose, Send)
    Route::get('/inbox', [GmailController::class, 'inbox'])->name('gmail.inbox');
    Route::get('/compose', [GmailController::class, 'compose'])->name('gmail.compose');
    Route::post('/send', [GmailController::class, 'send'])->name('gmail.send');
});
