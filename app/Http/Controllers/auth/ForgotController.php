<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ForgotController extends Controller
{
    public function index()
    {
        return view('auth.forgot');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return back()->withErrors(['username' => 'ไม่พบ Username นี้']);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'password_plain' => $request->password,
        ]);
        Auth::login($user);

        return redirect()->route('customer.index')->with('success', 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว');
    }
}
