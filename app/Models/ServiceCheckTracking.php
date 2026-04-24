<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceCheckTracking extends Model
{
    use SoftDeletes;

    protected $table = 'service_check_trackings';

    protected $fillable = [
        'salecar_id',
        'customer_id',
        'car_order_id',
        'UserInsert',
    ];

    public function salecar()
    {
        return $this->belongsTo(Salecar::class, 'salecar_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function carOrder()
    {
        return $this->belongsTo(CarOrder::class, 'car_order_id');
    }

    public function insertUser()
    {
        return $this->belongsTo(User::class, 'UserInsert');
    }

    public function details()
    {
        return $this->hasMany(ServiceCheckTrackingDetail::class, 'tracking_id');
    }

    public function latestDetail()
    {
        return $this->hasOne(ServiceCheckTrackingDetail::class, 'tracking_id')->latestOfMany();
    }
}
