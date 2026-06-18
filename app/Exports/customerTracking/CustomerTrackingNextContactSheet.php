<?php

namespace App\Exports\customerTracking;

use App\Models\CustomerTracking;
use App\Models\CustomerTrackingDetail;
use App\Models\Salecar;
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

class CustomerTrackingNextContactSheet implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    public function __construct(protected string $month) {}

    public function title(): string
    {
        return 'ติดตามต่อ';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FCE4D6']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
            2 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FCE4D6']],
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
                $sheet->getTabColor()->setRGB('FCE4D6');
            },
        ];
    }

    public function view(): View
    {
        $user      = Auth::user();
        $monthDate = Carbon::parse($this->month . '-01');
        $start     = $monthDate->copy()->startOfMonth()->toDateString();
        $end       = $monthDate->copy()->endOfMonth()->toDateString();

        // ลูกค้าที่มีใบจอง active แล้ว → ไม่ต้องติดตามต่อ
        $bookedCustomerIds = Salecar::whereNull('deleted_at')
            ->whereIn('con_status', [1, 2, 3, 4, 6])
            ->where('brand', $user->brand)
            ->pluck('CusID');

        // tracking ที่ยัง active (ไม่ยกเลิก, ลูกค้ายังไม่มีใบจอง active) ในแบรนด์นี้
        $validTrackingIds = CustomerTracking::where('brand', $user->brand)
            ->whereNull('cancelled_at')
            ->whereNotIn('customer_id', $bookedCustomerIds)
            ->pluck('id');

        // [เก่า] ดึง "ทุกนัด" (manager) ที่ตกในเดือนที่เลือก → 1 แถวต่อ 1 นัด (ลูกค้า 1 คนอาจมีหลายนัดในเดือน)
        // เก็บไว้เผื่อใช้
        // $details = CustomerTrackingDetail::with([
        //     'tracking.customer.prefix',
        //     'tracking.sale',
        //     'tracking.source',
        //     'decision',
        // ])
        //     ->whereIn('tracking_id', $validTrackingIds)
        //     ->where('entry_type', 'manager')
        //     ->whereBetween('contact_date', [$start, $end])
        //     ->orderBy('contact_date')
        //     ->orderBy('tracking_id')
        //     ->get();

        // [ใหม่] ดึงทุกนัด (manager) ในเดือนที่ยังไม่ได้กรอก (สถานะการติดต่อ หรือ หมายเหตุ ว่างช่องใดช่องหนึ่ง)
        $details = CustomerTrackingDetail::with([
            'tracking.customer.prefix',
            'tracking.sale',
            'tracking.source',
            'decision',
        ])
            ->whereIn('tracking_id', $validTrackingIds)
            ->where('entry_type', 'manager')
            ->whereBetween('contact_date', [$start, $end])
            ->where(function ($q) {
                $q->whereNull('contact_status')
                    ->orWhereNull('comment_sale')
                    ->orWhere('comment_sale', '');
            })
            ->orderBy('contact_date')
            ->orderBy('tracking_id')
            ->get();

        $rows = $details->map(function ($d) {
            $tracking = $d->tracking;
            $customer = $tracking?->customer;
            $fullName = $customer
                ? trim(($customer->prefix->Name_TH ?? '') . ' ' . $customer->FirstName . ' ' . $customer->LastName)
                : '-';

            return [
                'created_at'        => $tracking?->created_at ? Carbon::parse($tracking->created_at)->format('d/m/Y H:i') : '-',
                'full_name'         => $fullName,
                'sale'              => $tracking?->sale?->name ?? '-',
                'source'            => $tracking?->source?->name ?? '-',
                'next_contact_date' => $d->contact_date ? Carbon::parse($d->contact_date)->format('d/m/Y') : '-',
                'decision'          => $d->decision?->name ?? '-',
                'contact_status'    => is_null($d->contact_status) ? '-' : ($d->contact_status ? 'ติดต่อได้' : 'ติดต่อไม่ได้'),
                'comment'           => $d->comment_sale ?? '-',
            ];
        })
        ->values()
        ->map(function ($r, $i) {
            return ['no' => $i + 1] + $r;
        });

        return view('customer-tracking.excel-next-contact', [
            'rows'          => $rows,
            'dateFormatted' => $monthDate->format('m/Y'),
        ]);
    }
}
