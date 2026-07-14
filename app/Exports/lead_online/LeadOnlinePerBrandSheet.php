<?php

namespace App\Exports\lead_online;

use App\Models\Salecar;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

/**
 * 1 sheet = 1 (brand × สาขา) ของรายงาน "จัดสรร Lead Online"
 *
 * ช่วงเวลา (period) ต่างกันตาม brand:
 *   - brand 1,3 : รายเดือน (เดือนที่เลือก)
 *   - brand 2,4 : รายไตรมาสปฏิทินที่ครอบเดือนที่เลือก (เลือกเดือน 6 → เดือน 4-6, เลือกเดือน 8 → 7-9)
 *
 * แถว = เซลล์ของ brand นี้ "เฉพาะสาขานี้" (role sale/lead_sale) รวมคนที่ค่าเป็น 0
 * คอลัมน์ F,G,H,I,J,K,L เขียนเป็น "สูตร Excel จริง" อ้างถึง sheet Master_Settings (บล็อกของ brand×สาขานี้)
 * จึงคำนวณสดในไฟล์ — แต่ละสาขาตั้งเป้า (target) ต่างกันได้
 */
class LeadOnlinePerBrandSheet implements FromArray, WithTitle, WithEvents, ShouldAutoSize
{
  // 1 = MITSUBISHI, 2 = GWM, 3 = Wuling, 4 = LEPAS
  public const BRANDS = [
    1 => 'MITSUBISHI',
    2 => 'GWM',
    3 => 'Wuling',
    4 => 'LEPAS',
  ];

  protected int $brand;
  protected int $branch;
  protected string $branchName;
  protected string $fromDate;

  /** id แหล่งที่มาที่นับ (main_source online ยกเว้น 7,20) — ส่งมาจาก Export */
  protected array $onlineSourceIds;

  /** แถวใน sheet Master_Settings ที่สูตรของ brand นี้อ้างถึง (คอลัมน์ B) */
  protected array $settingRows;

  protected Carbon $start;
  protected Carbon $end;
  protected string $monthLabel;

  /** แถวข้อมูลจริงเริ่มที่แถว 3 (แถว 1 = ชื่อ brand, แถว 2 = หัวคอลัมน์) */
  protected int $firstDataRow = 3;
  protected int $lastDataRow  = 2;   // = firstDataRow - 1 เมื่อไม่มีข้อมูล
  protected int $totalRow     = 3;

  public function __construct($brand, $branch = null, $branchName = '', $fromDate = null, array $onlineSourceIds = [], array $settingRows = [])
  {
    $this->brand           = (int) $brand;
    $this->branch          = (int) $branch;
    $this->branchName      = $branchName !== '' ? $branchName : ('สาขา ' . (int) $branch);
    $this->fromDate        = $fromDate ?? now()->startOfMonth()->format('Y-m');
    $this->onlineSourceIds = $onlineSourceIds;
    // default: ตาราง Master_Settings บล็อกเดียวเริ่มแถว 3 (header=3, ค่าเริ่มแถว 4)
    $this->settingRows     = $settingRows + ['target' => 4, 'weight_delivery' => 5, 'weight_booking' => 6];

    $base = Carbon::parse($this->fromDate . '-01')->startOfMonth();

    if (in_array($this->brand, [2, 4], true)) {
      // ไตรมาสปฏิทินที่ครอบเดือนที่เลือก
      $qStartMonth = intdiv($base->month - 1, 3) * 3 + 1;
      $this->start = Carbon::create($base->year, $qStartMonth, 1)->startOfMonth();
      $this->end   = $this->start->copy()->addMonths(2)->endOfMonth();
      $this->monthLabel = $this->start->format('M') . '-' . $this->end->format('M Y');
    } else {
      $this->start = $base->copy()->startOfMonth();
      $this->end   = $base->copy()->endOfMonth();
      $this->monthLabel = $this->start->format('M-Y');
    }
  }

  public function title(): string
  {
    $brandName = self::BRANDS[$this->brand] ?? ('Brand ' . $this->brand);
    // ชื่อ sheet Excel ห้ามเกิน 31 ตัว และห้ามมีอักขระ : \ / ? * [ ]
    $title = $brandName . ' - ' . $this->branchName;
    return mb_substr(str_replace([':', '\\', '/', '?', '*', '[', ']'], ' ', $title), 0, 31);
  }

  /** ป้ายหัวตาราง (แถว 1) — ชื่อเต็มไม่ต้องตัด */
  protected function heading(): string
  {
    $brandName = self::BRANDS[$this->brand] ?? ('Brand ' . $this->brand);
    return $brandName . ' - ' . $this->branchName;
  }

