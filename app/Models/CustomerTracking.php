<?php

namespace App\Models;

use App\Models\Traits\UserAccessScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\TbInteriorColor;

class CustomerTracking extends Model
{
    use SoftDeletes, UserAccessScope;

    protected $table = 'customer_trackings';

    protected $fillable = [
        'sale_id',
        'customer_id',
        'source_id',
        'model_id',
        'sub_model_id',
        'year',
        'pricelist_color',
        'option',
        'color_id',
        'interior_color_id',
        'color_text',
        'userZone',
        'brand',
        'branch',
        'UserInsert',
        'cancelled_at',
        'CancelledBy',
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

    // entry ผู้จัดการที่ใกล้ที่สุดในอนาคต (> วันนี้)
    public function nextManagerDetail()
    {
        return $this->hasOne(CustomerTrackingDetail::class, 'tracking_id')
            ->ofMany(['contact_date' => 'min'], function ($q) {
                $q->where('entry_type', 'manager')
                  ->where('contact_date', '>', now()->toDateString());
            });
    }

    public function latestManagerDetail()
    {
        return $this->hasOne(CustomerTrackingDetail::class, 'tracking_id')
            ->ofMany(['contact_date' => 'max'], function ($q) {
                $q->where('entry_type', 'manager');
            });
    }

    public function wuColor()
    {
        return $this->belongsTo(TbColor::class, 'color_id');
    }

    public function interiorColor()
    {
        return $this->belongsTo(TbInteriorColor::class, 'interior_color_id');
    }
}
