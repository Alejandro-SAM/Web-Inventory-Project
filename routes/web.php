<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

    Route::view('/dashboard', 'dashboard')
        ->name('dashboard');

    Route::view('/inventory', 'inventory')
        ->name('inventory');

    Route::view('/users', 'users')
        ->name('users');

    Route::view('/logs', 'logs')
        ->name('logs');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__.'/auth.php';