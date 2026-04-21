<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerTracking extends Model
{
    use SoftDeletes;

    protected $table = 'customer_trackings';

    protected $fillable = [
        'sale_id',
        'customer_id',
        'source_id',
        'model_id',
        'sub_model_id',
        'year',
        'color_id',
        'userZone',
        'brand',
        'branch',
        'UserInsert',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function sale()
    {
        return $this->belongsTo(User::class, 'sale_id');
    }

    public function source()
    {
        return $this->belongsTo(TbSalecarType::class, 'source_id');
    }

    public function model()
    {
        return $this->belongsTo(TbCarmodel::class, 'model_id');
    }

    public function subModel()
    {
        return $this->belongsTo(TbSubcarmodel::class, 'sub_model_id');
    }

    public function details()
    {
        return $this->hasMany(CustomerTrackingDetail::class, 'tracking_id');
    }

    public function latestDetail()
    {
        return $this->hasOne(CustomerTrackingDetail::class, 'tracking_id')->latestOfMany();
    }

    public function wuColor()
    {
        return $this->belongsTo(TbColor::class, 'color_id');
    }
}
