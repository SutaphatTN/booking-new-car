<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ServiceCheckTrackingDetail extends Model
{
    protected $table = 'service_check_tracking_details';

    protected $fillable = [
        'tracking_id',
        'check_date',
        'mileage',
        'note',
        'UserInsert',
    ];

    public function tracking()
    {
        return $this->belongsTo(ServiceCheckTracking::class, 'tracking_id');
    }

    public function getFormatCheckDateAttribute()
    {
        return $this->check_date ? Carbon::parse($this->check_date)->format('d-m-Y') : '-';
    }
}
