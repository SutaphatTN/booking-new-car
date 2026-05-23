<?php

namespace App\Exports\customerTracking;

use App\Models\CustomerTrackingDetail;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

class CustomerTrackingByDateExport implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    public function __construct(
        protected string $dateFrom,
        protected string $dateTo
    ) {}

    public function title(): string
    {
        return 'รายงานการกรอกข้อมูล';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'C8E6C9']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
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

                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getFont()->setName('Angsana New')->setSize(14);

                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->setColor(new Color(Color::COLOR_BLACK));

                $sheet->getRowDimension(1)->setRowHeight(25);
                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }

                $sheet->setAutoFilter("A1:{$highestCol}1");
                $sheet->freezePane('A2');
                $sheet->getTabColor()->setRGB('C8E6C9');
            },
        ];
    }

    public function view(): View
    {
        $user = Auth::user();

        $details = CustomerTrackingDetail::with([
            'tracking.customer.prefix',
            'tracking.sale',
            'decision',
            'insertedBy',
        ])
            ->whereHas('tracking', fn($q) => $q->where('brand', $user->brand))
            ->whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo)
            ->orderBy('created_at')
            ->get();

        $no = 1;
        $rows = $details->map(function ($d) use (&$no) {
            $tracking  = $d->tracking;
            $customer  = $tracking?->customer;
            $fullName  = $customer
                ? trim(($customer->prefix->Name_TH ?? '') . ' ' . $customer->FirstName . ' ' . $customer->LastName)
                : '-';

            return [
                'no'             => $no++,
                'created_at'     => $d->created_at?->format('d/m/Y H:i'),
                'full_name'      => $fullName,
                'sale'           => $tracking?->sale?->name ?? '-',
                'inserted_by'    => $d->insertedBy?->name ?? '-',
                'entry_type'     => $d->entry_type === 'sale' ? 'เซลล์' : 'ผู้จัดการ',
                'contact_date'   => $d->contact_date ?? '-',
                'contact_status' => $d->contact_status ? 'ติดต่อได้' : 'ติดต่อไม่ได้',
                'decision'       => $d->decision?->name ?? '-',
                'comment'        => $d->comment_sale ?? '-',
                'test_date'      => $tracking->format_test_drive_date ?? '-',
                'test_note'      => $tracking->test_drive_note ?? '-',
            ];
        });

        return view('customer-tracking.excel-by-date', [
            'rows'             => $rows,
            'dateFromFormatted' => Carbon::parse($this->dateFrom)->format('d/m/Y'),
            'dateToFormatted'   => Carbon::parse($this->dateTo)->format('d/m/Y'),
        ]);
    }
}
