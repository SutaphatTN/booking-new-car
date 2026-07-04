<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * เป้ายอดขายต่อเดือน (ต่อ brand) — ใช้ตัดสิน "บรรลุเป้า 120%" ของคอมตัวรถรายคัน
 *
 * @property int $brand
 * @property int $year
 * @property int $month
 * @property int $target
 */
class MonthlySaleTarget extends Model
{
    protected $table = 'monthly_sale_targets';

    protected $fillable = ['brand', 'year', 'month', 'target'];

    protected $casts = [
        'brand'  => 'int',
        'year'   => 'int',
        'month'  => 'int',
        'target' => 'int',
    ];
}
