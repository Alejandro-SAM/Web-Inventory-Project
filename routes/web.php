<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController; /* FOR USERS TABLE */
use App\Http\Controllers\LogsController; /* FOR LOGS TABLE */
use App\Http\Controllers\InventoryController; /* FOR INVENTORY TABLE */
use App\Http\Controllers\DashboardController;

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

    Route::get('/inventory', [InventoryController::class, 'index'])
        ->middleware(['auth'])
        ->name('inventory');

    Route::get('/inventory/create', [InventoryController::class, 'create'])
        ->name('inventory.create');

    Route::post('/inventory', [InventoryController::class, 'store'])
        ->name('inventory.store');

    Route::put('/inventory/{inventory}', [InventoryController::class, 'update'])
        ->name('inventory.update');

    Route::get('/inventory/{inventory}/print-data', [InventoryController::class, 'downloadPrintData'])
        ->middleware(['auth'])
        ->name('inventory.print-data'); // For downloading print data for a specific inventory item
    
    Route::post('/inventory/import/preview', [InventoryController::class, 'importPreview'])
        ->name('inventory.import.preview');

    Route::get('/inventory/import/{batchId}/review', [InventoryController::class, 'importReview'])
        ->name('inventory.import.review');

    Route::get('/inventory/import/{batchId}/invalid', [InventoryController::class, 'reviewInvalidRows'])
        ->name('inventory.import.invalid');

    Route::put('/inventory/import/row/{row}', [InventoryController::class, 'updateImportRow'])
        ->name('inventory.import.row.update');

    Route::post('/inventory/import/{batchId}/confirm', [InventoryController::class, 'confirmImport'])
        ->name('inventory.import.confirm');

    Route::post('/inventory/import/{batchId}/cancel', [InventoryController::class, 'cancelImport'])
        ->name('inventory.import.cancel');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['auth'])
        ->name('dashboard');

    // For deleting all inventory items marked as "to_be_deleted".
    Route::delete('/inventory/delete-marked', [InventoryController::class, 'destroyMarked'])
        ->name('inventory.destroy-marked');

    // For deleting and inventory item.
    Route::delete('/inventory/{inventory}', [InventoryController::class, 'destroy'])
        ->name('inventory.destroy');

});

require __DIR__.'/auth.php';