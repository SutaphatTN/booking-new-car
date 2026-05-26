<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SsiRecord extends Model
{
    use SoftDeletes;
    use BrandScope;

    protected $table = 'ssi_records';

    protected $fillable = [
        'salecar_id',
        'userZone',
        'brand',
        'branch',
        'UserInsert',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function salecar()
    {
        return $this->belongsTo(Salecar::class, 'salecar_id', 'id');
    }

    public function contacts()
    {
        return $this->hasMany(SsiContact::class, 'ssi_record_id')->orderBy('contact_date');
    }

    public function assessment()
    {
        return $this->hasOne(SsiAssessment::class, 'ssi_record_id');
    }

    public function payment()
    {
        return $this->hasOne(SsiPayment::class, 'ssi_record_id');
    }

    public function feedback()
    {
        return $this->hasOne(SsiFeedback::class, 'ssi_record_id');
    }

    public function resolution()
    {
        return $this->hasOne(SsiResolution::class, 'ssi_record_id');
    }
}
