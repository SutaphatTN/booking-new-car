<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class CustomerTrackingDetail extends Model
{
    use SoftDeletes;

    protected $table = 'customer_tracking_details';

    protected $fillable = [
        'tracking_id',
        'contact_date',
        'contact_status',
        'decision_id',
        'comment_sale',
        'entry_type',
        'is_checkpoint',
        'UserInsert',
        'UserUpdate',
    ];

    public function tracking()
    {
        return $this->belongsTo(CustomerTracking::class, 'tracking_id');
    }

    public function decision()
    {
        return $this->belongsTo(TbDecision::class, 'decision_id');
    }

    public function insertedBy()
    {
        return $this->belongsTo(User::class, 'UserInsert');
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
