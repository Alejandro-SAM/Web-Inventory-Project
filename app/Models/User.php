<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
}