<?php

namespace App\Exports\insurance;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InsuranceExport implements WithMultipleSheets
{
  protected $fromDate;

  public function __construct($fromDate = null)
  {
    $this->fromDate = $fromDate ?? now()->startOfMonth()->format('Y-m');
  }

  public function sheets(): array
  {
    // ดึงทีเดียวทุก brand แยกตาม sheet (1 = MITSUBISHI, 2 = GWM, 3 = Wuling, 4 = Lepas)
    return [
      new InsurancePerBrandSheet(1, $this->fromDate),
      new InsurancePerBrandSheet(2, $this->fromDate),
      new InsurancePerBrandSheet(3, $this->fromDate),
      new InsurancePerBrandSheet(4, $this->fromDate),
    ];
  }
}
