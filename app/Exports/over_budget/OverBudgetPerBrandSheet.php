<?php

namespace App\Exports\over_budget;

use App\Models\Salecar;
use App\Services\ApprovalSummary;
use App\Support\BrandFeature;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

/**
 * ชีทรายงาน "เกินงบ" ต่อ brand
 * ดึงรายการจองที่ "ขอเกินงบ" (approval_type = 'overbudget') ในเดือนที่เลือก
 * ตามวันที่ขอ (approval_requested_at) — ครอบคลุมทั้งเกินงบไม่ทะลุเพดาน (b1_manager)
 * และทะลุเพดาน (b1_md / b2_gm)
 *
 * bypass global scope 'userAccess' เพื่อดึงข้าม brand — controller เป็นผู้กำหนด brand ที่เห็นได้
 */
// WithStrictNullComparison: ไม่ใส่ตัวนี้ PhpSpreadsheet จะเทียบแบบหลวม (0 == null) แล้วข้ามช่องทิ้ง
// → ยอดที่เป็น 0 จริง (RI 0, ของแถม 0, ยอดหัก 0) จะกลายเป็นช่องว่าง ดูเหมือนข้อมูลหาย
class OverBudgetPerBrandSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithEvents, ShouldAutoSize, WithStrictNullComparison
{
  protected int $brand;
  protected string $fromDate;
  protected bool $hasData = true;

  public function __construct(int $brand, string $fromDate)
  {
    $this->brand    = $brand;
    $this->fromDate = $fromDate;
  }

  public function title(): string
  {
    return config("brand.names.{$this->brand}", 'Brand ' . $this->brand);
  }

  /** brand นี้ต้องมีคอลัมน์ "ทีม" ไหม (config/brand.php multi_team_brands) */
  private function showsTeam(): bool
  {
    return BrandFeature::hasMultipleTeams($this->brand);
  }

  public function headings(): array
  {
    // ⚠ ห้ามใช้ array_filter ตัด null ตรงนี้ — ค่าใน array() มี null ได้จริง (เปอร์เซ็นต์หัก
    //   ของใบที่ยังไม่กรอกยอด) ถ้ากรองแบบเดียวกันทั้งสองที่ แถวนั้นจะเลื่อนไปทั้งบรรทัด
    return array_merge(
      ['วันที่ขอ', 'สาขา', 'ชื่อฝ่ายขาย'],
      $this->showsTeam() ? ['ทีม'] : [],
      [
      'ชื่อลูกค้า',
      'รุ่นรถ',
      'รุ่นย่อย',

      // ── ชุดเดียวกับบล็อก "สรุปยอดแคมเปญ" ในอีเมลขออนุมัติ (App\Services\ApprovalSummary) ──
      'ราคาขาย',
      'RI (cashSupport)',
      'ยอดรวมแคมเปญ',
      'ยอดรวมประดับยนต์ (ทุนอะไหล่)',
      'Margin คงเหลือ',

      // 2026-08-31 ปิดคอลัมน์ "ประเภทเกินงบ" ตามที่ขอ — ของจริงเกือบทั้งหมดเป็น
      // "เกินงบ ทะลุเพดาน" (ตั้งแต่ต้นปี 28 ใบ ต่อ ไม่ทะลุเพดาน 2 ใบ)
      // เปิดคืนได้โดยเอา comment ออกทั้งบรรทัดนี้ และบรรทัดคู่กันใน array() ด้านล่าง
      // 'ประเภทเกินงบ',
      // เดิมชื่อ "ยอดเกินงบ (เต็มจำนวน)" — เปลี่ยนชื่อให้ตรงกับบล็อก "สรุปยอด" ในอีเมล ค่าเท่าเดิม
      'สรุปการแถม (ยอดเกินงบเต็มจำนวน)',
      'เปอร์เซ็นต์หัก',
      // เดิมชื่อ "ยอดที่ผู้จัดการกรอก" — เปลี่ยนชื่อตามอีเมล แต่คงวงเล็บไว้เตือนว่า
      // กติกาใหม่ทุกแบรนด์กรอกเป็น "ยอดหัก" ส่วนใบเก่าของ brand 1/3 ยังเป็น "ค่าคอมที่ได้"
      // (ดู Salecar::usesDeductAmount)
      'สรุปหักค่าคอม (ยอดที่ผู้จัดการกรอก)',

      // 2026-08-31 ปิดคอลัมน์ "งบเพดาน (over_budget)" ตามที่ขอ — เปิดคืนได้โดยเอา comment ออก
      // ทั้งบรรทัดนี้และบรรทัดคู่กันใน array() ด้านล่าง
      // 'งบเพดาน (over_budget)',
      'เหตุผลขอเกินงบ',
      'สถานะอนุมัติ',
      'สถานะการจอง',
      'หมายเหตุ MD',
      ]
    );
  }

