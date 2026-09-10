<?php

namespace App\Exports\source;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * รายงานเงินเคลม (Form B) — ข้อมูลชุดเดียวกับตารางบนหน้าจอตามตัวกรองที่เลือก
 *
 * แถวถูกดึงมาจาก controller แล้วส่งเข้ามาทาง constructor (ไม่ query ซ้ำในนี้)
 * เพื่อให้รายงานตรงกับที่เห็นบนจอเสมอ และไม่ต้องยิง query ชุดเดิมสองรอบ
 */
class SourcePlaceClaimExport implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    /** คอลัมน์เงิน (สำหรับจัดรูปแบบ #,##0.00) — ต้องตรงกับลำดับหัวตารางใน excel.blade.php */
    private const MONEY_COLS = ['G', 'H', 'J', 'K'];

    public function __construct(
        private Collection $places,
        private $branchNames,
    ) {
    }

    public function title(): string
    {
        return 'เงินเคลม';
    }

    public function view(): View
    {
        return view('source.claim.excel', [
            'places'        => $this->places,
            'branchNames'   => $this->branchNames,
            'statuses'      => config('source.claim_statuses', []),
            'checkStatuses' => config('source.claim_check_statuses', []),
            'sharePct'      => (int) (config('source.claim_share', 0.5) * 100),
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font'      => ['bold' => true],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => 'ffe0b2']],
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
                $range      = "A1:{$highestCol}{$highestRow}";

                $sheet->getStyle($range)->getFont()->setName('Angsana New')->setSize(14);
                $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle($range)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->setColor(new Color(Color::COLOR_BLACK));

                $sheet->getRowDimension(1)->setRowHeight(25);
                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }

                $sheet->setAutoFilter("A1:{$highestCol}1");
                $sheet->freezePane('A2');
                $sheet->getTabColor()->setRGB('ffe0b2');

                foreach (self::MONEY_COLS as $col) {
                    $sheet->getStyle("{$col}2:{$col}{$highestRow}")
                        ->getNumberFormat()->setFormatCode('#,##0.00');
                }

                // แถวสุดท้าย = แถวรวม (ทำตัวหนาไว้ให้อ่านง่าย) — มีเฉพาะตอนมีข้อมูล
                if ($this->places->isNotEmpty()) {
                    $sheet->getStyle("A{$highestRow}:{$highestCol}{$highestRow}")->getFont()->setBold(true);
                }
            },
        ];
    }
}
