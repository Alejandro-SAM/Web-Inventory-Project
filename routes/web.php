<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login'); //Redirección a pagina Login en lugar de pagina default de breezeph
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () { //RUTAS HACIA VISTAS DE PAGINA
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit'); //RUTA EDITAR PERFIL
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update'); //RUTA ACTUALIZAR PERFIL
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy'); //RUTA DESTRUIR PERFIL

        Route::view('/dashboard', 'dashboard') //RUTA AL DASHBOARD
        ->name('dashboard');

    Route::view('/inventory', 'inventory') //RUTA AL INVENTARIO
        ->name('inventory');

    Route::view('/users', 'users') //RUTA A TABLA DE USUARIOS
        ->name('users');

    Route::view('/logs', 'logs') //RUTA A TABLA DE REGISTROS
        ->name('logs');
});

Route::middleware(['auth'])->group(function () { //RUTA DE AUTENTICACIÓN MIDDLEWARE PARA LOGIN
                                                 //SI NO EXISTE UNA AUTENTICACIÓN REBOTA A LOGIN.
    Route::get('/dashboard', function () {

        return view('dashboard');

    })->name('dashboard');

});

require __DIR__.'/auth.php';
