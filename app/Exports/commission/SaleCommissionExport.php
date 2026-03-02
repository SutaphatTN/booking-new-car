<?php

namespace App\Exports\commission;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SaleCommissionExport implements WithMultipleSheets
{
  protected $user;
  protected $fromDate;
  protected $toDate;

  public function __construct($user, $fromDate = null, $toDate = null)
  {
    $this->user = $user;
    $this->fromDate = $fromDate ?? now()->startOfMonth()->format('Y-m-d');
    $this->toDate   = $toDate   ?? now()->format('Y-m-d');
  }

  public function sheets(): array
  {
    $sheets = [];

    if ($this->user->role !== 'sale') {
      $sheets[] = new SaleCommissionSummary(
        $this->user,
        $this->fromDate,
        $this->toDate
      );
    }

    $sheets[] = new SaleCommissionPerCar(
      $this->user,
      $this->fromDate,
      $this->toDate
    );

    return $sheets;
  }
}
