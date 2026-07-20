<?php

namespace App\Exports\saleCar\estimated;

use App\Services\SaleBookingQuery;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EstimatedExport implements WithMultipleSheets
{
    protected $fromDate;
    /** 'estimate' = ข้อมูลประมาณการ (DeliveryEstimateDate) | 'sale' = ประมาณการเซลล์ (DeliveryInCKDate) */
    protected $mode;

    public function __construct($fromDate = null, $mode = 'estimate')
    {
        $this->fromDate = $fromDate;
        $this->mode     = $mode;
    }

    public function sheets(): array
    {
        $date = Carbon::createFromFormat('Y-m', $this->fromDate)->startOfMonth();

        $month = $date->month;
        $year  = $date->year;

        $dateCol = SaleBookingQuery::dateColumnFor($this->mode);

        // ดึง branch ที่มีข้อมูลจริง
        $branches = SaleBookingQuery::forReport($this->mode)
            ->whereMonth($dateCol, $month)
            ->whereYear($dateCol, $year)
            ->whereNotIn('con_status', [7, 8, 9])
            ->select('branch')
            ->distinct()
            ->pluck('branch');

        $sheets = [];

        if ($branches->isEmpty()) {

            // sheet เปล่า
            $sheets[] = new SaleCarEstimatedExport($this->fromDate, null, $this->mode);
            $sheets[] = new SaleCarEstimatedSummaryExport($this->fromDate, null, $this->mode);
        } else {

            foreach ($branches as $branchId) {
                $sheets[] = new SaleCarEstimatedExport($this->fromDate, $branchId, $this->mode);
                $sheets[] = new SaleCarEstimatedSummaryExport($this->fromDate, $branchId, $this->mode);
            }
        }

        return $sheets;
    }
}
