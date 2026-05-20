<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();

            // Identificación del activo
            $table->string('it_internal_number')->nullable()->unique();
            $table->string('serial_number')->nullable();
            $table->string('asset_number')->nullable();

            // Información general
            $table->text('description')->nullable();
            $table->string('model')->nullable();
            $table->string('brand')->nullable();
            $table->string('category')->nullable();

            // Ubicación
            $table->string('department')->nullable();
            $table->string('location')->nullable();
            $table->string('business_unit')->nullable();
            $table->string('plant')->nullable();
            $table->string('end_user')->nullable();
            $table->boolean('responsive')->default(false);
            $table->string('employee_id')->nullable();

            // Mantenimiento / OS
            $table->date('next_maintenance')->nullable();
            $table->string('operating_system')->nullable();

            // Clasificación CIA
            $table->tinyInteger('confidentiality')->nullable();
            $table->tinyInteger('integrity')->nullable();
            $table->tinyInteger('availability')->nullable();
            $table->unsignedTinyInteger('classification')->nullable();

            // Comentarios
            $table->text('comments')->nullable();

            // Control interno
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Estado del activo
            $table->enum('state', [
                'active',
                'inactive',
                'maintenance',
                'disposed',
                'lost'
            ])->default('active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
