<?php

namespace App\Exports;

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

        //สต็อกรวม
        $sheets[] = new BookingSummarySheet($this->request);

        //ข้อมูลรถรุ่นหลัก
        $models = TbCarmodel::orderBy('Name_TH')->get();

        foreach ($models as $model) {
            $sheets[] = new BookingByModelSheet($model);
        }

        return $sheets;
    }
}
