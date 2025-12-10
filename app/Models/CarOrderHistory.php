<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class CarOrderHistory extends Model
{
    use SoftDeletes;

    protected $table = 'car_order_history';

    protected $fillable = [
        'SaleID',
        'CarOrderID',
        'BookingDate',
        'changed_at',
        'userZone',
    ];

    protected $dates = ['deleted_at'];

    public function saleCar()
    {
        return $this->belongsTo(saleCar::class, 'SaleID', 'id');
    }

    public function carOrder()
    {
        return $this->belongsTo(CarOrder::class, 'CarOrderID', 'id');
    }

    public function getFormatBookingDateAttribute()
	{
		return $this->BookingDate ? Carbon::parse($this->BookingDate)->format('d-m-Y') : null;
	}

    public function getBookingDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }
}
