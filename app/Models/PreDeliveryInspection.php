<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreDeliveryInspection extends Model
{
    use SoftDeletes;
    use BrandScope;

    protected $table = 'pre_delivery_inspections';

    protected $casts = [
        'accessories_complete' => 'boolean',
        'exterior_clean'       => 'boolean',
        'interior_clean'       => 'boolean',
        'issues_resolved'      => 'boolean',
    ];

    protected $fillable = [
        'salecar_id',
        'accessories_complete',
        'accessories_incomplete_items',
        'accessories_note',
        'exterior_clean',
        'exterior_incomplete_items',
        'exterior_note',
        'interior_clean',
        'interior_incomplete_items',
        'interior_note',
        'issues_resolved',
        'issues_detail',
        'issues_reason',
        'userZone',
        'brand',
        'branch',
        'UserInsert',
    ];

    public function salecar()
    {
        return $this->belongsTo(Salecar::class, 'salecar_id', 'id');
    }

    public function files()
    {
        return $this->hasMany(PreDeliveryInspectionFile::class, 'inspection_id');
    }

    public function docs()
    {
        return $this->hasMany(PreDeliveryInspectionFile::class, 'inspection_id')
            ->where('file_type', 'doc');
    }

    public function photos()
    {
        return $this->hasMany(PreDeliveryInspectionFile::class, 'inspection_id')
            ->where('file_type', 'photo');
    }

    public function logs()
    {
        return $this->hasMany(PreDeliveryInspectionLog::class, 'inspection_id')->orderByDesc('created_at');
    }
}
