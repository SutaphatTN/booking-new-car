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
 * 1 brand = 1 sheet — รายชื่อลูกค้าที่ทดลองขับในเดือนนั้น
 * $trackings ดึงมาแล้วจาก CustomerTrackingTestDriveReport (ตามสิทธิ์ของ brand นี้)
 */
class CustomerTrackingTestDriveSheet implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    public function __construct(
        protected string $month,
        protected int $brand,
        protected Collection $trackings
    ) {}

    public function title(): string
    {
        return config("brand.names.{$this->brand}", 'Brand ' . $this->brand);
    }

    public function styles(Worksheet $sheet)
    {
        $header = [
            'font'      => ['bold' => true],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E2EFDA']],
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

                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(25);
                for ($row = 3; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }

                $sheet->setAutoFilter("A2:{$highestCol}2");
                $sheet->freezePane('A3');
                $sheet->getTabColor()->setRGB('E2EFDA');
            },
        ];
    }

    public function view(): View
    {
        $rows = $this->trackings->values()->map(function ($t, $i) {
            $customer = $t->customer;

            return [
                'no'          => $i + 1,
                'test_drive'  => $t->test_drive_date ? Carbon::parse($t->test_drive_date)->format('d/m/Y') : '-',
                'branch'      => $t->branchInfo?->name ?? '-',
                'full_name'   => $customer
                    ? trim(($customer->prefix->Name_TH ?? '') . ' ' . $customer->FirstName . ' ' . $customer->LastName)
                    : '-',
                'phone'       => $customer?->formatted_mobile ?: '-',
                'sale'        => $t->sale?->name ?? '-',
                'model'       => $t->model?->Name_TH ?? '-',
                'sub_model'   => trim(($t->subModel?->name ?? '') . ' ' . ($t->subModel?->detail ?? '')) ?: '-',
                'source'      => $t->source?->name ?? '-',
                'status'      => $t->statusLabel(),
                'note'        => $t->test_drive_note ?: '-',
                'files'       => is_array($t->test_drive_attachments) ? count($t->test_drive_attachments) : 0,
                'created_at'  => $t->created_at?->format('d/m/Y') ?? '-',
                'link'        => url('/customer-tracking/' . $t->id),
            ];
        });

        return view('customer-tracking.excel-test-drive', [
            'rows'       => $rows,
            'brandName'  => $this->title(),
            'monthLabel' => Carbon::parse($this->month . '-01')->format('m/Y'),
        ]);
    }
}
