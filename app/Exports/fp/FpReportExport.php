<?php

namespace App\Exports\fp;

use Carbon\Carbon;
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
    /** สีพื้นแถว "ประมาณการ" (ยังไม่ปิด FP) */
    private const ESTIMATE_FILL = 'fff2cc';

    protected array $rows;
    protected int $brand;
    protected ?Carbon $estimateTo;

    public function __construct(array $rows, int $brand, ?Carbon $estimateTo = null)
    {
        $this->rows       = $rows;
        $this->brand      = $brand;
        $this->estimateTo = $estimateTo;
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

                // จัดรูปแบบตัวเลขตาม "ชื่อหัวคอลัมน์" ไม่ใช่ตัวอักษรคอลัมน์ตายตัว
                $formats = [
                    'Net Amount' => '#,##0.00',
                    'Amount Due' => '#,##0.00',
                    'Rate'       => '0.00',
                    'No_Day'     => '#,##0',
                ];
                $lastColIndex = Coordinate::columnIndexFromString($highestCol);
                for ($i = 1; $i <= $lastColIndex; $i++) {
                    $letter = Coordinate::stringFromColumnIndex($i);
                    $header = $sheet->getCell("{$letter}1")->getValue();
                    if (isset($formats[$header])) {
                        $sheet->getStyle("{$letter}2:{$letter}{$highestRow}")
                            ->getNumberFormat()
                            ->setFormatCode($formats[$header]);
                    }
                }

                // ── แถว "ประมาณการ" (ยังไม่ปิด FP) : พื้นเหลือง + ตัวเอียง ──
                // ลำดับ $this->rows ตรงกับลำดับแถวใน view → แถว Excel = index + 2 (แถว 1 เป็นหัวตาราง)
                $estimatedRows = [];
                foreach ($this->rows as $i => $r) {
                    if (!empty($r['isEstimated'])) {
                        $estimatedRows[] = $i + 2;
                    }
                }

                foreach ($estimatedRows as $rowNo) {
                    $range = "A{$rowNo}:{$highestCol}{$rowNo}";
                    $sheet->getStyle($range)->getFill()
                        ->setFillType('solid')
                        ->getStartColor()->setRGB(self::ESTIMATE_FILL);
                    $sheet->getStyle($range)->getFont()->setItalic(true);
                }

                // คำอธิบายสัญลักษณ์ ใต้ตาราง (เว้น 1 บรรทัด) — ใส่หลังตีกรอบแล้วจะได้ไม่มีเส้นล้อม
                if ($estimatedRows) {
                    $noteRow = $highestRow + 2;
                    $sheet->setCellValue(
                        "A{$noteRow}",
                        '* แถวพื้นสีเหลือง = ยังไม่ปิด FP — ประมาณการดอกเบี้ยถึงวันสิ้นงวด'
                            . ($this->estimateTo ? ' ' . $this->estimateTo->format('d-m-Y') : '')
                    );
                    $sheet->getStyle("A{$noteRow}")->getFont()
                        ->setName('Angsana New')->setSize(14)->setItalic(true);
                    $sheet->getStyle("A{$noteRow}")->getFill()
                        ->setFillType('solid')
                        ->getStartColor()->setRGB(self::ESTIMATE_FILL);
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
