<?php

namespace App\Exports\lead_online;

use App\Models\Salecar;
use App\Models\User;
use App\Support\BrandFeature;
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

  /** id แหล่งที่มาที่นับ (main_source online + platform) — ส่งมาจาก Export */
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

  /** ช่วงแถว "หมายเหตุ" ใต้ตาราง (บอกวิธีนับ + PP รายเดือน) — 0 = ไม่มี */
  protected int $noteFirstRow = 0;
  protected int $noteLastRow  = 0;

  /** cache รายชื่อเซลล์ ใช้ทั้งตอนสร้างแถวและตอนคิด PP รายเดือน */
  protected ?array $salesCache = null;

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

    // ── หมายเหตุใต้ตาราง : เฉพาะยี่ห้อที่นับรายไตรมาส (2 GWM, 4 Lepas) ──
    // ยี่ห้อรายเดือน (1 Mitsubishi, 3 Wuling) ไม่ต้องมี เพราะตัวเลขในตารางคือยอดของเดือนนั้นอยู่แล้ว
    // เดือนอยู่คอลัมน์ B (ใต้ Salesperson) ตัวเลขอยู่คอลัมน์ C (ใต้ PP) จะได้อ่านตรงคอลัมน์เดียวกัน
    if (in_array($this->brand, [2, 4], true)) {
      // ต้องเป็น [''] ไม่ใช่ [] — fromArray ข้ามแถวที่เป็น array ว่าง ทำให้เลขแถวเลื่อนไม่ตรงกับที่คำนวณไว้
      $rows[] = [''];                               // เว้น 1 บรรทัดจากตาราง
      $this->noteFirstRow = count($rows) + 1;

      $rows[] = ['* ยี่ห้อนี้นับแบบ "รายไตรมาส" — ตัวเลขในตารางคือยอดรวมทั้งไตรมาส ' . $this->monthLabel];
      $rows[] = ['', 'PP แยกรายเดือน', ''];

      foreach ($this->ppMonthlyTotals() as $label => $count) {
        $rows[] = ['', $label, $count];
      }

      $this->noteLastRow = count($rows);
    }

    return $rows;
  }

  /**
   * PP รวมของ "สาขานี้" แยกรายเดือนภายในช่วงเวลาของ sheet — ใช้โชว์ใต้ตาราง
   * เงื่อนไขเดียวกับ ppCounts() ทุกอย่าง ต่างแค่ group ตามเดือนแทน sale_id
   * เดือนที่ไม่มีข้อมูลก็แสดงเป็น 0 (ไล่เดือนจาก start ถึง end)
   *
   * @return array<string,int> ['Jul 2026' => 43, ...]
   */
  protected function ppMonthlyTotals(): array
  {
    $saleIds = array_column($this->salespeople(), 'id');

    // ไล่ทุกเดือนในช่วงไว้ก่อน เพื่อให้เดือนที่ยอด 0 ยังโชว์
    $totals = [];
    $cursor = $this->start->copy()->startOfMonth();
    while ($cursor->lte($this->end)) {
      $totals[$cursor->format('Y-m')] = 0;
      $cursor->addMonthNoOverflow();
    }

    if ($saleIds) {
      $firstContact = DB::table('customer_tracking_details')
        ->select('tracking_id', DB::raw('MIN(contact_date) as first_contact'))
        ->whereNull('deleted_at')
        ->whereNotNull('contact_date')
        ->groupBy('tracking_id');

      $counts = DB::table('customer_trackings as ct')
        ->joinSub($firstContact, 'fc', 'fc.tracking_id', '=', 'ct.id')
        ->whereNull('ct.deleted_at')
        ->where('ct.brand', $this->brand)
        ->whereIn('ct.source_id', $this->onlineSourceIds)
        ->tap(fn($q) => $this->scopeEntrant($q))
        ->whereIn('ct.sale_id', $saleIds)
        ->whereBetween('fc.first_contact', [
          $this->start->format('Y-m-d 00:00:00'),
          $this->end->format('Y-m-d 23:59:59'),
        ])
        ->groupBy(DB::raw("DATE_FORMAT(fc.first_contact, '%Y-%m')"))
        ->selectRaw("DATE_FORMAT(fc.first_contact, '%Y-%m') as ym, COUNT(*) as cnt")
        ->pluck('cnt', 'ym')
        ->all();

      foreach ($counts as $ym => $cnt) {
        $totals[$ym] = (int) $cnt;
      }
    }

    // เปลี่ยน key เป็นป้ายอ่านง่าย (Jul 2026) ให้ตรงรูปแบบคอลัมน์ Month
    $labelled = [];
    foreach ($totals as $ym => $cnt) {
      $labelled[Carbon::parse($ym . '-01')->format('M Y')] = $cnt;
    }

    return $labelled;
  }

  /**
   * เซลล์ของ brand นี้ "เฉพาะสาขานี้" (role sale/lead_sale) เรียงตามชื่อ — รวมคนที่ยังไม่มีข้อมูล
   * "ใครขาย brand นี้ได้" อ่านจาก config/brand.php sale_pool + สิทธิ์ขายราย user (sale_switch_extra)
   * ต้องตรงกับที่ PurchaseOrderController::exportLeadOnline ใช้แตก unit
   *
   * ต้อง whereNull('deleted_at') เอง — withoutGlobalScopes() ปิด SoftDeletingScope ไปด้วย
   * ทำให้เซลล์ที่ลบไปแล้วโผล่ในรายงาน (ใส่ไว้เพื่อข้ามขอบเขต brand ของ user ที่กดออกรายงาน)
   */
  protected function salespeople(): array
  {
    $saleBrands = array_map('intval', (array) config("brand.sale_pool.{$this->brand}", [$this->brand]));
    $extraIds   = User::extraSaleUserIdsForBrand($this->brand);

    // brand ที่ถูกขายโดยหลายทีม ต่อท้ายชื่อด้วยทีม เช่น "ซันมาห์ หลานหาด (Mitsu อ่าวลึก)"
    // ใช้วิธีต่อท้ายชื่อแทนการเพิ่มคอลัมน์ เพราะชีทนี้มีสูตร Excel อ้างตัวอักษรคอลัมน์
    // ตายตัว (C..L) เต็มไปหมด แทรกคอลัมน์เมื่อไหร่สูตรเพี้ยนทั้งชีท
    // roster = "ใครอยู่ทีมไหนตอนนี้" จึงอ่านทีมปัจจุบันจาก users ถูกแล้ว ไม่ใช่ snapshot
    $showTeam = BrandFeature::hasMultipleTeams($this->brand);

    return $this->salesCache ??= User::withoutGlobalScopes()
      ->with('saleTeam')
      ->whereNull('deleted_at')
      ->whereIn('role', ['sale', 'lead_sale'])
      ->where(fn($w) => $w->whereIn('brand', $saleBrands)->orWhereIn('id', $extraIds))
      ->where('branch', $this->branch)
      ->orderBy('name')
      ->get(['id', 'name', 'sale_team_id'])
      ->map(function ($u) use ($showTeam) {
        $team = $showTeam ? $u->saleTeam?->name : null;

        return ['id' => $u->id, 'name' => $u->name . ($team ? " ({$team})" : '')];
      })
      ->all();
  }

  /**
   * id ของ "คนดูแลเพจ" (role adminPage) = คนที่รับ lead ออนไลน์เข้าระบบแล้วจ่ายให้เซลล์
   *
   * ไม่กรอง deleted_at ออก เพราะใช้ตัดสิน "ใครเป็นคนกรอก" ของข้อมูลย้อนหลัง
   * ถ้าคนดูแลเพจคนเก่าถูกลบ lead ที่เขาเคยกรอกไว้ต้องยังนับอยู่
   * ไม่ scope ตาม brand เพราะคนดูแลเพจคนเดียวกรอกให้ทุก brand
   */
  protected function adminPageUserIds(): array
  {
    static $ids = null;

    return $ids ??= User::withoutGlobalScopes()
      ->where('role', 'adminPage')
      ->pluck('id')
      ->map(fn($id) => (int) $id)
      ->all();
  }

  /**
   * สาขานี้เริ่มนับ lead ที่ "ใครกรอกก็ได้" ตั้งแต่วันไหน (null = ไม่มี นับเฉพาะที่คนดูแลเพจกรอก)
   *
   * ปกตินับเฉพาะ lead ที่คนดูแลเพจกรอก (= lead ที่เพจรับมาแล้วจ่ายให้เซลล์จริง ๆ) เพราะข้อมูล
   * ย้อนหลังมีเซลล์ติดแหล่งที่มา Online เองไว้เยอะมาก ถ้านับหมดตัวเลขจะเด้งเป็นเท่าตัว
   *
   * สาขาที่เปิดให้เซลล์คีย์ "Online บริษัท" ได้เอง (config/source.php sale_online_count_since)
   * เซลล์รับลีดเองทั้งสาขา ถ้ายังบังคับ adminPage อยู่ PP จะขาดไปทั้งที่ salecarCounts()
   * นับยอดจองให้ครบ → อัตราปิดการขายเพี้ยน แต่ให้นับเฉพาะใบที่คีย์ตั้งแต่วันเริ่มใช้จริง
   * ของเก่าก่อนหน้านั้นยังใช้กติกาเดิม
   */
  protected function anyEntrantSince(): ?string
  {
    $since = config('source.sale_online_count_since', [])[(int) $this->brand][(int) $this->branch] ?? null;

    return $since ? (string) $since : null;
  }

  /** เงื่อนไข "ใครเป็นคนกรอก" ของสาขานี้ — ใช้ร่วมกันทั้ง PP รายเดือนและ PP รายเซลล์ */
  protected function scopeEntrant($query)
  {
    $since = $this->anyEntrantSince();

    return $query->where(function ($q) use ($since) {
      $q->whereIn('ct.UserInsert', $this->adminPageUserIds());

      if ($since) {
        $q->orWhere('ct.created_at', '>=', $since . ' 00:00:00');
      }
    });
  }

  /**
   * PP = จำนวน customer_trackings ที่ "contact_date ตัวแรก" (detail แรกสุด) ตกอยู่ในช่วงเวลา
   *      นับต่อ tracking แค่ครั้งเดียว จัดกลุ่มตาม sale_id ของ tracking
   * (ใช้ contact_date ตัวแรกแทนวันที่สร้าง เพราะกรอกย้อนหลังได้)
   *
   * นับเฉพาะ lead ที่ "คนเพิ่ม" (UserInsert) เป็นคนดูแลเพจ — คือ lead ที่เพจรับมาแล้วจ่ายให้เซลล์จริง ๆ
   * ตัด tracking ที่เซลล์กรอกเองออก แม้จะติดแหล่งที่มาเป็น Online ก็ตาม
   * ยกเว้นสาขาที่เปิดให้เซลล์คีย์ "Online บริษัท" เองได้ ซึ่งนับทุกคนที่กรอก (ดู anyEntrantSince)
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
      // เฉพาะ lead ที่คนดูแลเพจกรอก — ยกเว้นสาขาที่เซลล์คีย์เองได้ (ดู anyEntrantSince)
      ->tap(fn($q) => $this->scopeEntrant($q))
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

        // เส้นกรอบเฉพาะ "ตาราง" — ไม่คลุมบล็อกหมายเหตุใต้ตาราง
        $tableLastRow = $hasData ? $this->totalRow : 2;
        $sheet->getStyle("A1:{$lastCol}{$tableLastRow}")->getBorders()->getAllBorders()
          ->setBorderStyle(Border::BORDER_THIN)->setColor(new Color(Color::COLOR_BLACK));

        // ── บล็อกหมายเหตุ : บอกวิธีนับ (รายเดือน/รายไตรมาส) + PP แยกรายเดือน ──
        if ($this->noteFirstRow > 0) {
          $labelRow  = $this->noteFirstRow;        // บรรทัด "* ยี่ห้อนี้นับแบบ ..."
          $headerRow = $labelRow + 1;              // บรรทัด "PP แยกรายเดือน"

          $sheet->mergeCells("A{$labelRow}:{$lastCol}{$labelRow}");
          $sheet->getStyle("A{$labelRow}")->getFont()->setBold(true)->setItalic(true);
          $sheet->getStyle("A{$labelRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

          $sheet->getStyle("B{$headerRow}")->getFont()->setBold(true);

          // ตารางย่อยเดือน/ยอด อยู่คอลัมน์ B–C ตีกรอบเฉพาะช่วงนั้น
          $sheet->getStyle("B{$headerRow}:C{$this->noteLastRow}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->setColor(new Color(Color::COLOR_BLACK));
          $sheet->getStyle("B{$headerRow}:C{$headerRow}")->getFill()
            ->setFillType('solid')->getStartColor()->setRGB('d9e1f2');
          $sheet->getStyle("C" . ($headerRow + 1) . ":C{$this->noteLastRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

          // fromArray ข้ามค่า 0 (เทียบกับ nullValue แบบหลวม) เดือนที่ไม่มี lead เลยกลายเป็นช่องว่าง
          // เขียน 0 กลับเข้าไปเอง จะได้ไม่ถูกอ่านว่า "ไม่มีข้อมูล"
          for ($r = $headerRow + 1; $r <= $this->noteLastRow; $r++) {
            if ($sheet->getCell("C{$r}")->getValue() === null) {
              $sheet->setCellValue("C{$r}", 0);
            }
          }
        }

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
