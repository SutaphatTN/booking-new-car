<?php

namespace App\Http\Controllers\car_order;

use App\Exports\carOrder\WsTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\CarOrderWsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class WsImportController extends Controller
{
    // หน้า import WS (การตั้งค่า -> ข้อมูลรถ -> นำเข้า WS)
    public function index()
    {
        return view('car-order.import-ws');
    }

    // ดาวน์โหลดไฟล์ต้นฉบับ (หัวคอลัมน์ vin_number, WS)
    public function template()
    {
        return Excel::download(new WsTemplateExport, 'template_import_ws.xlsx');
    }

    // อัปโหลดไฟล์ -> match vin_number -> update WS ในตาราง car_order
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx',
        ], [
            'file.required' => 'กรุณาเลือกไฟล์',
            'file.file'     => 'ไฟล์ไม่ถูกต้อง',
            'file.mimes'    => 'รองรับเฉพาะไฟล์ .xlsx เท่านั้น',
        ]);

        $import = new CarOrderWsImport();

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'อ่านไฟล์ไม่สำเร็จ กรุณาตรวจสอบว่าใช้ไฟล์ต้นฉบับและกรอกข้อมูลถูกต้อง',
            ], 422);
        }

        return response()->json([
            'success'  => true,
            'updated'  => $import->updated,
            'skipped'  => $import->skipped,
            'notFound' => $import->notFound,
            'message'  => "อัปเดต WS สำเร็จ {$import->updated} รายการ",
        ]);
    }
}
