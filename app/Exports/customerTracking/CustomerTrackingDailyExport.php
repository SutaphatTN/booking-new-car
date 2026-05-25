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

class CustomerTrackingDailyExport implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    public function __construct(protected string $date) {}

    public function title(): string
    {
        return 'รายงานประจำวัน';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'BDD7EE']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
            2 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DDEEFF']],
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
                $sheet->getRowDimension(2)->setRowHeight(25);
                for ($row = 3; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }

                $sheet->setAutoFilter("A2:{$highestCol}2");
                $sheet->freezePane('A3');
                $sheet->getTabColor()->setRGB('BDD7EE');
            },
        ];
    }

    public function view(): View
    {
        $user = Auth::user();

        $details = CustomerTrackingDetail::with([
            'tracking.customer.prefix',
            'tracking.model',
            'tracking.subModel',
            'decision',
        ])
            ->whereHas('tracking', fn($q) => $q->where('brand', $user->brand))
            ->where('UserInsert', $user->id)
            ->whereDate('contact_date', $this->date)
            ->orderBy('contact_date')
            ->orderBy('id')
            ->get();

        $no = 1;
        $rows = $details->map(function ($d) use (&$no) {
            $tracking  = $d->tracking;
            $customer  = $tracking?->customer;
            $fullName  = $customer
                ? trim(($customer->prefix->Name_TH ?? '') . ' ' . $customer->FirstName . ' ' . $customer->LastName)
                : '-';

            $model    = $tracking?->model?->Name_TH ?? '-';
            $subModel = $tracking?->subModel?->name ?? '-';
            $carInfo  = $model . ($subModel !== '-' ? ' / ' . $subModel : '');

            return [
                'no'             => $no++,
                'full_name'      => $fullName,
                'car_info'       => $carInfo,
                'test_date'      => $tracking?->format_test_drive_date ?? '-',
                'test_note'      => $tracking?->test_drive_note ?? '-',
                'contact_date'   => $d->contact_date ? Carbon::parse($d->contact_date)->format('d/m/Y') : '-',
                'decision'       => $d->decision?->name ?? '-',
                'contact_status' => $d->contact_status ? 'ติดต่อได้' : 'ติดต่อไม่ได้',
                'comment'        => $d->comment_sale ?? '-',
            ];
        });

        return view('customer-tracking.excel-daily', [
            'rows'          => $rows,
            'dateFormatted' => Carbon::parse($this->date)->format('d/m/Y'),
            'userName'      => $user->name,
        ]);
    }
}
