<?php

namespace App\Exports\customerTracking;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
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
 * sheet แรกของรายงาน — สรุปจำนวนลูกค้าต่อสถานที่ พร้อมชื่อ tab ให้กดตามไปดูรายชื่อได้
 * $groups = trackings ที่จัดกลุ่มตาม place_id มาแล้วจาก CustomerTrackingOfflinePlaceReport
 */
class CustomerTrackingOfflinePlaceSummarySheet implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    public function __construct(
        protected string $month,
        protected Collection $groups,
        protected array $titles
    ) {}

    public function title(): string
    {
        return 'สรุปรวม';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font'      => ['bold' => true],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => 'BBDEFB']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
            2 => [
                'font'      => ['bold' => true],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => 'BBDEFB']],
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

                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getFont()->setName('Angsana New')->setSize(14);

                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->setColor(new Color(Color::COLOR_BLACK));

                // แถวรวมท้ายตาราง (มีเสมอเมื่อมีข้อมูล)
                if ($this->groups->isNotEmpty()) {
                    $sheet->getStyle("A{$highestRow}:{$highestCol}{$highestRow}")
                        ->getFont()->setBold(true);
                    $sheet->getStyle("A{$highestRow}:{$highestCol}{$highestRow}")
                        ->getFill()->setFillType('solid')->getStartColor()->setRGB('E3F2FD');
                }

                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(25);
                for ($row = 3; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }

                $sheet->setAutoFilter("A2:{$highestCol}2");
                $sheet->freezePane('A3');
                $sheet->getTabColor()->setRGB('BBDEFB');
            },
        ];
    }

    public function view(): View
    {
        $no   = 1;
        $rows = $this->groups->map(function ($trackings, $placeId) use (&$no) {
            $place = $trackings->first()->place;

            $range = '-';
            if ($place?->start_date) {
                $range = $place->start_date->format('d/m/Y');
                if ($place->end_date && $place->end_date->ne($place->start_date)) {
                    $range .= ' – ' . $place->end_date->format('d/m/Y');
                }
            }

            $total   = $trackings->count();
            $booked  = $trackings->filter(fn($t) => $t->booked_at)->count();
            $ongoing = $trackings->filter(fn($t) => !$t->booked_at && !$t->cancelled_at)->count();
            $ended   = $trackings->filter(fn($t) => !$t->booked_at && $t->cancelled_at && $t->end_type === 'finished')->count();
            $cancel  = $trackings->filter(fn($t) => !$t->booked_at && $t->cancelled_at && $t->end_type !== 'finished')->count();

            return [
                'no'          => $no++,
                'place'       => $place?->location ?: '-',
                'type'        => $place?->source?->name ?? ($trackings->first()->source?->name ?? '-'),
                'range'       => $range,
                'las_number'  => $place?->las_number ?: '-',
                'sheet'       => $this->titles[$placeId] ?? '-',
                'total'       => $total,
                'booked'      => $booked,
                'ongoing'     => $ongoing,
                'ended'       => $ended,
                'cancel'      => $cancel,
                'booked_rate' => $total > 0 ? number_format($booked * 100 / $total, 1) . '%' : '-',
            ];
        })->values();

        return view('customer-tracking.excel-offline-place-summary', [
            'rows'       => $rows,
            'monthLabel' => Carbon::parse($this->month . '-01')->format('m/Y'),
            'sumTotal'   => $rows->sum('total'),
            'sumBooked'  => $rows->sum('booked'),
            'sumOngoing' => $rows->sum('ongoing'),
            'sumEnded'   => $rows->sum('ended'),
            'sumCancel'  => $rows->sum('cancel'),
        ]);
    }
}
