<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BrandSwitchController extends Controller
{
    public function switch(Request $request)
    {
        $request->validate(['brand_id' => 'required|integer|exists:tb_brand,id']);
        session(['brand_switch' => (int) $request->brand_id]);
        return back();
    }

    public function reset()
    {
        session()->forget('brand_switch');
        return back();
    }
}
