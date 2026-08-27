<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */

    Route::get('/users', [UserController::class, 'index'])
        ->name('users.index');

    Route::post('/users', [UserController::class, 'store'])
        ->name('users.store');

    Route::put('/users/{user}', [UserController::class, 'update'])
        ->name('users.update');

    /*
    |--------------------------------------------------------------------------
    | Logs
    |--------------------------------------------------------------------------
    */

    Route::get('/logs', [LogsController::class, 'index'])
        ->name('logs');

    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    */

    Route::get('/inventory', [InventoryController::class, 'index'])
        ->name('inventory');

    Route::get('/inventory/create', [InventoryController::class, 'create'])
        ->name('inventory.create');

    Route::post('/inventory', [InventoryController::class, 'store'])
        ->name('inventory.store');

    Route::put('/inventory/{inventory}', [InventoryController::class, 'update'])
        ->name('inventory.update');

    Route::get(
        '/inventory/{inventory}/print-data',
        [InventoryController::class, 'downloadPrintData']
    )->name('inventory.print-data');

    /*
    |--------------------------------------------------------------------------
    | Inventory import
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/inventory/import/preview',
        [InventoryController::class, 'importPreview']
    )->name('inventory.import.preview');

    Route::get(
        '/inventory/import/{batchId}/review',
        [InventoryController::class, 'importReview']
    )->name('inventory.import.review');

    Route::get(
        '/inventory/import/{batchId}/invalid',
        [InventoryController::class, 'reviewInvalidRows']
    )->name('inventory.import.invalid');

    Route::put(
        '/inventory/import/row/{row}',
        [InventoryController::class, 'updateImportRow']
    )->name('inventory.import.row.update');

    Route::delete(
        '/inventory/import/row/{row}',
        [InventoryController::class, 'destroyImportRow']
    )->name('inventory.import.row.destroy');

    Route::post(
        '/inventory/import/{batchId}/confirm',
        [InventoryController::class, 'confirmImport']
    )->name('inventory.import.confirm');

    Route::post(
        '/inventory/import/{batchId}/cancel',
        [InventoryController::class, 'cancelImport']
    )->name('inventory.import.cancel');

    /*
    |--------------------------------------------------------------------------
    | Inventory deletion
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/inventory/delete-marked',
        [InventoryController::class, 'destroyMarked']
    )->name('inventory.destroy-marked');

    Route::delete(
        '/inventory/{inventory}',
        [InventoryController::class, 'destroy']
    )->name('inventory.destroy');

    /*
    |--------------------------------------------------------------------------
    | Maintenance
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/maintenance',
        [MaintenanceController::class, 'index']
    )->name('maintenance.index');

    Route::get(
    '/maintenance/history',
    [MaintenanceController::class, 'history']
    )->name('maintenance.history');

    Route::patch(
        '/maintenance/{inventory}/finalize',
        [MaintenanceController::class, 'requestCompletion']
    )->name('maintenance.finalize');

    Route::patch(
        '/maintenance/{inventory}/approve',
        [MaintenanceController::class, 'approve']
    )->name('maintenance.approve');

    Route::patch(
        '/maintenance/{inventory}/reject',
        [MaintenanceController::class, 'reject']
    )->name('maintenance.reject');

    Route::patch(
        '/maintenance/{inventory}/schedule-next',
        [MaintenanceController::class, 'scheduleNext']
    )->name('maintenance.schedule-next');

    Route::patch(
        '/maintenance/{inventory}/assign',
        [MaintenanceController::class, 'assign']
    )->name('maintenance.assign');

});

require __DIR__.'/auth.php';
