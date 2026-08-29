<?php

namespace App\Exports\booking;

use App\Models\TbCarmodel;
use App\Services\BookingReportQuery;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BookingExport implements WithMultipleSheets
{
    protected $request;

    /**
     * maatwebsite/excel เรียก sheets() 2 รอบต่อการ export 1 ครั้ง
     * (Writer::export() และ WriterFactory::includesCharts()) → ถ้าไม่จำผลไว้
     * จะโหลดข้อมูลทั้งรายงานซ้ำอีกรอบฟรี ๆ
     */
    protected $sheets;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function sheets(): array
    {
        if ($this->sheets !== null) {
            return $this->sheets;
        }

        $sheets = [];

        // โหลดข้อมูลทั้งรายงานครั้งเดียว แล้วส่งต่อให้ทุก sheet ไปกรองใน PHP
        // (เดิมแต่ละ sheet ยิง query เอง + hasData() ยิงซ้ำอีกรอบ → ~219 query/ครั้ง
        //  ซึ่งช้ามากเพราะ DB อยู่ remote RTT ~50 ms ต่อ query)
        $cars    = BookingReportQuery::allCars();
        $orphans = BookingReportQuery::allOrphanSales();

        $stockCars = BookingReportQuery::stockOnly($cars);

        //Test Drive
        $sheets[] = new TestDriveSheet(BookingReportQuery::testDriveOnly($cars));

        //สต็อกรวม
        $sheets[] = new BookingSummarySheet($this->request, $stockCars);

        //ข้อมูลรถรุ่นหลัก — ข้ามรุ่นที่ไม่มีข้อมูล จะได้ไม่มี sheet เปล่าเยอะ
        $addModelSheet = function ($model, $filter = null) use (&$sheets, $stockCars, $orphans) {

            $sheet = new BookingByModelSheet($model, $filter, $stockCars, $orphans);

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
        $sheets[] = new AgingReportSheet(BookingReportQuery::withStockDate($stockCars));

        return $this->sheets = $sheets;
    }
}
