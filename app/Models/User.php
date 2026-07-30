<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use Notifiable;

    const UPDATED_AT = null;

    protected $fillable = [
        'employee_number',
        'name',
        'department',
        'user_level',
        'is_active',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Activos donde el usuario es responsable actual.
     */
    public function assignedMaintenanceInventory(): HasMany
    {
        return $this->hasMany(
            Inventory::class,
            'maintenance_responsible_id'
        );
    }

    /**
     * Ciclos donde el usuario fue responsable.
     */
    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(
            MaintenanceRecord::class,
            'responsible_id'
        );
    }

    /**
     * Solicitudes de finalización realizadas por el usuario.
     */
    public function requestedMaintenanceCompletions(): HasMany
    {
        return $this->hasMany(
            MaintenanceRecord::class,
            'completion_requested_by'
        );
    }

    /**
     * Revisiones administrativas realizadas por el usuario.
     */
    public function reviewedMaintenanceRecords(): HasMany
    {
        return $this->hasMany(
            MaintenanceRecord::class,
            'reviewed_by'
        );
    }
}