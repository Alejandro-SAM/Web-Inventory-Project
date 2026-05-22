<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryImportRow extends Model
{
    protected $fillable = [
        'batch_id',
        'row_number',
        'raw_data',
        'normalized_data',
        'errors',
        'status',
        'created_by',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'normalized_data' => 'array',
        'errors' => 'array',
    ];
}
