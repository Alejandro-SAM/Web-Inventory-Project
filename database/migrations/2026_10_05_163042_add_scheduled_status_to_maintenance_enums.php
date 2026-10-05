<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE inventory
            MODIFY maintenance_status
            ENUM('pending', 'awaiting', 'completed', 'scheduled')
            NOT NULL DEFAULT 'pending'
        ");

        DB::statement("
            ALTER TABLE maintenance_records
            MODIFY status
            ENUM('pending', 'awaiting', 'completed', 'rejected', 'scheduled')
            NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        /*
         * Antes de eliminar 'scheduled', regresamos cualquier registro
         * existente a 'pending' para evitar errores al modificar el ENUM.
         */
        DB::table('inventory')
            ->where('maintenance_status', 'scheduled')
            ->update([
                'maintenance_status' => 'pending',
            ]);

        DB::table('maintenance_records')
            ->where('status', 'scheduled')
            ->update([
                'status' => 'pending',
            ]);

        DB::statement("
            ALTER TABLE inventory
            MODIFY maintenance_status
            ENUM('pending', 'awaiting', 'completed')
            NOT NULL DEFAULT 'pending'
        ");

        DB::statement("
            ALTER TABLE maintenance_records
            MODIFY status
            ENUM('pending', 'awaiting', 'completed', 'rejected')
            NOT NULL DEFAULT 'pending'
        ");
    }
};