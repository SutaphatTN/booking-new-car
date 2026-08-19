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

    /** pending = ยังไม่เบิก | withdrawn = เบิกแล้ว | null/อื่น ๆ = ทุกสถานะ */
    protected $status;

    public function __construct($month = null, $status = null)
    {
        $this->month  = $month;
        $this->status = $status;
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
        // รายงาน = สิ่งที่เห็นในตารางหน้าจอ (สถานะ + เดือนของวันที่รับ)
        // ยึดจาก car_order (1 คัน = 1 แถว) เพราะเอกสารแจ้งจำหน่ายผูกกับรถ ไม่ใช่ใบจอง
        $query = CarOrder::with([
                'model', 'subModel', 'interiorColor', 'gwmColor',
                'salecars' => fn ($q) => $q->whereNotIn('con_status', [7, 8, 9])->with('customer'),
            ]);

        // สถานะเบิก — ตรงกับตัวกรองในหน้าจอ (ไม่ส่งมา = ทุกสถานะ)
        if ($this->status === 'pending') {
            $query->whereNull('dispose_reg_withdraw_date');
        } elseif ($this->status === 'withdrawn') {
            $query->whereNotNull('dispose_reg_withdraw_date');
        }

        // เดือนตาม "วันที่รับ" — เลือกเดือน = ต้องมีวันที่รับและอยู่ในเดือนนั้น
        // ไม่เลือกเดือน = ไม่บังคับว่าต้องกรอกวันที่รับแล้ว (รถค้างเบิกที่ยังไม่กรอกต้องเห็นด้วย)
        $hasMonth = false;
        if ($this->month) {
            [$y, $m] = array_pad(explode('-', $this->month), 2, null);
            if ($y && $m) {
                $hasMonth = true;
                $query->whereYear('dispose_received_date', (int) $y)
                    ->whereMonth('dispose_received_date', (int) $m);
            }
        }

        // เลือกเดือน = ไล่วันที่รับจากต้นเดือนไปท้ายเดือน / ไม่เลือกเดือน = ใหม่ก่อน (คันที่ยังไม่กรอกวันที่รับตกท้ายตาราง)
        $query = $hasMonth
            ? $query->orderBy('dispose_received_date')
            : $query->orderByDesc('dispose_received_date');

        $rows = $query->orderByDesc('order_date')
            ->orderByDesc('id')
            ->get();

        return view('floor-plan.dispose.report', [
            'rows'        => $rows,
            'disposeSets' => FloorPlanController::DISPOSE_SETS,
        ]);
    }
}
