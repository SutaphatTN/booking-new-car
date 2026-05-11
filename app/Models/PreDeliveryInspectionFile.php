<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreDeliveryInspectionFile extends Model
{
    use SoftDeletes;

    protected $table = 'pre_delivery_inspection_files';

    protected $fillable = [
        'inspection_id',
        'file_type',
        'file_name',
        'file_url',
    ];

    public function inspection()
    {
        return $this->belongsTo(PreDeliveryInspection::class, 'inspection_id');
    }
}