  public function array(): array
  {
    $sales = $this->salespeople();          // [ ['id'=>, 'name'=>], ... ]
    $pp       = $this->ppCounts();          // [sale_id => count]
    $booking  = $this->salecarCounts('BookingDate');
    $delivery = $this->salecarCounts('DeliveryInCKDate');

    $tg = $this->settingRows['target'];          // Target PP / Month
    $wd = $this->settingRows['weight_delivery']; // Weight: Delivery
    $wb = $this->settingRows['weight_booking'];  // Weight: Booking

    $header = [
      'Month', 'Salesperson', 'PP', 'Booking', 'Delivery',
      'Booking', 'Delivery', 'Score', 'Share', 'Raw Next PP',
      'Final Next Month PP', 'เป้าส่งมอบเดือนหน้า [20:1]',
    ];

    $rows = [];
    $rows[] = [$this->heading()];  // แถว 1 : ชื่อ brand + สาขา (merge ทีหลัง)
    $rows[] = $header;           // แถว 2 : หัวคอลัมน์

    $n = count($sales);
    $this->lastDataRow = $n > 0 ? ($this->firstDataRow + $n - 1) : ($this->firstDataRow - 1);
    $this->totalRow    = $this->lastDataRow + 1;

    $first = $this->firstDataRow;
    $last  = $this->lastDataRow;

    foreach ($sales as $i => $s) {
      $r = $first + $i;
      $rows[] = [
        $this->monthLabel,
        $s['name'],
        (int) ($pp[$s['id']] ?? 0),
        (int) ($booking[$s['id']] ?? 0),
        (int) ($delivery[$s['id']] ?? 0),
        "=IFERROR(D{$r}/C{$r},0)",                                                             // F Booking Rate
        "=IFERROR(E{$r}/C{$r},0)",                                                             // G Delivery Rate
        "=G{$r}*Master_Settings!\$B\${$wd}+F{$r}*Master_Settings!\$B\${$wb}",                  // H Score
        "=IFERROR(H{$r}/SUM(\$H\${$first}:\$H\${$last}),0)",                                   // I Share
        "=I{$r}*Master_Settings!\$B\${$tg}",                                                   // J Raw Next PP
        "=MIN(50,IF(J{$r}=0,10,IF(J{$r}<20,20,IF(J{$r}<30,25,ROUND(J{$r},-1)))))",             // K Final Next Month PP
        "=ROUND((K{$r}/20),1)",                                                                // L เป้าส่งมอบเดือนหน้า
      ];
    }

    // แถวรวม (เฉพาะเมื่อมีข้อมูล)
    if ($n > 0) {
      $rows[] = [
        '', 'รวม',
        "=SUM(C{$first}:C{$last})",
        "=SUM(D{$first}:D{$last})",
        "=SUM(E{$first}:E{$last})",
        '', '',
        "=SUM(H{$first}:H{$last})",
        "=SUM(I{$first}:I{$last})",
        "=SUM(J{$first}:J{$last})",
        "=SUM(K{$first}:K{$last})",
        "=SUM(L{$first}:L{$last})",
      ];
    }

    return $rows;
  }

  /**
   * เซลล์ของ brand นี้ "เฉพาะสาขานี้" (role sale/lead_sale) เรียงตามชื่อ — รวมคนที่ยังไม่มีข้อมูล
   * brand 3 (Wuling) รวมเซลล์ brand 4 (Lepas) ด้วย เพราะ Lepas ขายรถ Wuling (record brand=3)
   */
  protected function salespeople(): array
  {
    $rosterBrands = $this->brand === 3 ? [3, 4] : [$this->brand];

    return User::withoutGlobalScopes()
      ->whereIn('role', ['sale', 'lead_sale'])
      ->whereIn('brand', $rosterBrands)
      ->where('branch', $this->branch)
      ->orderBy('name')
      ->get(['id', 'name'])
      ->map(fn($u) => ['id' => $u->id, 'name' => $u->name])
      ->all();
  }

  /**
   * PP = จำนวน customer_trackings ที่ "contact_date ตัวแรก" (detail แรกสุด) ตกอยู่ในช่วงเวลา
   *      นับต่อ tracking แค่ครั้งเดียว จัดกลุ่มตาม sale_id ของ tracking
   * (ใช้ contact_date ตัวแรกแทนวันที่สร้าง เพราะกรอกย้อนหลังได้)
   */
  protected function ppCounts(): array
  {
    $firstContact = DB::table('customer_tracking_details')
      ->select('tracking_id', DB::raw('MIN(contact_date) as first_contact'))
      ->whereNull('deleted_at')
      ->whereNotNull('contact_date')
      ->groupBy('tracking_id');

    return DB::table('customer_trackings as ct')
      ->joinSub($firstContact, 'fc', 'fc.tracking_id', '=', 'ct.id')
      ->whereNull('ct.deleted_at')
      ->where('ct.brand', $this->brand)
      // นับเฉพาะแหล่งที่มา Online (ยกเว้น id 7,20) — รวม tracking ที่ยกเลิก (cancelled_at) ด้วย
      ->whereIn('ct.source_id', $this->onlineSourceIds)
      ->whereBetween('fc.first_contact', [
        $this->start->format('Y-m-d 00:00:00'),
        $this->end->format('Y-m-d 23:59:59'),
      ])
      ->select(DB::raw('ct.sale_id as sale_id'), DB::raw('COUNT(*) as cnt'))
      ->groupBy('ct.sale_id')
      ->pluck('cnt', 'sale_id')
      ->all();
  }

