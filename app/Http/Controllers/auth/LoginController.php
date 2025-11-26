<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            return redirect()->intended(route('home'));
        }

        return back()->with('error', 'Username หรือ Password ไม่ถูกต้อง');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login.index');
    }
}
