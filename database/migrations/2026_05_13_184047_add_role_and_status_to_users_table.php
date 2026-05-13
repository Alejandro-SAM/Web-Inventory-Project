<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
        public function up(): void //FUNCION DE AUTENTICACIÓN DE USUARIOS.
        {
        Schema::table('users', function (Blueprint $table) {

        $table->string('role')
              ->default('guest');

        $table->boolean('is_active')
              ->default(true);

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void //Revierte migraciones
    {
    Schema::table('users', function (Blueprint $table) {

        $table->dropColumn('role');

        $table->dropColumn('is_active');

    });
}
};
