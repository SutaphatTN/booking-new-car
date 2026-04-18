<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BranchSwitchController extends Controller
{
    public function switch(Request $request)
    {
        $request->validate(['branch_id' => 'required|integer|in:1,2,3']);
        session(['branch_switch' => (int) $request->branch_id]);
        return back();
    }

    public function reset()
    {
        session()->forget('branch_switch');
        return back();
    }
}
