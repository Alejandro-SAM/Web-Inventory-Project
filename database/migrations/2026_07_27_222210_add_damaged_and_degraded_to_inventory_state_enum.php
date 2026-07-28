<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE inventory
            MODIFY state ENUM(
                'active',
                'damaged',
                'degraded',
                'inactive',
                'maintenance',
                'disposed',
                'lost',
                'to_be_deleted'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        /*
         * Before removing the new ENUM values, convert existing records
         * so the rollback does not fail.
         */
        DB::table('inventory')
            ->whereIn('state', ['damaged', 'degraded'])
            ->update(['state' => 'inactive']);

        DB::statement("
            ALTER TABLE inventory
            MODIFY state ENUM(
                'active',
                'inactive',
                'maintenance',
                'disposed',
                'lost',
                'to_be_deleted'
            ) NOT NULL
        ");
    }
};