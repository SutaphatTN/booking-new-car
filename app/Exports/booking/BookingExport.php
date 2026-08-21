<?php

namespace App\Exports\booking;

use App\Models\TbCarmodel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BookingExport implements WithMultipleSheets
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function sheets(): array
    {
        $sheets = [];

        //Test Drive
        $sheets[] = new TestDriveSheet();

        //สต็อกรวม
        $sheets[] = new BookingSummarySheet($this->request);

        //ข้อมูลรถรุ่นหลัก — ข้ามรุ่นที่ไม่มีข้อมูล จะได้ไม่มี sheet เปล่าเยอะ
        $addModelSheet = function ($model, $filter = null) use (&$sheets) {

            $sheet = new BookingByModelSheet($model, $filter);

            if ($sheet->hasData()) {
                $sheets[] = $sheet;
            }
        };

        $models = TbCarmodel::orderBy('Name_TH')->get();
        foreach ($models as $model) {

            // ถ้าเป็น model 3
            if ($model->id == 3) {

                // sheet สำหรับ submodel 5-8
                $addModelSheet($model, 'exclude9');

                // sheet สำหรับ submodel 9
                $addModelSheet($model, 'only9');

            } elseif ($model->id == 9) {

                // sheet สำหรับ submodel 40, 41
                $addModelSheet($model, 'sub_4041');

                // sheet สำหรับ submodel 53, 62
                $addModelSheet($model, 'sub_5362');

            } else {

                $addModelSheet($model);
            }
        }

        // Aging Report
        $sheets[] = new AgingReportSheet();

        return $sheets;
    }
}
