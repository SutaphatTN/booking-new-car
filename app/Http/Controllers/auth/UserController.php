<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $user = User::all();
        return view('auth.user.view', compact('user'));
    }

    public function listUser()
    {
        $user = User::all();

        $data = $user->map(function ($u, $index) {
            return [
                'No' => $index + 1,
                'name' => $u->name,
                'email' => $u->email,
                'username' => $u->username,
                'role' => $u->role,
                'Action' => view('auth.user.button', compact('u'))->render()
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function viewMore($id)
    {
        $user = User::findOrFail($id);
        return view('auth.user.view-more', compact('user'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('auth.user.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $data = $request->except(['_token', '_method']);

            if ($request->filled('password')) {
                $data['password_plain'] = $request->password;
                $data['password'] = bcrypt($request->password);
            }

            $user->update($data);

            return response()->json([
                'success' => true,
                'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
        }
    }
}