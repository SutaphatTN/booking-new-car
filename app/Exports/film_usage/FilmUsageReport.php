<?php

namespace App\Exports\film_usage;

use App\Models\FilmUsage;
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
 * รายงานประวัติการใช้ฟิล์ม — กรองตามเดือนของ "วันที่สั่งงาน" (order_date)
 * แสดงระดับรายการ (1 แถว = 1 ตำแหน่งฟิล์ม) พร้อมสต็อกที่ตัด เพื่อดูประวัติการใช้งาน
 * scope ตาม brand ของผู้ใช้อัตโนมัติผ่าน BrandScope (FilmUsage::$sharedByBrandGroup)
 */
class FilmUsageReport implements FromArray, WithTitle, WithHeadings, WithStyles, WithEvents, ShouldAutoSize
{
  protected string $month;
  protected bool $hasData = true;

  public function __construct($month = null)
  {
    $this->month = $month ?: now()->format('Y-m');
  }

  public function title(): string
  {
    return 'ประวัติการใช้ฟิล์ม ' . $this->month;
  }

  public function headings(): array
  {
    return [
      'ลำดับ',
      'วันที่สั่งงาน',
      'ประเภท',
      'เลข VIN',
      'ชื่อลูกค้า',
      'รุ่นรถ',
      // stock ฟิล์มใช้ร่วมกันข้าม brand → บอกให้ชัดว่างานนี้ของแบรนด์ไหน
      'แบรนด์',
      'ฝ่ายขาย',
      'ยี่ห้อฟิล์ม',
      'ตำแหน่ง',
      'ความเข้ม',
      'Stock No.',
      'ตร.ฟุต',
      'ราคาขาย (฿)',
      'ค่าคอม (฿)',
    ];
  }

  public function array(): array
  {
    [$year, $mon] = array_pad(explode('-', $this->month), 2, null);

    $records = FilmUsage::with(['model', 'filmBrand', 'items'])
      ->when($year && $mon, function ($q) use ($year, $mon) {
        $q->whereYear('order_date', (int) $year)
          ->whereMonth('order_date', (int) $mon);
      })
      ->orderBy('order_date')
      ->orderBy('id')
      ->get();

    if ($records->isEmpty()) {
      $this->hasData = false;
      return [['— ไม่มีข้อมูลในเดือนนี้ —']];
    }

    $rows = [];
    $no = 0;
    foreach ($records as $r) {
      $no++;

      $date = $r->order_date?->format('d/m/Y') ?? '-';
      $type = $r->type === 'bp' ? 'BP' : 'ทั่วไป';
      // รุ่นรถจริงเสมอ (relation ปลด BrandScope) + แบรนด์แยกคอลัมน์
      $car   = $r->carLabel();
      $brand = $r->brandName();
      $film  = $r->filmBrand?->name ?? '-';

      // 1 แถว = 1 ตำแหน่งฟิล์ม ; ถ้าไม่มีรายการ แสดงหัวข้อแถวเดียว
      $items = $r->items->count() ? $r->items : collect([null]);
      foreach ($items as $item) {
        $rows[] = [
          $no,
          $date,
          $type,
          $r->vin ?: '-',
          $r->customer_name ?: '-',
          $car,
          $brand,
          $r->sale_person ?: '-',
          $film,
          $item?->position ?: '-',
          $item?->shade ?: '-',
          $item?->stock_no ?: '-',
          $item && $item->sqft_used !== null ? (float) $item->sqft_used : null,
          $item && $item->price !== null ? (float) $item->price : null,
          $item && $item->commission !== null ? (float) $item->commission : null,
        ];
      }
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

        // คำนวณคอลัมน์จากจำนวนหัวตาราง (กันเพี้ยนเวลาเพิ่ม/ลดคอลัมน์)
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

        // คอลัมน์ตัวเลข = 3 คอลัมน์สุดท้าย (ตร.ฟุต, ราคาขาย, ค่าคอม)
        foreach ([$colCount - 2, $colCount - 1, $colCount] as $i) {
          $col = Coordinate::stringFromColumnIndex($i);
          $sheet->getStyle("{$col}2:{$col}{$highestRow}")->getNumberFormat()->setFormatCode('#,##0.00');
        }
      },
    ];
  }
}
