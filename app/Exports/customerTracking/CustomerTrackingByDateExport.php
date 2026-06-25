<?php

namespace App\Exports\customerTracking;

use App\Models\CustomerTracking;
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
            2 => [
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
                $sheet->getRowDimension(2)->setRowHeight(25);
                for ($row = 3; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }

                $sheet->setAutoFilter("A2:{$highestCol}2");
                $sheet->freezePane('A3');
                $sheet->getTabColor()->setRGB('C8E6C9');
            },
        ];
    }

    public function view(): View
    {
        $user = Auth::user();

        $trackingIds = CustomerTracking::where('brand', $user->brand)
            ->whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo)
            ->pluck('id');

        $firstDetailIds = CustomerTrackingDetail::whereIn('tracking_id', $trackingIds)
            ->selectRaw('MIN(id) as id')
            ->groupBy('tracking_id')
            ->pluck('id');

        $details = CustomerTrackingDetail::with([
            'tracking.customer.prefix',
            'tracking.sale',
            'tracking.source',
            'tracking.model',
            'tracking.subModel',
            'tracking.wuColor',
            'tracking.interiorColor',
            'decision',
            'insertedBy',
        ])
            ->whereIn('id', $firstDetailIds)
            ->orderBy('id')
            ->get();

        $no = 1;
        $rows = $details->map(function ($d) use (&$no) {
            $tracking  = $d->tracking;
            $customer  = $tracking?->customer;
            $fullName  = $customer
                ? trim(($customer->prefix->Name_TH ?? '') . ' ' . $customer->FirstName . ' ' . $customer->LastName)
                : '-';

            // ข้อมูลรถ — บาง brand ใช้ field ต่างกัน (สี/สีภายใน/option)
            $brand = $tracking?->brand;
            $color = $brand == 1
                ? ($tracking?->color_text ?? '-')          // Mitsubishi: สีเป็น text อิสระ
                : ($tracking?->wuColor?->name ?? '-');     // GWM / Wuling: เลือกจากรายการสี

            return [
                'no'             => $no++,
                'created_at'     => $d->created_at?->format('d/m/Y H:i'),
                'full_name'      => $fullName,
                'sale'           => $tracking?->sale?->name ?? '-',
                'source'         => $tracking?->source?->name ?? '-',
                'model'          => $tracking?->model?->Name_TH ?? '-',
                'sub_model'      => $tracking?->subModel?->name ?? '-',
                'color'          => $color,
                'year'           => $tracking?->year ?? '-',
                'interior_color' => $tracking?->interiorColor?->name ?? '-', // ใช้เฉพาะ brand 2 (ดู $showInterior)
                'option'         => $tracking?->option ?? '-',               // ใช้เฉพาะ brand 1 (ดู $showOption)
                'inserted_by'    => $d->insertedBy?->name ?? '-',
                'entry_type'     => $d->entry_type === 'sale' ? 'เซลล์' : 'ผู้จัดการ',
                'contact_date'   => $d->contact_date ?? '-',
                'contact_status' => is_null($d->contact_status) ? '-' : ($d->contact_status ? 'ติดต่อได้' : 'ติดต่อไม่ได้'),
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
            // คุมการแสดงคอลัมน์ตาม brand: สีภายใน = GWM(2), Option = Mitsubishi(1), Wuling(3) ไม่มีทั้งคู่
            'showInterior'     => $user->brand == 2,
            'showOption'       => $user->brand == 1,
        ]);
    }
}
