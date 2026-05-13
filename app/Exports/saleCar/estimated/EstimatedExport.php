<?php

namespace App\Exports\saleCar\estimated;

use App\Services\SaleBookingQuery;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EstimatedExport implements WithMultipleSheets
{
    protected $fromDate;

    public function __construct($fromDate = null)
    {
        $this->fromDate = $fromDate;
    }

    public function sheets(): array
    {
        $date = Carbon::createFromFormat('Y-m', $this->fromDate)->startOfMonth();

        $month = $date->month;
        $year  = $date->year;

        // ดึง branch ที่มีข้อมูลจริง
        $branches = SaleBookingQuery::base()
            ->whereMonth('DeliveryEstimateDate', $month)
            ->whereYear('DeliveryEstimateDate', $year)
            ->whereNotIn('con_status', [7, 8, 9])
            ->select('branch')
            ->distinct()
            ->pluck('branch');

        $sheets = [];

        if ($branches->isEmpty()) {

            // sheet เปล่า
            $sheets[] = new SaleCarEstimatedExport(
                $this->fromDate,
                null
            );
        } else {

            foreach ($branches as $branchId) {
                $sheets[] = new SaleCarEstimatedExport(
                    $this->fromDate,
                    $branchId
                );
            }
        }

        return $sheets;
    }
}
