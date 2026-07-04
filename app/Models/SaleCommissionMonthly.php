<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ค่าคอมเพิ่มเติมระดับ "ต่อเซลล์ ต่อเดือน"
 *  - brand 2 : ค่าคอมวินัย, ค่าขาด/ลา/มาสาย, คอม lead, คอม clip (เป็นตัวเงินทั้งหมด)
 *  - brand 1/3 : วินัยเป็น "ผ่าน/ไม่ผ่าน" (discipline_failed) — ไม่ผ่านหัก 15% จากรวมค่าคอมรถ,
 *                ไม่มี lead/clip, คง ค่าขาด/ลา/มาสาย
 *
 * @property int $id
 * @property int $SaleID
 * @property int $year
 * @property int $month
 * @property float $com_discipline
 * @property float $deduct_absence
 * @property float $com_lead
 * @property float $com_clip
 * @property bool $discipline_failed
 */
class SaleCommissionMonthly extends Model
{
    protected $table = 'sale_commission_monthly';

    /** วินัยไม่ผ่าน → หัก 15% จากรวมค่าคอมรถ (brand 1/3) */
    public const DISCIPLINE_FAIL_RATE = 0.15;

    protected $fillable = [
        'SaleID',
        'year',
        'month',
        'com_discipline',
        'deduct_absence',
        'com_lead',
        'com_clip',
        'discipline_failed',
    ];

    protected $casts = [
        'SaleID' => 'int',
        'year' => 'int',
        'month' => 'int',
        'com_discipline' => 'float',
        'deduct_absence' => 'float',
        'com_lead' => 'float',
        'com_clip' => 'float',
        'discipline_failed' => 'bool',
    ];

    /**
     * ยอดค่าคอมสุทธิ (brand-aware)
     *  - brand 1/3 : (วินัยไม่ผ่าน → base×0.85) − ขาด/ลา/มาสาย
     *  - brand อื่น (2) : base + วินัย + lead + clip − ขาด/ลา/มาสาย
     */
    public function computeNet(float $baseCommission, int $brand): float
    {
        if (in_array($brand, [1, 3], true)) {
            $base = $this->discipline_failed
                ? $baseCommission * (1 - self::DISCIPLINE_FAIL_RATE)
                : $baseCommission;

            return $base - (float) ($this->deduct_absence ?? 0);
        }

        return $baseCommission
            + (float) ($this->com_discipline ?? 0)
            + (float) ($this->com_lead ?? 0)
            + (float) ($this->com_clip ?? 0)
            - (float) ($this->deduct_absence ?? 0);
    }

    public function saleUser()
    {
        return $this->belongsTo(User::class, 'SaleID', 'id');
    }
}
