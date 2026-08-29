<?php

namespace App\Exports\carOrder;

use App\Exports\carOrder\Concerns\PullsAllBranches;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * รายงาน "ข้อมูลรถที่สั่งในระบบ" — เลือกช่วงวันที่สั่ง (order_date = วันที่สั่งรถในระบบ New Car)
 *
 *   ชีทแรก  = สรุปจำนวนคันแยกตามรุ่นหลัก/รุ่นย่อย
 *   ชีทถัดไป = 1 ชีทต่อ 1 รุ่นหลักที่มีรถสั่งในช่วงนั้น
 *
 * brand-scope อัตโนมัติผ่าน UserAccessScope ของ CarOrder
 * โหลดข้อมูลครั้งเดียวแล้วแบ่งชีทใน PHP — DB อยู่ remote การยิง query รายรุ่นจะช้ามาก
 */
class CarOrderByOrderDateExport implements WithMultipleSheets
{
    use PullsAllBranches;

    protected $fromDate;
    protected $toDate;

    /** maatwebsite เรียก sheets() 2 รอบต่อการ export 1 ครั้ง → จำผลไว้ ไม่งั้นโหลดข้อมูลซ้ำฟรี ๆ */
    protected $sheets;

    public function __construct($fromDate = null, $toDate = null)
    {
        $this->fromDate = $fromDate ?: now()->startOfMonth()->format('Y-m-d');
        $this->toDate   = $toDate   ?: now()->format('Y-m-d');
    }

    public function sheets(): array
    {
        if ($this->sheets !== null) {
            return $this->sheets;
        }

        $rows = $this->rows();

        $sheets = [new OrderDateSummarySheet($rows, $this->fromDate, $this->toDate)];

        // แยกชีทตามรุ่นหลัก เรียงตามชื่อรุ่น (รถที่ยังไม่ได้เลือกรุ่นไปรวมชีทท้ายสุด)
        $used = [];
        $groups = $rows->groupBy(fn($r) => $r->model_id ?? 0)
            ->sortBy(fn($g) => $g->first()->model->Name_TH ?? 'zzz');

        foreach ($groups as $group) {
            $sheets[] = new OrderDateModelSheet(
                $group->values(),
                $this->sheetTitle($group->first()->model->initials ?? 'ไม่ระบุรุ่น', $used)
            );
        }

        return $this->sheets = $sheets;
    }

    private function rows(): Collection
    {
        return $this->scopedCarOrders()
            ->with(['model', 'subModel', 'purchaseType', 'orderStatus', 'gwmColor', 'interiorColor', 'dealerProvince', 'branchInfo'])
            ->whereNotNull('order_date')
            ->whereBetween('order_date', [$this->fromDate, $this->toDate])
            ->orderBy('order_date')
            ->orderBy('model_id')
            ->orderBy('subModel_id')
            ->get();
    }

    /**
     * ชื่อชีท Excel ห้ามเกิน 31 ตัว ห้ามมี : \ / ? * [ ] และห้ามซ้ำกัน
     * (initials ซ้ำกันได้จริง เช่น POER มี 2 รุ่น → เติมเลขต่อท้าย)
     */
    private function sheetTitle(string $name, array &$used): string
    {
        $title = trim(preg_replace('/[:\\\\\/\?\*\[\]]/u', '-', $name));
        $title = mb_substr($title !== '' ? $title : 'ไม่ระบุรุ่น', 0, 31);

        $base = $title;
        $i    = 2;
        while (isset($used[$title])) {
            $suffix = ' (' . $i++ . ')';
            $title  = mb_substr($base, 0, 31 - mb_strlen($suffix)) . $suffix;
        }

        $used[$title] = true;

        return $title;
    }
}
