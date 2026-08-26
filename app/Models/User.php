<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot([
                'id',
                'plant',
                'is_active',
            ])
            ->withTimestamps();
    }

    public function hasBadge(string $slug): bool
    {
        /*
        |--------------------------------------------------------------------------
        | Administrators automatically have access to all badge permissions
        |--------------------------------------------------------------------------
        */
        if ($this->user_level === 'Admin') {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | Only User-level accounts can currently use badges
        |--------------------------------------------------------------------------
        */
        if ($this->user_level !== 'User') {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Check active badge assignment
        |--------------------------------------------------------------------------
        */
        return $this->badges()
            ->where('badges.slug', $slug)
            ->wherePivot('is_active', true)
            ->exists();
    }


    public function hasBadgeForPlant(string $slug, string $plant): bool
    {
        if ($this->user_level === 'Admin') {
            return true;
        }

        if ($this->user_level !== 'User') {
            return false;
        }

        return $this->badges()
            ->where('badges.slug', $slug)
            ->wherePivot('plant', $plant)
            ->wherePivot('is_active', true)
            ->exists();
    }
    
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