<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inventory_id')
                ->constrained('inventory')
                ->cascadeOnDelete();

            $table->date('maintenance_date')->nullable();

            $table->foreignId('responsible_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('status', [
                'pending',
                'awaiting_approval',
                'completed',
                'rejected',
            ])->default('pending');

            $table->foreignId('completion_requested_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('completion_requested_at')->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            $table->timestamp('completed_at')->nullable();

            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->index([
                'inventory_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_records');
    }
};