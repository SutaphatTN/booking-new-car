<?php

namespace App\Exports\lead_online;

use App\Models\TbSalecarType;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * รายงาน "จัดสรร Lead Online"
 * แยก sheet ตาม "brand × สาขา" + sheet Master_Settings (ค่า/เป้าที่แก้ได้ในไฟล์ Excel เอง ต่อเดือน)
 *   - brand 1,3 = รายเดือน / brand 2,4 = รายไตรมาส (ไตรมาสปฏิทินที่ครอบเดือนที่เลือก)
 *   - admin/gm/md เห็นทุก brand ทุกสาขา, audit/manager เห็นเฉพาะสาขาตนในขอบเขต brand ของตน
 *     (units ถูกคำนวณมาจาก controller แล้ว)
 *   - แต่ละสาขาตั้งเป้า (target) ต่างกันได้ (บล็อกแยกใน Master_Settings)
 *   - นับเฉพาะแหล่งที่มาที่เป็น Online (ยกเว้น id 7,20)
 *
 * unit = ['brand' => int, 'branch' => int, 'branchName' => string]
 */
class LeadOnlineAllocationExport implements WithMultipleSheets
{
  protected string $fromDate;

  /** @var array<int,array{brand:int,branch:int,branchName:string}> */
  protected array $units;

  public function __construct($fromDate = null, array $units = [])
  {
    $this->fromDate = $fromDate ?? now()->startOfMonth()->format('Y-m');
    $this->units    = array_values($units);
  }

  public function sheets(): array
  {
    // แหล่งที่มาที่นับ = main_source 'online' ยกเว้น id 7 (เพจส่วนตัว) และ 20 (Showroom Event)
    $onlineSourceIds = TbSalecarType::where('main_source', 'online')
      ->whereNotIn('id', [7, 20])
      ->pluck('id')
      ->all();

    // ตำแหน่งแถวของตาราง Master_Settings แต่ละ unit — ใช้ให้สูตรอ้าง cell ของ unit ตัวเอง
    $layout = MasterSettingsSheet::layout($this->units);

    $sheets = [];
    foreach ($this->units as $unit) {
      $key = MasterSettingsSheet::unitKey($unit);
      $sheets[] = new LeadOnlinePerBrandSheet(
        $unit['brand'],
        $unit['branch'],
        $unit['branchName'] ?? '',
        $this->fromDate,
        $onlineSourceIds,
        $layout[$key]
      );
    }

    // sheet ค่าคงที่ที่สูตรทุก unit อ้างถึง (ผู้ใช้แก้เป้าต่อสาขาได้เองในไฟล์ Excel แต่ละเดือน)
    $sheets[] = new MasterSettingsSheet($this->units);

    return $sheets;
  }
}
