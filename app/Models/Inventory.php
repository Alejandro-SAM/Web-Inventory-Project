<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    protected $table = 'inventory';

    protected $fillable = [
        'it_internal_number',
        'serial_number',
        'asset_number',
        'description',
        'model',
        'brand',
        'category',
        'warranty_start_date',
        'warranty_expiry_date',
        'purchase_origin_country',
        'department',
        'location',
        'business_unit',
        'plant',
        'end_user',
        'responsive',
        'employee_id',
        'next_maintenance',
        'maintenance_responsible_id',
        'maintenance_status',
        'operating_system',
        'confidentiality',
        'integrity',
        'availability',
        'classification',
        'comments',
        'created_by',
        'state',
    ];

    protected $casts = [
        'responsive' => 'boolean',
        'next_maintenance' => 'date',
        'warranty_start_date' => 'date',
        'warranty_expiry_date' => 'date',
    ];

    /**
     * User who created the inventory record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User currently responsible for the asset maintenance.
     */
    public function maintenanceResponsible(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'maintenance_responsible_id'
        );
    }

    /**
     * Maintenance history associated with the asset.
     */
    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(
            MaintenanceRecord::class,
            'inventory_id'
        );
    }

    /**
     * Effective maintenance status displayed by the application.
     *
     * An incomplete maintenance becomes overdue automatically when
     * its scheduled date is earlier than the current date.
     */
    protected function effectiveMaintenanceStatus(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->maintenance_status === 'completed') {
                return 'completed';
            }

            if ($this->maintenance_status === 'awaiting') {
                return 'awaiting';
            }

            if (
                $this->maintenance_status === 'pending'
                && $this->next_maintenance !== null
                && $this->next_maintenance->isBefore(today())
            ) {
                return 'overdue';
            }

            return 'pending';
        });
    }

    /**
     * Historial de ciclos de mantenimiento del activo.
     */
    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(
            MaintenanceRecord::class,
            'inventory_id'
        );
    }
}