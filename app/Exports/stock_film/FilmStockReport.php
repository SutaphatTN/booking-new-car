<?php

namespace App\Exports\stock_film;

use App\Models\FilmStock;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

/**
 * รายงานดึง Stock ฟิล์ม — สแนปช็อตสต็อกฟิล์มที่ยังใช้งานอยู่ (ยังไม่ปิดการตรวจสอบ)
 * scope ตาม brand ของผู้ใช้อัตโนมัติผ่าน BrandScope (FilmStock::$sharedByBrandGroup)
 */
class FilmStockReport implements FromArray, WithTitle, WithHeadings, WithStyles, WithEvents, ShouldAutoSize
{
  protected bool $hasData = true;

  public function title(): string
  {
    return 'ข้อมูล Stock ฟิล์ม';
  }

  public function headings(): array
  {
    return [
      'ลำดับ',
      'Stock No.',
      'Part No.',
      'กลุ่มแบรนด์',
      'ยี่ห้อฟิล์ม',
      'ความเข้ม',
      'วันที่เบิก',
      'จำนวนเริ่มต้น (ตร.ฟุต)',
      'ใช้ไปแล้ว (ตร.ฟุต)',
      'คงเหลือ (ตร.ฟุต)',
      'สถานะ',
      'วันที่ตรวจสอบ',
      'ตรวจสอบคงเหลือ (ตร.ฟุต)',
      'ยอดส่วนต่าง (ตร.ฟุต)',
      'ผลการตรวจนับ',
    ];
  }

  public function array(): array
  {
    $stocks = FilmStock::with('filmBrand')
      ->whereNull('audit_completed_at') // ตรงกับลิสต์บนหน้าจอ — ตรวจสอบเสร็จสิ้นแล้ว = ไม่นับ
      ->orderBy('withdrawal_date', 'desc')
      ->orderBy('brand_group')
      ->orderBy('film_brand_id')
      ->orderBy('shade')
      ->get();

    if ($stocks->isEmpty()) {
      $this->hasData = false;
      return [['— ไม่มีข้อมูลสต็อกฟิล์ม —']];
    }

    $rows = [];
    $no = 0;
    foreach ($stocks as $s) {
      $no++;

      $remaining = $s->remaining_qty;
      $diff      = $s->inspection_diff;

      $status = $remaining <= 0
        ? 'หมด'
        : ($remaining < 100 ? 'เหลือน้อย' : 'ใช้งาน');

      $result = match ($s->inspection_result) {
        'pass'  => 'ถูกต้อง',
        'fail'  => 'ไม่ถูกต้อง',
        default => '-',
      };

      $rows[] = [
        $no,
        $s->stock_no,
        $s->part_no ?: '-',
        FilmStock::BRAND_GROUPS[$s->brand_group] ?? $s->brand_group,
        $s->filmBrand?->name ?? '-',
        $s->shade,
        $s->withdrawal_date?->format('d/m/Y') ?? '-',
        (float) $s->initial_qty,
        (float) $s->used_qty,
        (float) $remaining,
        $status,
        $s->inspection_date?->format('d/m/Y') ?? '-',
        $s->inspection_qty !== null ? (float) $s->inspection_qty : '-',
        $diff !== null ? (float) $diff : '-',
        $result,
      ];
    }

    return $rows;
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
        $sheet->freezePane('A2');
        $sheet->getTabColor()->setRGB('ffc000');

        $colCount = count($this->headings());
        $lastCol  = Coordinate::stringFromColumnIndex($colCount);

        // ไม่มีข้อมูล — รวมช่องข้อความให้เต็มบรรทัด
        if (!$this->hasData) {
          $sheet->mergeCells("A2:{$lastCol}2");
          $sheet->getStyle("A2:{$lastCol}2")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
          $sheet->getStyle('A2')->getFont()->setItalic(true)->getColor()->setRGB('999999');
          return;
        }

        $sheet->setAutoFilter("A1:{$lastCol}{$highestRow}");

        // คอลัมน์ตัวเลข (ตร.ฟุต) : H, I, J (เริ่มต้น/ใช้ไป/คงเหลือ) และ M, N (ตรวจสอบคงเหลือ/ส่วนต่าง)
        foreach (['H', 'I', 'J', 'M', 'N'] as $col) {
          $sheet->getStyle("{$col}2:{$col}{$highestRow}")->getNumberFormat()->setFormatCode('#,##0.00');
        }
      },
    ];
  }
}
