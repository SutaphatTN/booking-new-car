<?php

namespace App\Exports\carOrder\Concerns;

use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * รูปแบบชีทมาตรฐานของรายงานฝั่งข้อมูลรถ (Angsana New 14 / หัวตารางเขียว / freeze หัว / กรอบทุกช่อง)
 * ยกมาจาก CarOrderDataExport เพื่อไม่ต้องก๊อปทั้งก้อนซ้ำในทุกชีทของรายงานหลายชีท
 *
 * คอลัมน์เงินหาจาก "ข้อความหัวตาราง" ไม่ใช่ตำแหน่งคอลัมน์ตายตัว
 * เพราะลำดับคอลัมน์ขยับตาม brand (Option/สีภายใน) และ role (manager ไม่เห็นราคาทุน)
 */
trait CarOrderSheetStyle
{
    /** สีหัวตาราง + สี tab — ชีทไหนอยากเปลี่ยนให้ override เมธอดนี้ */
    protected function headerColor(): string
    {
        return 'c6efce';
    }

    /** หัวตารางที่ต้องจัดเป็นรูปแบบเงิน */
    protected function moneyHeaders(): array
    {
        return ['ราคาทุน', 'ราคาขาย'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => 'solid',
                    'startColor' => ['rgb' => $this->headerColor()],
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
                $sheet->getTabColor()->setRGB($this->headerColor());

                $lastColIndex = Coordinate::columnIndexFromString($highestCol);
                for ($i = 1; $i <= $lastColIndex; $i++) {
                    $letter = Coordinate::stringFromColumnIndex($i);
                    if (in_array($sheet->getCell("{$letter}1")->getValue(), $this->moneyHeaders(), true)) {
                        $sheet->getStyle("{$letter}2:{$letter}{$highestRow}")
                            ->getNumberFormat()
                            ->setFormatCode('#,##0.00');
                    }
                }
            },
        ];
    }
}
