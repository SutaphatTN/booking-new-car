<?php

namespace App\Http\Controllers\insurance;

use App\Http\Controllers\Controller;
use App\Models\Insurance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class InsuranceController extends Controller
{
    /** เพิ่ม/แก้/ลบ ได้เฉพาะ admin — role อื่นดูได้อย่างเดียว */
    private function assertAdmin(): void
    {
        abort_unless(Auth::user()->role === 'admin', 403);
    }

    public function index()
    {
        return view('insurance.view');
    }

    public function list()
    {
        $isAdmin = Auth::user()->role === 'admin';

        $data = Insurance::orderBy('name')->get()->map(function ($ins, $index) use ($isAdmin) {
            return [
                'No'     => $index + 1,
                'name'   => $ins->name,
                'Action' => $isAdmin
                    ? view('insurance.button', ['ins' => $ins])->render()
                    : '<span class="text-muted small">ดูอย่างเดียว</span>',
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function create()
    {
        $this->assertAdmin();
        return view('insurance.input');
    }

    public function store(Request $request)
    {
        $this->assertAdmin();

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            Insurance::create($validated);

            return response()->json(['success' => true, 'message' => 'เพิ่มข้อมูลเรียบร้อยแล้ว']);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    public function edit($id)
    {
        $this->assertAdmin();
        $insurance = Insurance::findOrFail($id);
        return view('insurance.edit', compact('insurance'));
    }

    public function update(Request $request, $id)
    {
        $this->assertAdmin();

        try {
            $insurance = Insurance::findOrFail($id);
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $insurance->update($validated);

            return response()->json(['success' => true, 'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว']);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    public function destroy($id)
    {
        $this->assertAdmin();

        try {
            // soft delete — เก็บแถวไว้เพื่อให้ PO เดิมที่อ้างอิงยังแสดงชื่อได้ (relation ใช้ withTrashed ฝั่ง PO)
            Insurance::findOrFail($id)->delete();

            return response()->json(['success' => true, 'message' => 'ลบข้อมูลเรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }
}