  public function array(): array
  {
    $start = Carbon::parse($this->fromDate)->startOfMonth();
    $end   = Carbon::parse($this->fromDate)->endOfMonth();

    // ปลด scope preApproval → เห็น "คำขออนุมัติเกินงบล่วงหน้า" ที่ยังไม่จองด้วย
    // (แถวเดียวกันนี้จะเปลี่ยนสถานะเป็น "จองแล้ว" เมื่อกดสร้างการจอง → ไม่มีการนับซ้ำ)
    $rows = Salecar::withoutGlobalScope('userAccess')
      ->withoutGlobalScope('preApproval')
      ->with([
        'saleUser.branchInfo',
        'saleTeam',
        'customer.prefix',
        'carOrder.model',
        'carOrder.subModel',
        // ยอดสรุปแคมเปญ — โหลดมาให้ครบก่อน ไม่งั้น ApprovalSummary::build() จะกลายเป็น N+1
        ...ApprovalSummary::RELATIONS,
        // ใบ FN ใช้คิด com finance — ปลด scope ให้ตรงกับที่ ApprovalSummary::comFinance() เคยหาเอง
        'financeConfirm' => fn($q) => $q->withoutGlobalScopes(),
        'model',    // ใช้คิดเคสอนุมัติ (over_budget/per_budget) ใน approvalCase() + fallback รุ่นรถ
        'subModel', // fallback เมื่อยังไม่ผูกรถ (คำขอล่วงหน้า)
      ])
      ->where('brand', $this->brand)
      ->where('approval_type', 'overbudget')
      ->whereNotNull('approval_requested_at')
      ->whereBetween('approval_requested_at', [$start, $end])
      ->orderBy('approval_requested_at')
      ->get();

    // ไม่มีข้อมูล — แสดงข้อความแทน (แถวเดียว รวมช่องใน AfterSheet)
    if ($rows->isEmpty()) {
      $this->hasData = false;
      return [['— ไม่มีข้อมูล —']];
    }

    return $rows->map(function ($r) {
      $customer = trim(
        ($r->customer->prefix->Name_TH ?? '') . ' ' .
          ($r->customer->FirstName ?? '') . ' ' .
          ($r->customer->LastName ?? '')
      );

      // คำขอล่วงหน้ายังไม่ผูกรถ (ไม่มี carOrder) → fallback ไปรุ่นที่เลือกไว้ในใบคำขอ
      $sub    = $r->carOrder?->subModel?->name ?? $r->subModel?->name ?? '-';
      $detail = $r->carOrder?->subModel?->detail ?? $r->subModel?->detail;

      // ประเภทเกินงบ จากเคสอนุมัติ (b1_manager = ไม่ทะลุเพดาน ; b1_md/b2_gm = ทะลุเพดาน)
      // ปิดคอลัมน์ไว้ — เปิดคืนพร้อมกับหัวตารางและบรรทัดใน return
      // $type = match ($r->approvalCase()) {
      //   'b1_manager' => 'เกินงบ ไม่ทะลุเพดาน',
      //   'b1_md', 'b2_gm' => 'เกินงบ ทะลุเพดาน',
      //   default => 'เกินงบ',
      // };

      // ยอดเกินงบเต็มจำนวน — balanceCampaign เก็บค่าที่หาร 2 แล้ว (ติดลบ=เกิน) → คูณกลับ ×2
      $balance = (float) ($r->balanceCampaign ?? 0);
      $overAmount = $balance < 0 ? abs($balance) * 2 : 0.0;
      // $ceiling = (float) ($r->model?->over_budget ?? 0);   // งบเพดาน — ปิดคอลัมน์ไว้

      // ยอดสรุปแคมเปญ — สูตรเดียวกับอีเมลขออนุมัติเป๊ะ ๆ (relation โหลดมาแล้วด้านบน)
      $sum  = ApprovalSummary::build($r, $r->financeConfirm);
      $pct  = ApprovalSummary::deductPercent($r) ?? '';

      // สถานะอนุมัติ — ใช้ลายเซ็น "ตามเคส" (เกินเพดานต้องมี GMApprovalSignature ถึงจะจบ)
      // เดิมเช็คแบบ OR → ใบที่ผู้จัดการกรอกยอดแล้วแต่ยังรอ GM/MD จะขึ้นว่าอนุมัติแล้ว
      $approved = $r->isApprovedNow();
      $appDate  = $r->GMApprovalSignatureDate ?: $r->ApprovalSignatureDate;
      $status   = $approved
        ? 'อนุมัติแล้ว' . ($appDate ? ' (' . Carbon::parse($appDate)->format('d-m-Y') . ')' : '')
        : 'รออนุมัติ';

      return array_merge(
        [
          $r->approval_requested_at ? Carbon::parse($r->approval_requested_at)->format('d-m-Y') : '-',
          optional($r->saleUser?->branchInfo)->name ?? '-',
          optional($r->saleUser)->name ?? '-',
        ],
        $this->showsTeam() ? [$r->saleTeam?->name ?? '-'] : [],
        [
        $customer ?: '-',
        $r->carOrder?->model?->Name_TH ?? $r->model?->Name_TH ?? '-',
        $detail ? "{$detail} - {$sub}" : $sub,

        $sum['price_sub'],
        $sum['ri'],
        $sum['campaign_total'],
        $sum['gift_total'],
        $sum['remaining'],

        // $type,     // ประเภทเกินงบ — ปิดคอลัมน์ไว้
        $overAmount,
        $pct,
        $r->approval_commission_deduct !== null ? (float) $r->approval_commission_deduct : 0.0,
        // $ceiling,   // งบเพดาน — ปิดคอลัมน์ไว้
        $r->reason_campaign ?: '-',
        $status,
        $r->bookingStatusLabel(),
        $r->approval_md_note ?: '-',
        ]
      );
    })->all();
  }

