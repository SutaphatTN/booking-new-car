<?php

namespace App\Exports\commission;

use App\Models\User;
use App\Services\StaffCommissionQuery;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * รายงานค่าคอมฝ่ายสนับสนุน (ผู้จัดการ / แอดมิน / ทะเบียน / การตลาด) ของ 1 เดือน
 *
 *  ชีท 1 สรุปรายคน            — ยอดเดียวกับตารางในหน้าจอ
 *  ชีท 2 รายละเอียดตามเกณฑ์   — กางทีละก้อน/ทีละรายการที่กรอกเอง
 *
 * $onlyIds : จำกัดเฉพาะบางคน (คนที่ไม่ใช่ admin/md/gm ได้เฉพาะของตัวเอง — ดู StaffCommissionController)
 * sheets() ถูกเรียกซ้ำ 2 รอบโดย maatwebsite → memoize ข้อมูลไว้ ไม่งั้นคิวรี่ซ้ำฟรี ๆ
 */
class StaffCommissionExport implements WithMultipleSheets
{
    private ?array $memo = null;

    public function __construct(
        protected int $year,
        protected int $month,
        protected ?array $onlyIds = null,
    ) {}

    /** [['name' => ชื่อ, 'data' => ผลคิดค่าคอม], ...] เรียงจากยอดมากไปน้อย */
    private function rows(): array
    {
        if ($this->memo !== null) {
            return $this->memo;
        }

        $result = StaffCommissionQuery::forMonth($this->year, $this->month, $this->onlyIds);
        $users  = User::withTrashed()->whereIn('id', array_keys($result))->get()->keyBy('id');

        $rows = [];
        foreach ($result as $uid => $data) {
            $rows[] = [
                'name' => $users->get($uid)->name ?? ('(ไม่พบผู้ใช้ #' . $uid . ')'),
                'data' => $data,
            ];
        }

        usort($rows, fn($a, $b) => $b['data']['total'] <=> $a['data']['total']);

        return $this->memo = $rows;
    }

    public function sheets(): array
    {
        $rows = $this->rows();

        return [
            new StaffCommissionSummary($rows),
            new StaffCommissionDetail($rows),
        ];
    }
}
