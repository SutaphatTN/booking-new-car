<?php

namespace App\Exports\lead_online;

use App\Models\TbSalecarType;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * รายงาน "จัดสรร Lead Online"
 * แยก sheet ตาม brand + sheet Master_Settings (ค่าที่แก้ได้ในไฟล์ Excel เอง ต่อเดือน)
 *   - brand 1,3 = รายเดือน / brand 2,4 = รายไตรมาส (ไตรมาสปฏิทินที่ครอบเดือนที่เลือก)
 *   - admin/gm/md เห็นทุก brand, audit/manager เห็นตาม brand ของตน ($brands ส่งมาจาก controller)
 *   - นับเฉพาะแหล่งที่มาที่เป็น Online (ยกเว้น id 7,20)
 */
class LeadOnlineAllocationExport implements WithMultipleSheets
{
  protected string $fromDate;
  protected array $brands;

  public function __construct($fromDate = null, array $brands = [1, 2, 3, 4])
  {
    $this->fromDate = $fromDate ?? now()->startOfMonth()->format('Y-m');
    $this->brands   = $brands;
  }

  public function sheets(): array
  {
    // แหล่งที่มาที่นับ = main_source 'online' ยกเว้น id 7 (เพจส่วนตัว) และ 20 (Showroom Event)
    $onlineSourceIds = TbSalecarType::where('main_source', 'online')
      ->whereNotIn('id', [7, 20])
      ->pluck('id')
      ->all();

    // ตำแหน่งแถวของตาราง Master_Settings แต่ละ brand — ใช้ให้สูตรอ้าง cell ของ brand ตัวเอง
    $layout = MasterSettingsSheet::layout($this->brands);

    $sheets = [];
    foreach ($this->brands as $brand) {
      $sheets[] = new LeadOnlinePerBrandSheet($brand, $this->fromDate, $onlineSourceIds, $layout[$brand]);
    }

    // sheet ค่าคงที่ที่สูตรทุก brand อ้างถึง (ผู้ใช้แก้ค่าได้เองในไฟล์ Excel แต่ละเดือน)
    $sheets[] = new MasterSettingsSheet($this->brands);

    return $sheets;
  }
}
