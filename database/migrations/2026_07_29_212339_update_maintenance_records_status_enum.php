<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Normaliza registros antiguos antes de modificar el ENUM.
         */
        DB::table('maintenance_records')
            ->where('status', 'awaiting_approval')
            ->update([
                'status' => 'pending',
            ]);

        DB::statement("
            ALTER TABLE maintenance_records
            MODIFY status ENUM(
                'pending',
                'awaiting',
                'completed',
                'rejected'
            ) NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        /*
         * Convierte awaiting antes de retirar ese valor.
         */
        DB::table('maintenance_records')
            ->where('status', 'awaiting')
            ->update([
                'status' => 'pending',
            ]);

        DB::statement("
            ALTER TABLE maintenance_records
            MODIFY status ENUM(
                'pending',
                'completed',
                'rejected'
            ) NOT NULL DEFAULT 'pending'
        ");
    }
};