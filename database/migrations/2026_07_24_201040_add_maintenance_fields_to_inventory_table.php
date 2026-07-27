<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the maintenance fields required by the maintenance module.
     */
    public function up(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            /*
             * User assigned to perform the maintenance.
             *
             * It remains nullable so existing inventory records
             * continue working while responsible users are assigned.
             */
            $table->foreignId('maintenance_responsible_id')
                ->nullable()
                ->after('next_maintenance')
                ->constrained('users')
                ->nullOnDelete();

            /*
             * Stored maintenance status.
             *
             * Overdue is not stored because it will be calculated
             * automatically using the next maintenance date.
             */
            $table->enum('maintenance_status', [
                'pending',
                'completed',
            ])
                ->default('pending')
                ->after('maintenance_responsible_id');
        });
    }

    /**
     * Remove the maintenance fields.
     */
    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropForeign([
                'maintenance_responsible_id',
            ]);

            $table->dropColumn([
                'maintenance_responsible_id',
                'maintenance_status',
            ]);
        });
    }
};