<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SourcePlaceClear extends Model
{
    use SoftDeletes;
    use BrandScope;

    protected $table = 'source_place_clears';

    protected $fillable = [
        'place_id',
        'clear_date',
        'total',
        'pay_date',
        'pay_approved',
        'pay_approved_by',
        'pay_approved_at',
        'brand',
        'userZone',
        'branch',
        'UserInsert',
    ];

    protected $casts = [
        'clear_date'      => 'date',
        'pay_date'        => 'date',
        'pay_approved'    => 'boolean',
        'pay_approved_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    public function place()
    {
        return $this->belongsTo(SourcePlace::class, 'place_id');
    }

    public function items()
    {
        return $this->hasMany(SourcePlaceClearItem::class, 'clear_id');
    }

    public function payApprover()
    {
        return $this->belongsTo(User::class, 'pay_approved_by');
    }
}
