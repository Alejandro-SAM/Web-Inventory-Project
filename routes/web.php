<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController; /* FOR USERS TABLE */
use App\Http\Controllers\LogsController; /* FOR LOGS TABLE */

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () { /* AUTHENTICATION PROTECTED ROUTES */

    Route::view('/dashboard', 'dashboard')
        ->name('dashboard');

    Route::view('/inventory', 'inventory')
        ->name('inventory');

    Route::view('/logs', 'logs')
        ->name('logs');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::get('/users', [UserController::class, 'index']) /* FOR USERS TABLE */
        ->name('users.index');

    Route::get('/logs', [LogsController::class, 'index']) /* FOR LOGS TABLE */
    ->name('logs');

    Route::post('/users', [UserController::class, 'store'])
        ->name('users.store');

    Route::put('/users/{user}', [UserController::class, 'update'])
        ->name('users.update');
});

require __DIR__.'/auth.php';