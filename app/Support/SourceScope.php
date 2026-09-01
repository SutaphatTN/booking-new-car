<?php

namespace App\Support;

use App\Models\TbSalecarType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * สิทธิ์เลือก "แหล่งที่มา" — กติกาชุดเดียวใช้ทั้งหน้าติดตามลูกค้าและหน้าใบจอง
 *
 * เดิมสองหน้านี้คุมกันคนละแบบ (หน้าติดตามอ่านจาก config ส่วนหน้าใบจองฮาร์ดโค้ด id 12,16
 * ไว้ใน blade) เซลล์เลยคีย์ใบจองเป็น Online บริษัท ตรง ๆ ได้ทั้งที่หน้าติดตามห้ามไว้
 * ค่าจริงอยู่ใน config/source.php (sale_hidden_main / sale_main_exceptions)
 */
class SourceScope
{
    /**
     * แหล่งที่มาหลักที่ user คนนี้เลือกได้ (key => label)
     *
     * ตัดกลุ่มที่ซ่อนเฉพาะหน้าติดตาม (tracking_hidden_main) ออกก่อน แล้วตัดกลุ่มที่เซลล์
     * คีย์เองไม่ได้ (sale_hidden_main) ออกอีกชั้นเฉพาะ role ใน sale_restricted_roles
     * ยกเว้น brand/branch ที่ระบุไว้ใน sale_main_exceptions
     */
    public static function allowedMains(): array
    {
        $user  = Auth::user();
        $mains = collect(config('source.main', []))->except(config('source.tracking_hidden_main', []));

        if (!$user || !in_array($user->role, config('source.sale_restricted_roles', []), true)) {
            return $mains->all();
        }

        $exceptions = config('source.sale_main_exceptions', []);

        $hidden = collect(config('source.sale_hidden_main', []))
            ->reject(fn ($main) => in_array(
                (int) $user->branch,
                $exceptions[$main][(int) $user->brand] ?? [],
                true
            ))
            ->all();

        return $mains->except($hidden)->all();
    }

    /** key ของแหล่งที่มาหลักที่เลือกได้ — ใช้กับ whereIn / Rule::in */
    public static function allowedMainKeys(): array
    {
        return array_keys(self::allowedMains());
    }

    /** แหล่งที่มาย่อย id นี้อยู่ในกลุ่มที่ user คนนี้เลือกได้ไหม — ใช้ตรวจซ้ำตอนบันทึก */
    public static function allowsSub($sourceId): bool
    {
        return TbSalecarType::whereKey($sourceId)
            ->whereIn('main_source', self::allowedMainKeys())
            ->exists();
    }

    /**
     * แหล่งที่มาย่อยที่เลือกได้
     *
     * $keepId = id ที่ต้องคงไว้ในลิสต์เสมอแม้จะอยู่ในกลุ่มที่ถูกซ่อน/ถูกลบไปแล้ว
     * (ค่าเดิมของใบที่กำลังเปิดอยู่ — ไม่งั้นเปิดหน้ามาค่าจะหาย)
     */
    public static function allowedSubs($keepId = null): Collection
    {
        $keys = self::allowedMainKeys();

        return TbSalecarType::withTrashed()
            ->where(function ($q) use ($keys, $keepId) {
                // ตัวที่ยังใช้งานอยู่ในกลุ่มที่เลือกได้ + ค่าเดิมของใบนี้ (จะถูกลบไปแล้วก็ต้องคงไว้)
                $q->where(fn ($w) => $w->whereIn('main_source', $keys)->whereNull('deleted_at'));

                if ($keepId) {
                    $q->orWhere('id', $keepId);
                }
            })
            ->get();
    }
}
