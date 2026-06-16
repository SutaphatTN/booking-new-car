<?php

namespace App\Exports\customerTracking;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CustomerTrackingOverdueReport implements WithMultipleSheets
{
    public function __construct(protected string $month) {}

    public function sheets(): array
    {
        return [
            new CustomerTrackingOverdueExport($this->month),
            new CustomerTrackingNextContactSheet($this->month),
        ];
    }
}
