<?php

namespace App\Exports\carOrder\Concerns;

use App\Models\CarOrder;
use App\Support\BrandFeature;
use Illuminate\Support\Facades\Auth;

/**
 * รายงานฝั่งข้อมูลรถที่ต้องเห็นภาพรวมทั้ง brand
 *
 * brand ที่มีหลายสาขา (config brand.multi_branch_brands) จะปลด scope สาขาทิ้ง
 * แล้วล็อก brand เองแทน ข้อมูลจึงครบทุกสาขา และชีทมีคอลัมน์ "สาขา" กำกับว่าคันไหนของสาขาไหน
 * brand ที่มีสาขาเดียวปล่อยให้ UserAccessScope ทำงานตามปกติ ไม่ต้องมีคอลัมน์สาขาให้รก
 *
 * ท่าเดียวกับ App\Services\BookingReportQuery::scoped() ของรายงานข้อมูลรถและการจอง
 */
trait PullsAllBranches
{
    protected function scopedCarOrders()
    {
        $brand = (int) (Auth::user()->brand ?? 0);

        if (BrandFeature::hasMultipleBranches($brand)) {
            return CarOrder::withoutGlobalScope('userAccess')->where('brand', $brand);
        }

        return CarOrder::query();
    }
}
