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

        //ข้อมูลรถรุ่นหลัก
        $models = TbCarmodel::orderBy('Name_TH')->get();
        foreach ($models as $model) {

            // ถ้าเป็น model 3
            if ($model->id == 3) {

                // sheet สำหรับ submodel 5-8
                $sheets[] = new BookingByModelSheet($model, 'exclude9');

                // sheet สำหรับ submodel 9
                $sheets[] = new BookingByModelSheet($model, 'only9');
            } else {

                $sheets[] = new BookingByModelSheet($model);
            }
        }

        // Aging Report
        $sheets[] = new AgingReportSheet();

        return $sheets;
    }
}
