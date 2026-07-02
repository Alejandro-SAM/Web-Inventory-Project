<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create temporary rows table for inventory Excel imports.
     */
    public function up(): void
    {
        Schema::create('inventory_import_rows', function (Blueprint $table) {
            $table->id();

            // Groups all rows from the same uploaded Excel file.
            $table->uuid('batch_id')->index();

            // Original Excel row number.
            $table->unsignedInteger('row_number');

            // Original Excel row data.
            $table->json('raw_data')->nullable();

            // Cleaned and converted data ready for inventory.
            $table->json('normalized_data')->nullable();

            // List of validation or conversion errors.
            $table->json('errors')->nullable();

            // Current row status inside the import process.
            $table->enum('status', [
                'valid',
                'invalid',
                'imported',
                'cancelled',
            ])->default('invalid');

            // User who uploaded the Excel file.
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['batch_id', 'status']);
        });
    }

    /**
     * Drop temporary import rows table.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_import_rows');
    }
};