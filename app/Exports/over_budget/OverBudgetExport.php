<?php

namespace App\Exports\over_budget;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * รายงาน "เกินงบ" — รายการจองที่ขอเกินงบ (ทั้งไม่ทะลุเพดาน + ทะลุเพดาน) กรองตามเดือนที่ขอ
 *   - admin/md/account/gm เห็นทุก brand (แยก sheet ต่อ brand)
 *   - manager/audit เห็นตาม brand ของตน (1 → [1,3]) — $brands ส่งมาจาก controller
 */
class OverBudgetExport implements WithMultipleSheets
{
  protected string $fromDate;
  protected array $brands;

  public function __construct($fromDate = null, array $brands = [1, 2, 3, 4])
  {
    $this->fromDate = $fromDate ?? now()->format('Y-m');
    $this->brands   = $brands;
  }

  public function sheets(): array
  {
    $sheets = [];
    foreach ($this->brands as $brand) {
      $sheets[] = new OverBudgetPerBrandSheet((int) $brand, $this->fromDate);
    }

    return $sheets;
  }
}
