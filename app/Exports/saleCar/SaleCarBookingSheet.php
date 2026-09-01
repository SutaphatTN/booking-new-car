<?php

namespace App\Exports\saleCar;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Support\BrandFeature;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * sheet "สรุปข้อมูลการจอง" — รายใบจอง 1 บรรทัด
 * ข้อมูลถูก query + map มาจาก SaleCarBookingExport แล้ว (แชร์กับ sheet สรุป Lead Channel
 * จะได้ไม่ยิง query ซ้ำสองรอบ)
 */
class SaleCarBookingSheet implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize, WithColumnFormatting
{
    protected Collection $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function title(): string
    {
        return 'สรุปข้อมูลการจอง';
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,   // เลขบัตรประชาชน (อยู่ก่อนคอลัมน์ที่ขยับได้ทั้งหมด)
        ];
    }

    /**
     * ตำแหน่งคอลัมน์ ราคาขาย / เงินจอง — ขยับตาม brand เพราะ ทีม / Option / สีภายใน โผล่ไม่เท่ากัน
     * ต้องตรงกับลำดับใน blade saleCar/booking/summary.blade.php
     */
    private function moneyColumns(): array
    {
        $brand = auth()->user()->brand;

        $next = 9; // A-H = No, ลูกค้า, บัตรประชาชน, เบอร์โทร, ที่อยู่, ฝ่ายขาย, รุ่นหลัก, รุ่นย่อย
        if (BrandFeature::hasMultipleTeams($brand)) $next++;  // ทีม (ต่อจากฝ่ายขาย)
        if (!in_array($brand, [2, 3, 4])) $next++;            // Option
        $next++;                                              // สี
        if (BrandFeature::hasInteriorColor($brand)) $next++;  // สีภายใน
        $next++;                                              // ปี
        $msrp = $next++;                                      // ราคาขาย
        $next += 2;                                           // แหล่งที่มาหลัก + แหล่งที่มาย่อย
        $deposit = $next++;                                   // เงินจอง

        return array_map(fn($i) => Coordinate::stringFromColumnIndex($i), [$msrp, $deposit]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            //แถวบนสุด
            1 => [
                'font' => [],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => 'ffdaa2'],
                ],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center',
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();

                // font
                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getFont()
                    ->setName('Angsana New')
                    ->setSize(14);

                // กึ่งกลางตาม row
                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // เส้นกรอบ
                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->setColor(new Color(Color::COLOR_BLACK));

                // ความสูงของ row
                $sheet->getRowDimension(1)->setRowHeight(25);
                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }

                // ฟิลเตอร์เฉพาะ I
                $sheet->setAutoFilter("B1:{$highestCol}{$highestRow}");

                // freeze header
                $sheet->freezePane('A2');

                // สี sheet
                $sheet->getTabColor()->setRGB('ffdaa2');

                // format comma
                $numberColumns = $this->moneyColumns();

                foreach ($numberColumns as $col) {
                    $sheet->getStyle("{$col}2:{$col}{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                }
            },
        ];
    }

    public function view(): View
    {
        return view('purchase-order.report.saleCar.booking.summary', [
            'sale' => $this->rows,
        ]);
    }
}
