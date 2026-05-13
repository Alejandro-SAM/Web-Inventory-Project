<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login'); //Redirección a pagina Login en lugar de pagina default de breezeph
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () { //RUTA DE AUTENTICACIÓN MIDDLEWARE PARA LOGIN
                                                 //SI NO EXISTE UNA AUTENTICACIÓN REBOTA A LOGIN.
    Route::get('/dashboard', function () {

        return view('dashboard');

    })->name('dashboard');

});

require __DIR__.'/auth.php';
