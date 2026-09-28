<?php

namespace App\Exports\customerTracking;

use App\Models\CustomerTracking;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * รายงานทดลองขับ (Test Drive) รายเดือน — กรองจาก test_drive_date
 *  - sheet แรก = สรุปจำนวนต่อเซลล์ ; จากนั้น 1 brand = 1 sheet (รายชื่อลูกค้าที่ทดลองขับ)
 *  - brand ที่ได้ = brand ที่ user สลับไปได้ (User::switchableBrandIds) โหลดครั้งเดียวได้ครบ
 *  - แต่ละ brand ใช้สิทธิ์เหมือนตอนสลับไป brand นั้นเอง (UserAccessScope/SaleTeamScope/BrandScope
 *    อ่าน Auth::user()->brand ทั้งหมด) จึงสลับ brand ชั่วคราวระหว่างดึง แล้วคืนค่าเดิมเสมอ
 *  - gm/md เห็นทุกสาขาทุกทีม (ปลด userAccess/saleTeam) — มีคอลัมน์สาขาแยกให้ เพราะ GWM แยกสาขาจริง
 *  - sale เห็นเฉพาะของตัวเอง, lead_sale เห็นตัวเอง + ทีม (เหมือนหน้ารายการติดตาม) role อื่นเห็นทุกคน
 */
class CustomerTrackingTestDriveReport implements WithMultipleSheets
{
    /** maatwebsite เรียก sheets() 2 รอบ — จำผลไว้ไม่ให้ query ซ้ำ */
    private ?array $sheets = null;

    public function __construct(protected string $month) {}

    public function sheets(): array
    {
        if ($this->sheets !== null) {
            return $this->sheets;
        }

        $brands = collect(Auth::user()->switchableBrandIds())->sort()->values();

        $byBrand = [];
        foreach ($brands as $brand) {
            $byBrand[$brand] = $this->trackingsForBrand($brand);
        }

        $sheets = [new CustomerTrackingTestDriveSummarySheet($this->month, $byBrand)];
        foreach ($byBrand as $brand => $trackings) {
            $sheets[] = new CustomerTrackingTestDriveSheet($this->month, $brand, $trackings);
        }

        return $this->sheets = $sheets;
    }

    private function trackingsForBrand(int $brand): Collection
    {
        $user     = Auth::user();
        $original = $user->brand;
        $start    = Carbon::parse($this->month . '-01')->startOfMonth();
        $end      = (clone $start)->endOfMonth();

        $user->brand = $brand;
        CustomerTracking::forgetSaleTeamIds();

        try {
            // with ต้องโหลดตอน brand ยังสลับอยู่ — model/subModel ติด BrandScope
            return CustomerTracking::with(['customer.prefix', 'sale', 'source', 'model', 'subModel', 'branchInfo'])
                // gm/md เห็นทุกสาขา/ทุกทีมของทุก brand (มติ: GWM แยกสาขาแต่ต้องเห็นครบในไฟล์เดียว)
                ->when($this->seesAllBranches(), fn($q) => $q->withoutGlobalScopes(['userAccess', 'saleTeam']))
                ->where('brand', $brand)
                ->whereBetween('test_drive_date', [$start->format('Y-m-d 00:00:00'), $end->format('Y-m-d 23:59:59')])
                ->when($this->visibleSaleIds(), fn($q, $ids) => $q->whereIn('sale_id', $ids))
                ->orderBy('test_drive_date')
                ->orderBy('id')
                ->get();
        } finally {
            $user->brand = $original;
            CustomerTracking::forgetSaleTeamIds();
        }
    }

    private function seesAllBranches(): bool
    {
        return in_array(Auth::user()->role, ['gm', 'md'], true);
    }

    /** null = ไม่จำกัดเซลล์ ; ใช้กติกาเดียวกับ CustomerTrackingController::list */
    private function visibleSaleIds(): ?array
    {
        $user = Auth::user();

        return match ($user->role) {
            'sale'      => [$user->id],
            'lead_sale' => array_merge([$user->id], [9, 10, 11]),
            default     => null,
        };
    }
}
