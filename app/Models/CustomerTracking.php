<?php

namespace App\Models;

use App\Models\Traits\TracksUserActions;
use App\Models\Traits\UserAccessScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\TbInteriorColor;
use Carbon\Carbon;

class CustomerTracking extends Model
{
    use SoftDeletes, UserAccessScope, TracksUserActions;

    protected $table = 'customer_trackings';

    protected $casts = [
        'booked_at'    => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected $fillable = [
        'sale_id',
        'customer_id',
        'source_id',
        'place_id',
        'clip_add',
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
        'UserUpdate',
        'UserDelete',
        'BookedBy',
        'booked_at',
        'cancelled_at',
        'CancelledBy',
        'delivery_timeline_scoring',
        'test_drive_scoring',
        'occupation_scoring',
        'revenue_scoring',
        'model_interest_scoring',
        'purchase_type_scoring',
        'engagement_scoring',
        'test_drive_date',
        'test_drive_note',
        'customer_date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id')->withTrashed();
    }

    public function sale()
    {
        return $this->belongsTo(User::class, 'sale_id');
    }

    public function userInsert()
    {
        return $this->belongsTo(User::class, 'UserInsert');
    }

    public function userUpdate()
    {
        return $this->belongsTo(User::class, 'UserUpdate');
    }

    public function userDelete()
    {
        return $this->belongsTo(User::class, 'UserDelete');
    }

    public function bookedBy()
    {
        return $this->belongsTo(User::class, 'BookedBy');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'CancelledBy');
    }

    public function source()
    {
        // withTrashed: แหล่งที่มาที่ถูกลบไปแล้ว ประวัติการติดตามเดิมยังต้องแสดงชื่อได้
        return $this->belongsTo(TbSalecarType::class, 'source_id')->withTrashed();
    }

    public function place()
    {
        return $this->belongsTo(SourcePlace::class, 'place_id');
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

    // entry ล่าสุด (ทุกประเภท) ที่วันที่ <= วันนี้
    public function latestPastDetail()
    {
        return $this->hasOne(CustomerTrackingDetail::class, 'tracking_id')
            ->ofMany(['contact_date' => 'max'], function ($q) {
                $q->whereDate('contact_date', '<=', now()->toDateString());
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

    public function getFormatTestDriveDateAttribute()
	{
		return $this->test_drive_date ? Carbon::parse($this->test_drive_date)->format('d-m-Y') : null;
	}
}
