<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRecord extends Model
{
    protected $fillable = [
        'inventory_id',
        'maintenance_date',
        'responsible_id',
        'status',
        'completion_requested_by',
        'completion_requested_at',
        'reviewed_by',
        'reviewed_at',
        'completed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'completion_requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Activo relacionado con este ciclo de mantenimiento.
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(
            Inventory::class,
            'inventory_id'
        );
    }

    /**
     * Usuario responsable del mantenimiento.
     */
    public function responsible(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'responsible_id'
        );
    }

    /**
     * Usuario que solicitó finalizar el mantenimiento.
     */
    public function completionRequestedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'completion_requested_by'
        );
    }

    /**
     * Administrador que revisó el mantenimiento.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }
}