<?php

namespace App\Exports\lead_online;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

/**
 * sheet "Master_Settings" — ค่าคงที่ที่สูตรในแต่ละ sheet brand×สาขา อ้างถึง
 * แยกตารางต่อ "unit" (brand + สาขา) — แต่ละสาขากรอกค่า/เป้าไม่เท่ากันได้ วางเรียงลงมาใน sheet เดียว
 * ผู้ใช้เปิดไฟล์ Excel แล้วแก้ค่าในคอลัมน์ B ได้เอง สูตรจะคำนวณใหม่ทันที
 *
 * ตำแหน่งแถวของแต่ละ unit คำนวณจาก layout() ซึ่ง Export ใช้ร่วมกับ LeadOnlinePerBrandSheet
 * เพื่อให้สูตรอ้าง cell ของ unit ตัวเองถูกต้อง
 *
 * unit = ['brand' => int, 'branch' => int, 'branchName' => string]
 */
class MasterSettingsSheet implements FromArray, WithTitle, WithEvents
{
  /** ค่า default (เท่ากันทุก unit ตอน export — ผู้ใช้ไปแก้เป้าแยกต่อสาขาเองในไฟล์) */
  protected const DEFAULTS = [
    'target'          => 450,  // Target PP / Month  → สูตรอ้าง (Raw Next PP)
    'weight_delivery' => 0.8,  // Weight: Delivery   → สูตรอ้าง (Score)
    'weight_booking'  => 0.2,  // Weight: Booking    → สูตรอ้าง (Score)
    'min_pp'          => 10,   // Min PP / Sales     (ข้อมูลอ้างอิง)
    'max_pp'          => 50,   // Max PP / Sales     (ข้อมูลอ้างอิง)
    'rounding'        => 10,   // Rounding Base      (ข้อมูลอ้างอิง)
  ];

  protected const LABELS = [
    'target'          => 'Target PP / Month',
    'weight_delivery' => 'Weight: Delivery',
    'weight_booking'  => 'Weight: Booking',
    'min_pp'          => 'Min PP / Sales',
    'max_pp'          => 'Max PP / Sales',
    'rounding'        => 'Rounding Base',
  ];

  protected const START_ROW = 3;   // แถวหัวตารางของ unit แรก
  protected const STRIDE    = 9;   // ความสูงต่อ 1 unit (หัว 1 + ค่า 6 + เว้น 2)

  /** @var array<int,array{brand:int,branch:int,branchName:string}> */
  protected array $units;

  public function __construct(array $units = [])
  {
    $this->units = array_values($units);
  }

  public function title(): string
  {
    return 'Master_Settings';
  }

  /** key ประจำ unit (brand + สาขา) — ใช้ให้สูตรของ sheet brand×สาขา อ้างแถวถูกบล็อก */
  public static function unitKey(array $unit): string
  {
    return $unit['brand'] . '-' . $unit['branch'];
  }

  /**
   * แผนที่แถวของแต่ละ unit: [ "brand-branch" => ['header'=>, 'target'=>, 'weight_delivery'=>, ...] ]
   * (คอลัมน์ค่าคือ B เสมอ)
   */
  public static function layout(array $units): array
  {
    $keys = array_keys(self::DEFAULTS);
    $map  = [];
    foreach (array_values($units) as $i => $unit) {
      $header = self::START_ROW + $i * self::STRIDE;
      $rows = ['header' => $header];
      foreach ($keys as $k => $key) {
        $rows[$key] = $header + 1 + $k;
      }
      $map[self::unitKey($unit)] = $rows;
    }
    return $map;
  }

  public function array(): array
  {
    $layout = self::layout($this->units);
    $lastRow = empty($layout) ? 1 : max(array_map(fn($r) => $r['rounding'], $layout));

    // เตรียมกริดว่าง (คอลัมน์ A,B) แล้วเติมตามตำแหน่งของแต่ละ unit
    $grid = [];
    for ($r = 1; $r <= $lastRow; $r++) {
      $grid[$r] = ['', ''];
    }
    $grid[1] = ['Online PP Allocation – Master Settings (ใช้ทุกเดือน)', ''];

    foreach ($this->units as $unit) {
      $rows = $layout[self::unitKey($unit)];
      $grid[$rows['header']] = [self::unitLabel($unit), ''];
      foreach (self::LABELS as $key => $label) {
        $grid[$rows[$key]] = [$label, self::DEFAULTS[$key]];
      }
    }

    return array_values($grid);
  }

  /** ป้ายบล็อก: ชื่อ brand + ชื่อสาขา */
  protected static function unitLabel(array $unit): string
  {
    $brandName = LeadOnlinePerBrandSheet::BRANDS[$unit['brand']] ?? ('Brand ' . $unit['brand']);
    $branch    = $unit['branchName'] ?? ('สาขา ' . $unit['branch']);
    return $brandName . ' - ' . $branch;
  }

  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        $sheet = $event->sheet->getDelegate();
        $layout = self::layout($this->units);
        if (empty($layout)) {
          return;
        }
        $lastRow = max(array_map(fn($r) => $r['rounding'], $layout));

        $sheet->getStyle("A1:B{$lastRow}")->getFont()->setName('Angsana New')->setSize(14);

        // หัวเรื่องรวม
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(15);
        $sheet->getStyle('A1')->getFill()->setFillType('solid')->getStartColor()->setRGB('f4b183');

        $tabColors = [1 => 'a4d4ae', 2 => 'ffe699', 3 => 'b4c7e7', 4 => 'f8cbad'];

        foreach ($this->units as $unit) {
          $rows = $layout[self::unitKey($unit)];
          $h = $rows['header'];

          // หัวตาราง unit
          $sheet->mergeCells("A{$h}:B{$h}");
          $sheet->getStyle("A{$h}")->getFont()->setBold(true)->setSize(14);
          $sheet->getStyle("A{$h}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
          $sheet->getStyle("A{$h}")->getFill()->setFillType('solid')
            ->getStartColor()->setRGB($tabColors[$unit['brand']] ?? 'd9d9d9');

          // แถวค่า
          $valTop = $rows['target'];
          $valBottom = $rows['rounding'];
          $sheet->getStyle("A{$valTop}:A{$valBottom}")->getFont()->setBold(true);
          $sheet->getStyle("B{$valTop}:B{$valBottom}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
          $sheet->getStyle("B{$valTop}:B{$valBottom}")->getFill()->setFillType('solid')
            ->getStartColor()->setRGB('dbe5f1');

          // กรอบตาราง (หัว + ค่า)
          $sheet->getStyle("A{$h}:B{$valBottom}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->setColor(new Color(Color::COLOR_BLACK));
        }

        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getTabColor()->setRGB('f4b183');
      },
    ];
  }
}
