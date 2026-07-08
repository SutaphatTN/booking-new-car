<?php

namespace App\Exports\customerTracking;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CustomerTrackingOverdueReport implements WithMultipleSheets
{
    // $saleId != null → กรองเฉพาะของเซลล์คนนั้น (รายงานฝั่งเซลล์) ; null = เห็นทั้งหมด (ผจก.)
    public function __construct(protected string $month, protected ?int $saleId = null) {}

    public function sheets(): array
    {
        return [
            new CustomerTrackingOverdueExport($this->month, $this->saleId),
            new CustomerTrackingNextContactSheet($this->month, $this->saleId),
        ];
    }
}
