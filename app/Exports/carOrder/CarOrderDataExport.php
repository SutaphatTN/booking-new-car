<?php

namespace App\Exports\carOrder;

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

/**
 * รายงานข้อมูลรถ (Car Order) — ตัดสถานะรถ Delivered ออก
 * กรองตามรุ่นหลัก/รุ่นย่อยที่เลือก (ว่าง = ทั้งหมด) — brand-scope อัตโนมัติผ่าน UserAccessScope
 */
class CarOrderDataExport implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    protected $modelId;
    protected $subModelId;

    public function __construct($modelId = null, $subModelId = null)
    {
        $this->modelId    = $modelId ?: null;
        $this->subModelId = $subModelId ?: null;
    }

    public function title(): string
    {
        return 'ข้อมูลรถ';
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

                // จัดรูปแบบเงินคอลัมน์ ราคาทุน/RI/WS โดยหาจากหัวตาราง
                // (ตำแหน่งคอลัมน์เลื่อนตาม brand: Option/RI/WS เฉพาะ brand 1, สีภายใน เฉพาะ brand 2)
                $lastColIndex = Coordinate::columnIndexFromString($highestCol);
                for ($i = 1; $i <= $lastColIndex; $i++) {
                    $letter = Coordinate::stringFromColumnIndex($i);
                    if (in_array($sheet->getCell("{$letter}1")->getValue(), ['ราคาทุน', 'RI', 'WS'], true)) {
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
        $rows = CarOrder::with(['model', 'subModel', 'purchaseType', 'orderStatus', 'gwmColor', 'interiorColor'])
            ->where('status', 'finished')
            // ตัดรถที่ส่งมอบแล้วออก (null-safe: รถที่ car_status ยังว่างต้องไม่หลุด)
            ->where(fn($q) => $q->where('car_status', '!=', 'Delivered')->orWhereNull('car_status'))
            ->when($this->modelId, fn($q) => $q->where('model_id', $this->modelId))
            ->when($this->subModelId, fn($q) => $q->where('subModel_id', $this->subModelId))
            ->orderBy('model_id')
            ->orderBy('subModel_id')
            ->get();

        return view('car-order.report.data', ['rows' => $rows]);
    }
}
