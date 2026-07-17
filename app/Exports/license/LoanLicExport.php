<?php

namespace App\Exports\license;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LoanLicExport implements WithMultipleSheets
{
    // ประวัติยืม-คืนป้ายแดงทั้งหมด แยก sheet ตามแบรนด์เจ้าของป้าย
    public function sheets(): array
    {
        $sheets = [];

        foreach (config('brand.names', []) as $brandId => $brandName) {
            $sheets[] = new LoanLicSheet($brandId, $brandName);
        }

        return $sheets;
    }
}
