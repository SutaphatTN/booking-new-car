<?php

namespace App\Exports\gp;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GPExport implements WithMultipleSheets
{
  protected $month;
  protected $year;

  public function __construct($month = null, $year = null)
  {
    $this->month = $month ?? now()->month;
    $this->year  = $year  ?? now()->year;
  }

  public function sheets(): array
  {
    return [
      new GPSummary($this->month, $this->year),
      new GPPerCar($this->month, $this->year),
    ];
  }
}
