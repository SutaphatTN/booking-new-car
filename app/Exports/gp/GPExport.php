<?php

namespace App\Exports\gp;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GPExport implements WithMultipleSheets
{
  protected $fromDate;
  protected $toDate;

  public function __construct($fromDate = null, $toDate = null)
  {
    $this->fromDate = $fromDate ?? now()->startOfMonth()->format('Y-m-d');
    $this->toDate   = $toDate   ?? now()->format('Y-m-d');
  }

  public function sheets(): array
  {
    return [
      new GPSummary($this->fromDate, $this->toDate),
      new GPPerCar($this->fromDate, $this->toDate),
    ];
  }
}
