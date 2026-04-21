<?php

namespace App\Exports\customerTracking;

use App\Models\CustomerTracking;
use App\Models\Salecar;
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

class CustomerTrackingExport implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    public function title(): string
    {
        return 'รายงานการติดตามลูกค้า';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => 'C8E6C9'],
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
                $sheet = $event->sheet->getDelegate();
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
                $sheet->getTabColor()->setRGB('C8E6C9');
            },
        ];
    }

    public function view(): View
    {
        // กรอง customer_id ที่มีการจองแล้ว (ยังไม่ถูก soft-delete และยังไม่ถอนจอง ยังไม่ส่งมอบ)
        $bookedCustomerIds = Salecar::whereNull('deleted_at')
            ->whereNull('CancelDate')
            ->whereNull('DeliveryDate')
            ->pluck('CusID')
            ->unique()
            ->toArray();

        $trackings = CustomerTracking::with([
            'customer.prefix',
            'sale',
            'source',
            'model',
            'subModel',
            'latestDetail.decision',
            // 'details.decision', // ใช้แทน latestDetail เมื่อต้องการแสดงทุก detail
            'wuColor',
        ])
        ->whereNotIn('customer_id', $bookedCustomerIds)
        ->get();

        $no = 1;
        $rows = $trackings->map(function ($t) use (&$no) {
            $customer = $t->customer;
            $fullName = $customer
                ? trim(($customer->prefix->Name_TH ?? '') . ' ' . $customer->FirstName . ' ' . $customer->LastName)
                : '-';

            $latestDetail = $t->latestDetail;

            return [
                'no'             => $no++,
                'full_name'      => $fullName,
                'model'          => $t->model->Name_TH ?? '-',
                'sub_model'      => $t->subModel->name ?? '-',
                'color'          => $t->wuColor->name ?? '-',
                'sale'           => $t->sale->name ?? '-',
                'source'         => $t->source->name ?? '-',
                'contact_date'   => $latestDetail?->contact_date ?? '-',
                'contact_status' => $latestDetail
                    ? ($latestDetail->contact_status ? 'ติดต่อได้' : 'ติดต่อไม่ได้')
                    : '-',
                'decision'       => $latestDetail?->decision?->name ?? '-',
                'comment'        => $latestDetail?->comment_sale ?? '-',
            ];
        });

        /*
         * แสดงทุก detail เรียงจากใหม่ไปเก่า (1 tracking = หลาย row)
         *
         * $rows = collect();
         * $no = 1;
         * foreach ($trackings as $t) {
         *     $customer = $t->customer;
         *     $fullName = $customer
         *         ? trim(($customer->prefix->Name_TH ?? '') . ' ' . $customer->FirstName . ' ' . $customer->LastName)
         *         : '-';
         *
         *     $details = $t->details->sortByDesc('contact_date');
         *
         *     foreach ($details as $d) {
         *         $rows->push([
         *             'no'             => $no++,
         *             'full_name'      => $fullName,
         *             'model'          => $t->model->Name_TH ?? '-',
         *             'sub_model'      => $t->subModel->name ?? '-',
         *             'color'          => $t->wuColor->name ?? '-',
         *             'sale'           => $t->sale->name ?? '-',
         *             'source'         => $t->source->name ?? '-',
         *             'contact_date'   => $d->contact_date ?? '-',
         *             'contact_status' => $d->contact_status ? 'ติดต่อได้' : 'ติดต่อไม่ได้',
         *             'decision'       => $d->decision->name ?? '-',
         *             'comment'        => $d->comment_sale ?? '-',
         *         ]);
         *     }
         *
         *     if ($details->isEmpty()) {
         *         $rows->push([
         *             'no'             => $no++,
         *             'full_name'      => $fullName,
         *             'model'          => $t->model->Name_TH ?? '-',
         *             'sub_model'      => $t->subModel->name ?? '-',
         *             'color'          => $t->wuColor->name ?? '-',
         *             'sale'           => $t->sale->name ?? '-',
         *             'source'         => $t->source->name ?? '-',
         *             'contact_date'   => '-',
         *             'contact_status' => '-',
         *             'decision'       => '-',
         *             'comment'        => '-',
         *         ]);
         *     }
         * }
         * ต้องเปลี่ยน eager load บนด้านบน: 'latestDetail.decision' → 'details.decision'
         */

        return view('customer-tracking.excel', ['rows' => $rows]);
    }
}
