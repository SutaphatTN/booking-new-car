<?php

namespace App\Exports\license;

use App\Models\LicensePlateLoan;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

class LoanLicSheet implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    protected $brandId;
    protected $brandName;

    public function __construct($brandId, $brandName)
    {
        $this->brandId = $brandId;
        $this->brandName = $brandName;
    }

    public function title(): string
    {
        return $this->brandName;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => 'f5b7b1'],
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

                // freeze header
                $sheet->freezePane('A2');

                // สี sheet
                $sheet->getTabColor()->setRGB('f5b7b1');
            },
        ];
    }

    public function view(): View
    {
        // ประวัติยืม-คืนทั้งหมดของป้ายที่แบรนด์นี้เป็นเจ้าของ (ไม่กรองตาม user)
        $rows = LicensePlateLoan::with([
            'plate' => fn($q) => $q->withoutGlobalScope('brandAccess'),
            'borrowedByUser',
            'returnedByUser',
        ])
            ->where('owner_brand', $this->brandId)
            ->orderByDesc('borrow_date')
            ->orderByDesc('id')
            ->get();

        $brandNames = config('brand.names', []);

        $data = $rows->map(function ($r) use ($brandNames) {
            return [
                'number' => $r->plate?->number ?? '-',
                'borrower' => $brandNames[$r->borrower_brand] ?? '-',
                'borrow_date' => $r->format_borrow_date ?? '-',
                'return_date' => $r->format_return_date ?? '-',
                'status' => $r->return_date ? 'คืนแล้ว' : 'ยืมอยู่',
                'borrowed_by' => $r->borrowedByUser?->name ?? '-',
                'returned_by' => $r->returnedByUser?->name ?? '-',
                'note' => $r->note ?? '-',
            ];
        });

        return view('number_register.license.report.loan', [
            'loans' => $data,
        ]);
    }
}
