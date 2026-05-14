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
    {   //ESQUEMA DE LA TABLA DE USUARIOS
        Schema::create('users', function (Blueprint $table) {
        $table->id();

        // Número de empleado: será usado para iniciar sesión
        $table->string('employee_number')->unique();

        // Nombre completo del empleado
        $table->string('name');

        // Departamento o área del empleado
        $table->string('department')->nullable();

        // Nivel de usuario dentro del sistema
        $table->enum('user_level', ['Admin', 'User', 'Read'])->default('Read');

        // Estado de la cuenta
        // true = activa, false = desactivada
        $table->boolean('is_active')->default(true);

        // Contraseña cifrada
        $table->string('password');

        // Token para "recordar usuario"
        $table->rememberToken();

        // Fecha de creación
        $table->timestamp('created_at')->nullable();
    });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('employee_number')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
