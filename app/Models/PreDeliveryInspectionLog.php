<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreDeliveryInspectionLog extends Model
{
    public $timestamps = false;

    protected $table = 'pre_delivery_inspection_logs';

    protected $fillable = [
        'inspection_id',
        'salecar_id',
        'accessories_complete',
        'accessories_incomplete_items',
        'exterior_clean',
        'exterior_incomplete_items',
        'interior_clean',
        'interior_incomplete_items',
        'issues_resolved',
        'issues_detail',
        'UserInsert',
    ];

    protected $casts = [
        'created_at'           => 'datetime',
        'accessories_complete' => 'boolean',
        'exterior_clean'       => 'boolean',
        'interior_clean'       => 'boolean',
        'issues_resolved'      => 'boolean',
    ];
}
