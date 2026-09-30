<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ยอดปิด FP รายรอบ — ใช้เฉพาะ brand ที่แบ่งปิดได้ (ดู BrandFeature::hasFpSplitClose)
 * คันที่ปิดครั้งเดียวไม่มีแถวในตารางนี้ ยังใช้ car_order.fp_close_date ตามเดิม
 * ทุกยอดใช้ Billing date เดียวกัน (car_order.fp_date) ผลรวม amount = Net Amount ของคันนั้น
 */
class CarOrderFpClosing extends Model
{
    use SoftDeletes;

    protected $table = 'car_order_fp_closings';

    protected $fillable = [
        'car_order_id',
        'seq',
        'amount',
        'close_date',
        'UserInsert',
        'UserUpdate',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function carOrder()
    {
        return $this->belongsTo(CarOrder::class, 'car_order_id');
    }
}
