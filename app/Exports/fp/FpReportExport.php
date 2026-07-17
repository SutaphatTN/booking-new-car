<?php

namespace App\Exports\fp;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

class FpReportExport implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    protected array $rows;
    protected int $brand;

    public function __construct(array $rows, int $brand)
    {
        $this->rows  = $rows;
        $this->brand = $brand;
    }

    public function title(): string
    {
        return 'รายการ FP';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => 'solid',
                    'startColor' => ['rgb' => 'c6efce'],
                ],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical'   => 'center',
                ],
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

                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getFont()
                    ->setName('Angsana New')
                    ->setSize(14);

                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->setColor(new Color(Color::COLOR_BLACK));

                $sheet->getRowDimension(1)->setRowHeight(25);
                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }

                $sheet->setAutoFilter("A1:{$highestCol}1");
                $sheet->freezePane('A2');
                $sheet->getTabColor()->setRGB('c6efce');

                // จัดรูปแบบเงิน คอลัมน์ "ราคาทุน" และ "รวมดอกเบี้ย"
                $lastColIndex = Coordinate::columnIndexFromString($highestCol);
                for ($i = 1; $i <= $lastColIndex; $i++) {
                    $letter = Coordinate::stringFromColumnIndex($i);
                    $header = $sheet->getCell("{$letter}1")->getValue();
                    if (in_array($header, ['ราคาทุน', 'รวมดอกเบี้ย'], true)) {
                        $sheet->getStyle("{$letter}2:{$letter}{$highestRow}")
                            ->getNumberFormat()
                            ->setFormatCode('#,##0.00');
                    }
                }
            },
        ];
    }

    public function view(): View
    {
        return view('floor-plan.fp.report', [
            'rows'  => $this->rows,
            'brand' => $this->brand,
        ]);
    }
}
