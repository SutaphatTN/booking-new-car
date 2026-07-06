<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BrandSwitchController extends Controller
{
    public function switch(Request $request)
    {
        $request->validate(['brand_id' => 'required|integer|exists:tb_brand,id']);
        $target = (int) $request->brand_id;

        // เก็บ session เฉพาะ brand ที่ user มีสิทธิ์สลับ (สอดคล้องกับ BrandSwitcher middleware)
        if (in_array($target, $request->user()->switchableBrandIds(), true)) {
            session(['brand_switch' => $target]);
        }
        return back();
    }

    public function reset()
    {
        session()->forget('brand_switch');
        return back();
    }
}
