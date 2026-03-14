<?php

namespace App\Exports\gwm;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GwmExport implements WithMultipleSheets
{
    protected $fromDate;

    public function __construct($fromDate = null)
    {
        $this->fromDate = $fromDate ?? now()->startOfMonth()->format('Y-m');
    }

    public function sheets(): array
  {
    return [
      new StockGWMSheet($this->fromDate),
      new BookingGWMSheet($this->fromDate),
    ];
  }
}