  /** นับ salecars ตามคอลัมน์วันที่ ($dateColumn) ในช่วงเวลา จัดกลุ่มตาม SaleID */
  protected function salecarCounts(string $dateColumn): array
  {
    return Salecar::withoutGlobalScope('userAccess')
      ->where('brand', $this->brand)
      // นับเฉพาะแหล่งที่มา Online (ยกเว้น id 7,20)
      ->whereIn('type', $this->onlineSourceIds)
      // ตัดสถานะถอน con_status 7-9 (คง null ไว้)
      ->where(function ($q) {
        $q->whereNull('con_status')->orWhereNotIn('con_status', [7, 8, 9]);
      })
      ->whereNotNull($dateColumn)
      ->whereBetween($dateColumn, [
        $this->start->format('Y-m-d 00:00:00'),
        $this->end->format('Y-m-d 23:59:59'),
      ])
      ->groupBy('SaleID')
      ->selectRaw('SaleID as sale_id, COUNT(*) as cnt')
      ->pluck('cnt', 'sale_id')
      ->all();
  }

  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        $sheet = $event->sheet->getDelegate();
        $highestRow = $sheet->getHighestRow();
        $lastCol = 'L';

        $first = $this->firstDataRow;
        $last  = $this->lastDataRow;
        $hasData = $last >= $first;

        // ฟอนต์รวมทั้ง sheet
        $sheet->getStyle("A1:{$lastCol}{$highestRow}")->getFont()->setName('Angsana New')->setSize(14);
        $sheet->getStyle("A1:{$lastCol}{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // แถว 1 : ชื่อ brand
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFill()->setFillType('solid')->getStartColor()->setRGB('1f4e78');
        $sheet->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF');

        // แถว 2 : หัวคอลัมน์
        $sheet->getStyle("A2:{$lastCol}2")->getFont()->setBold(true);
        $sheet->getStyle("A2:{$lastCol}2")->getFill()->setFillType('solid')->getStartColor()->setRGB('2e75b6');
        $sheet->getStyle("A2:{$lastCol}2")->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A2:{$lastCol}2")->getAlignment()
          ->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);

        if ($hasData) {
          // จัดกลางคอลัมน์ตัวเลข
          $sheet->getStyle("C{$first}:{$lastCol}{$this->totalRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

          // เปอร์เซ็นต์: Booking Rate (F), Delivery Rate (G), Share (I)
          $sheet->getStyle("F{$first}:G{$this->totalRow}")->getNumberFormat()->setFormatCode('0.00%');
          $sheet->getStyle("I{$first}:I{$this->totalRow}")->getNumberFormat()->setFormatCode('0.00%');

          // Score (H) ทศนิยมละเอียด, Raw Next PP (J) ทศนิยม 2, Final (K) จำนวนเต็ม, เป้า (L) ทศนิยม 1
          $sheet->getStyle("H{$first}:H{$this->totalRow}")->getNumberFormat()->setFormatCode('0.000000000');
          $sheet->getStyle("J{$first}:J{$this->totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');
          $sheet->getStyle("K{$first}:K{$this->totalRow}")->getNumberFormat()->setFormatCode('#,##0');
          $sheet->getStyle("L{$first}:L{$this->totalRow}")->getNumberFormat()->setFormatCode('0.0');

          // แถวรวม
          $sheet->getStyle("A{$this->totalRow}:{$lastCol}{$this->totalRow}")->getFont()->setBold(true);
          $sheet->getStyle("A{$this->totalRow}:{$lastCol}{$this->totalRow}")->getFill()
            ->setFillType('solid')->getStartColor()->setRGB('d9e1f2');
        }

        // เส้นกรอบทั้งตาราง
        $sheet->getStyle("A1:{$lastCol}{$highestRow}")->getBorders()->getAllBorders()
          ->setBorderStyle(Border::BORDER_THIN)->setColor(new Color(Color::COLOR_BLACK));

        // ความสูงแถว
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getRowDimension(2)->setRowHeight(38);

        // freeze หัวตาราง
        $sheet->freezePane('A3');

        // สี tab
        $tabColors = [1 => 'a4d4ae', 2 => 'ffe699', 3 => 'b4c7e7', 4 => 'f8cbad'];
        $sheet->getTabColor()->setRGB($tabColors[$this->brand] ?? 'a4d4ae');
      },
    ];
  }
}
