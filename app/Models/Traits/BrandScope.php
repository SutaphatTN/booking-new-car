<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Auth;

trait BrandScope
{
    protected static function bootBrandScope()
    {
        static::addGlobalScope('brandAccess', function ($query) {
            if (!Auth::check()) return;

            $user = Auth::user();
            $model = $query->getModel();
            $table = $model->getTable();

            if (!$user->brand) return;

            // model ที่แชร์ทรัพยากรตามกลุ่ม brand จะมองเห็นทุก brand ในกลุ่มเดียวกัน
            // (เช่น ป้ายแดง / stock film ของ brand 1 กับ 3) ส่วน model อื่นกรอง brand ตรงตัว
            if (!empty($model->sharedByBrandGroup)) {
                $group = config("brand.group_of.{$user->brand}");
                $brands = $group
                    ? config("brand.brands_in.{$group}", [$user->brand])
                    : [$user->brand];

                $query->whereIn($table . '.brand', $brands);
            } else {
                $query->where($table . '.brand', $user->brand);
            }
        });
    }
}