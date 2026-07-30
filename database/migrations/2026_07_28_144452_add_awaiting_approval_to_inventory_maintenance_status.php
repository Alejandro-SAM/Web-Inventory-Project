<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE inventory
            MODIFY maintenance_status ENUM(
                'pending',
                'awaiting_approval',
                'completed'
            ) NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        DB::table('inventory')
            ->where('maintenance_status', 'awaiting_approval')
            ->update([
                'maintenance_status' => 'pending',
            ]);

        DB::statement("
            ALTER TABLE inventory
            MODIFY maintenance_status ENUM(
                'pending',
                'completed'
            ) NOT NULL DEFAULT 'pending'
        ");
    }
};