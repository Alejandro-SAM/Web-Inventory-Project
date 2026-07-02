<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}