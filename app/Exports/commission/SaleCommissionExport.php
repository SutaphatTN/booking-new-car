<?php

namespace App\Exports\Commission;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SaleCommissionExport implements WithMultipleSheets
{
  protected $user;
  protected $month;
  protected $year;

  public function __construct($user, $month, $year)
  {
    $this->user = $user;
    $this->month = $month;
    $this->year  = $year;
  }

  public function sheets(): array
  {
    return [
      new SaleCommissionSummary($this->user, $this->month, $this->year),
      new SaleCommissionPerCar($this->user, $this->month, $this->year),
    ];
  }
}