  public function styles(Worksheet $sheet)
  {
    return [
      1 => [
        'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'ffc000']],
        'font' => ['bold' => true],
        'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
      ],
    ];
  }

  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        $sheet      = $event->sheet->getDelegate();
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();

        $sheet->getStyle("A1:{$highestCol}{$highestRow}")->getFont()->setName('Angsana New')->setSize(14);
        $sheet->getStyle("A1:{$highestCol}{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A1:{$highestCol}{$highestRow}")->getBorders()->getAllBorders()
          ->setBorderStyle(Border::BORDER_THIN)->setColor(new Color(Color::COLOR_BLACK));

        $sheet->getRowDimension(1)->setRowHeight(25);

        // คำนวณคอลัมน์สุดท้ายจากจำนวนหัวตาราง (กันเพี้ยนเวลาเพิ่ม/ลดคอลัมน์)
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->headings()));

        $sheet->setAutoFilter("A1:{$lastCol}{$highestRow}");
        $sheet->freezePane('A2');
        $sheet->getTabColor()->setRGB('ffc000');

        // ไม่มีข้อมูล — รวมช่องแถวข้อความให้เต็มบรรทัด จัดกึ่งกลาง
        if (!$this->hasData) {
          $sheet->mergeCells("A2:{$lastCol}2");
          $sheet->getStyle("A2:{$lastCol}2")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
          $sheet->getStyle('A2')->getFont()->setItalic(true)->getColor()->setRGB('999999');
          return;
        }

        // คอลัมน์เงิน — หาตำแหน่งจากชื่อหัวตาราง (ขยับตามคอลัมน์ "ทีม" ที่โผล่บาง brand)
        $headings  = $this->headings();
        $moneyCols = [];
        $money = [
          'ราคาขาย', 'RI (cashSupport)', 'ยอดรวมแคมเปญ', 'ยอดรวมประดับยนต์ (ทุนอะไหล่)',
          'Margin คงเหลือ', 'สรุปการแถม (ยอดเกินงบเต็มจำนวน)', 'สรุปหักค่าคอม (ยอดที่ผู้จัดการกรอก)',
          'งบเพดาน (over_budget)',
        ];
        foreach ($money as $label) {
          $i = array_search($label, $headings, true);
          if ($i !== false) {
            $moneyCols[] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
          }
        }

        foreach ($moneyCols as $col) {
          $sheet->getStyle("{$col}2:{$col}{$highestRow}")->getNumberFormat()->setFormatCode('#,##0.00');
        }

        // เปอร์เซ็นต์หัก — เก็บเป็นตัวเลขล้วน (10.00) ต่อท้ายด้วย % ตอนแสดง
        $pctIndex = array_search('เปอร์เซ็นต์หัก', $headings, true);
        if ($pctIndex !== false) {
          $pctCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($pctIndex + 1);
          $sheet->getStyle("{$pctCol}2:{$pctCol}{$highestRow}")->getNumberFormat()->setFormatCode('0.00"%"');
        }
      },
    ];
  }
}
