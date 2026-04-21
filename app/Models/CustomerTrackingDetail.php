<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CustomerTrackingDetail extends Model
{
    protected $table = 'customer_tracking_details';

    protected $fillable = [
        'tracking_id',
        'contact_date',
        'contact_status',
        'decision_id',
        'comment_sale',
        'UserInsert',
    ];

    public function tracking()
    {
        return $this->belongsTo(CustomerTracking::class, 'tracking_id');
    }

    public function decision()
    {
        return $this->belongsTo(TbDecision::class, 'decision_id');
    }

    public function getContactDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getFormatContactDateAttribute()
    {
        return $this->contact_date ? Carbon::parse($this->contact_date)->format('d-m-Y') : null;
    }
}
