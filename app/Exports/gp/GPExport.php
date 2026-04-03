<?php

namespace App\Exports\gp;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GPExport implements WithMultipleSheets
{
  protected $fromDate;

  public function __construct($fromDate = null)
  {
    $this->fromDate = $fromDate ?? now()->startOfMonth()->format('Y-m');
  }

  public function sheets(): array
  {
    return [
      new GPSummary($this->fromDate),
      new GPPerCar($this->fromDate),
    ];
  }
}
