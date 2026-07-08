<?php

namespace App\Exports\over_budget;

use App\Models\Salecar;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
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
class OverBudgetPerBrandSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithEvents, ShouldAutoSize
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

  public function headings(): array
  {
    return [
      'วันที่ขอ',
      'สาขา',
      'ชื่อฝ่ายขาย',
      'ชื่อลูกค้า',
      'รุ่นรถ',
      'รุ่นย่อย',
      'ประเภทเกินงบ',
      'ยอดเกินงบ (เต็มจำนวน)',
      'งบเพดาน (over_budget)',
      'ยอดหักคอม (ผู้จัดการกรอก)',
      'เหตุผลขอเกินงบ',
      'สถานะอนุมัติ',
      'หมายเหตุ MD',
    ];
  }

  public function array(): array
  {
    $start = Carbon::parse($this->fromDate)->startOfMonth();
    $end   = Carbon::parse($this->fromDate)->endOfMonth();

    $rows = Salecar::withoutGlobalScope('userAccess')
      ->with([
        'saleUser.branchInfo',
        'customer.prefix',
        'carOrder.model',
        'carOrder.subModel',
        'model', // ใช้คิดเคสอนุมัติ (over_budget/per_budget) ใน approvalCase()
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

      $sub    = $r->carOrder->subModel->name ?? '-';
      $detail = $r->carOrder->subModel->detail ?? null;

      // ประเภทเกินงบ จากเคสอนุมัติ (b1_manager = ไม่ทะลุเพดาน ; b1_md/b2_gm = ทะลุเพดาน)
      $type = match ($r->approvalCase()) {
        'b1_manager' => 'เกินงบ ไม่ทะลุเพดาน',
        'b1_md', 'b2_gm' => 'เกินงบ ทะลุเพดาน',
        default => 'เกินงบ',
      };

      // ยอดเกินงบเต็มจำนวน — balanceCampaign เก็บค่าที่หาร 2 แล้ว (ติดลบ=เกิน) → คูณกลับ ×2
      $balance = (float) ($r->balanceCampaign ?? 0);
      $overAmount = $balance < 0 ? abs($balance) * 2 : 0.0;
      $ceiling = (float) ($r->model?->over_budget ?? 0);

      // สถานะอนุมัติ — brand 2 ใช้ GMApprovalSignature, brand 1/3 ใช้ ApprovalSignature
      $approved = !empty($r->ApprovalSignature) || !empty($r->GMApprovalSignature);
      $appDate  = $r->ApprovalSignatureDate ?: $r->GMApprovalSignatureDate;
      $status   = $approved
        ? 'อนุมัติแล้ว' . ($appDate ? ' (' . Carbon::parse($appDate)->format('d-m-Y') . ')' : '')
        : 'รออนุมัติ';

      return [
        $r->approval_requested_at ? Carbon::parse($r->approval_requested_at)->format('d-m-Y') : '-',
        optional($r->saleUser?->branchInfo)->name ?? '-',
        optional($r->saleUser)->name ?? '-',
        $customer ?: '-',
        $r->carOrder->model->Name_TH ?? '-',
        $detail ? "{$detail} - {$sub}" : $sub,
        $type,
        $overAmount,
        $ceiling,
        $r->approval_commission_deduct !== null ? (float) $r->approval_commission_deduct : 0.0,
        $r->reason_campaign ?: '-',
        $status,
        $r->approval_md_note ?: '-',
      ];
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

        $sheet->setAutoFilter("A1:M{$highestRow}");
        $sheet->freezePane('A2');
        $sheet->getTabColor()->setRGB('ffc000');

        // ไม่มีข้อมูล — รวมช่องแถวข้อความให้เต็มบรรทัด จัดกึ่งกลาง
        if (!$this->hasData) {
          $sheet->mergeCells('A2:M2');
          $sheet->getStyle('A2:M2')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
          $sheet->getStyle('A2')->getFont()->setItalic(true)->getColor()->setRGB('999999');
          return;
        }

        // คอลัมน์เงิน H (ยอดเกินงบ), I (งบเพดาน), J (ยอดหักคอม)
        foreach (['H', 'I', 'J'] as $col) {
          $sheet->getStyle("{$col}2:{$col}{$highestRow}")->getNumberFormat()->setFormatCode('#,##0.00');
        }
      },
    ];
  }
}
