<?php

namespace App\Exports\commission\Concerns;

use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * หน้าตาชีทของรายงานค่าคอมฝ่ายสนับสนุน — ให้เหมือนรายงานค่าคอมฝ่ายขาย
 * (หัวเขียว, Angsana New 14, เส้นขอบทุกช่อง, freeze หัวตาราง, แถว Total ตัวหนา)
 *
 * คลาสที่ใช้ต้องมี moneyCols() คืนตัวอักษรคอลัมน์ที่เป็นเงิน เช่น ['D','E','F']
 */
trait StaffSheetStyle
{
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '92d050']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
        ];
    }

    public function registerEvents(): array
    {
        $moneyCols = $this->moneyCols();

        return [
            AfterSheet::class => function (AfterSheet $event) use ($moneyCols) {

                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();

                $range = "A1:{$highestCol}{$highestRow}";
                $sheet->getStyle($range)->getFont()->setName('Angsana New')->setSize(14);
                $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle($range)->getAlignment()->setWrapText(true);
                $sheet->getStyle($range)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)->setColor(new Color(Color::COLOR_BLACK));

                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->freezePane('A2');
                $sheet->getTabColor()->setRGB('92d050');

                foreach ($moneyCols as $col) {
                    $sheet->getStyle("{$col}2:{$col}{$highestRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                }

                // แถวสุดท้าย = Total (มีเสมอเมื่อมีข้อมูล — ดู buildRows ของแต่ละชีท)
                if ($highestRow > 1) {
                    $sheet->getStyle("A{$highestRow}:{$highestCol}{$highestRow}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$highestRow}:{$highestCol}{$highestRow}")->getFill()
                        ->setFillType('solid')->getStartColor()->setRGB('e2efda');
                }
            },
        ];
    }
}
