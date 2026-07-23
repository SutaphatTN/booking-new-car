<?php

namespace App\Exports\dispose;

use App\Http\Controllers\floor_plan\FloorPlanController;
use App\Models\CarOrder;
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

class DisposeReportExport implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    protected $month;

    public function __construct($month = null)
    {
        $this->month = $month;
    }

    public function title(): string
    {
        return 'แจ้งจำหน่าย';
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

                // จัดรูปแบบเงินคอลัมน์ "ราคาทุน" (ตำแหน่งเลื่อนตาม brand)
                $lastColIndex = Coordinate::columnIndexFromString($highestCol);
                for ($i = 1; $i <= $lastColIndex; $i++) {
                    $letter = Coordinate::stringFromColumnIndex($i);
                    if ($sheet->getCell("{$letter}1")->getValue() === 'ราคาทุน') {
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
        // รายงานยึด "เดือนของวันที่รับ" — ไม่ยึดสถานะเบิก/ยังไม่เบิก และเอาเฉพาะรถที่มีวันที่รับ
        // ยึดจาก car_order (1 คัน = 1 แถว) เพราะเอกสารแจ้งจำหน่ายผูกกับรถ ไม่ใช่ใบจอง
        $query = CarOrder::with([
                'model', 'subModel', 'interiorColor', 'gwmColor',
                'salecars' => fn ($q) => $q->whereNotIn('con_status', [7, 8, 9])->with('customer'),
            ])
            ->whereNotNull('dispose_received_date');

        // เดือนตาม "วันที่รับ" (เฉพาะเดือนที่เลือก)
        if ($this->month) {
            [$y, $m] = array_pad(explode('-', $this->month), 2, null);
            if ($y && $m) {
                $query->whereYear('dispose_received_date', (int) $y)
                    ->whereMonth('dispose_received_date', (int) $m);
            }
        }

        $rows = $query->orderBy('dispose_received_date')
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->get();

        return view('floor-plan.dispose.report', [
            'rows'        => $rows,
            'disposeSets' => FloorPlanController::DISPOSE_SETS,
        ]);
    }
}
