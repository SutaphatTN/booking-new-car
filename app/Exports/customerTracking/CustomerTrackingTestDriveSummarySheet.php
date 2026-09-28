<?php

namespace App\Exports\customerTracking;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * sheet แรกของรายงานทดลองขับ — จำนวนทดลองขับต่อเซลล์ แยก brand + แถวรวมท้ายตาราง
 * $byBrand = [brand id => Collection<CustomerTracking>] จาก CustomerTrackingTestDriveReport
 */
class CustomerTrackingTestDriveSummarySheet implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    public function __construct(protected string $month, protected array $byBrand) {}

    public function title(): string
    {
        return 'สรุปรวม';
    }

    public function styles(Worksheet $sheet)
    {
        $header = [
            'font'      => ['bold' => true],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => 'BBDEFB']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ];

        return [1 => $header, 2 => $header];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet      = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();

                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getFont()->setName('Angsana New')->setSize(14);

                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->setColor(new Color(Color::COLOR_BLACK));

                // แถวรวมท้ายตาราง
                $sheet->getStyle("A{$highestRow}:{$highestCol}{$highestRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$highestRow}:{$highestCol}{$highestRow}")
                    ->getFill()->setFillType('solid')->getStartColor()->setRGB('E3F2FD');

                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(25);
                for ($row = 3; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }

                $sheet->freezePane('A3');
                $sheet->getTabColor()->setRGB('BBDEFB');
            },
        ];
    }

    public function view(): View
    {
        $rows = collect();

        foreach ($this->byBrand as $brand => $trackings) {
            $brandName = config("brand.names.{$brand}", 'Brand ' . $brand);

            // แยกสาขาด้วย — GWM มี 2 สาขา เซลล์คนเดียวกันต้องไม่ถูกรวมข้ามสาขา
            $trackings->groupBy(fn($t) => $t->branch . '-' . $t->sale_id)
                ->map(function ($items) use ($brandName) {
                    $total  = $items->count();
                    $booked = $items->filter(fn($t) => $t->booked_at)->count();

                    return [
                        'brand'       => $brandName,
                        'branch'      => $items->first()->branchInfo?->name ?? '-',
                        'sale'        => $items->first()->sale?->name ?? '-',
                        'total'       => $total,
                        'booked'      => $booked,
                        'booked_rate' => number_format($booked * 100 / $total, 1) . '%',
                    ];
                })
                ->sortBy([['branch', 'asc'], ['total', 'desc']])
                ->each(fn($r) => $rows->push($r));
        }

        $rows = $rows->values()->map(fn($r, $i) => ['no' => $i + 1] + $r);

        $sumTotal  = $rows->sum('total');
        $sumBooked = $rows->sum('booked');

        return view('customer-tracking.excel-test-drive-summary', [
            'rows'       => $rows,
            'monthLabel' => Carbon::parse($this->month . '-01')->format('m/Y'),
            'sumTotal'   => $sumTotal,
            'sumBooked'  => $sumBooked,
            'sumRate'    => $sumTotal > 0 ? number_format($sumBooked * 100 / $sumTotal, 1) . '%' : '-',
        ]);
    }
}
