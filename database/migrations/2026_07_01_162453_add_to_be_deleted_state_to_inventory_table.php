<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Add the new state used to mark inventory records
         * that should be deleted later.
         */
        DB::statement("
            ALTER TABLE inventory
            MODIFY state ENUM(
                'active',
                'inactive',
                'maintenance',
                'disposed',
                'lost',
                'to_be_deleted'
            ) NOT NULL DEFAULT 'active'
        ");
    }

    public function down(): void
    {
        /*
         * Change marked records before removing the enum value.
         */
        DB::table('inventory')
            ->where('state', 'to_be_deleted')
            ->update(['state' => 'inactive']);

        DB::statement("
            ALTER TABLE inventory
            MODIFY state ENUM(
                'active',
                'inactive',
                'maintenance',
                'disposed',
                'lost'
            ) NOT NULL DEFAULT 'active'
        ");
    }
};