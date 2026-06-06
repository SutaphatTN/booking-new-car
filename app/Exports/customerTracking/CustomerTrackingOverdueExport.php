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

class CustomerTrackingOverdueExport implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    public function __construct(protected string $month) {}

    public function title(): string
    {
        return 'เลยกำหนดติดตาม';
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
        $user       = Auth::user();
        $today      = Carbon::today();
        $monthDate  = Carbon::parse($this->month . '-01');
        $year       = $monthDate->year;
        $month      = $monthDate->month;

        $trackingIds = CustomerTracking::where('brand', $user->brand)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereDoesntHave('details', function ($q) {
                $q->where('entry_type', 'manager')
                  ->whereNotNull('comment_sale')
                  ->whereNotIn('id', function ($sub) {
                      $sub->from('customer_tracking_details')
                          ->selectRaw('MIN(id)')
                          ->groupBy('tracking_id');
                  });
            })
            ->pluck('id');

        $firstDetailIds = CustomerTrackingDetail::whereIn('tracking_id', $trackingIds)
            ->selectRaw('MIN(id) as id')
            ->groupBy('tracking_id')
            ->pluck('id');

        $details = CustomerTrackingDetail::with([
            'tracking.customer.prefix',
            'tracking.sale',
            'tracking.source',
            'decision',
            'insertedBy',
        ])
            ->whereIn('id', $firstDetailIds)
            ->orderByRaw('(SELECT created_at FROM customer_trackings WHERE id = tracking_id) ASC')
            ->get();

        $rows = $details->map(function ($d) use ($today) {
            $tracking  = $d->tracking;
            $customer  = $tracking?->customer;
            $fullName  = $customer
                ? trim(($customer->prefix->Name_TH ?? '') . ' ' . $customer->FirstName . ' ' . $customer->LastName)
                : '-';

            $createdAt = $tracking?->created_at ? Carbon::parse($tracking->created_at) : null;
            $days      = $createdAt ? abs((int) $today->diffInDays($createdAt->copy()->startOfDay())) : 0;

            return [
                'created_at'  => $createdAt?->format('d/m/Y H:i') ?? '-',
                'days'        => $days,
                'full_name'   => $fullName,
                'sale'        => $tracking?->sale?->name ?? '-',
                'source'      => $tracking?->source?->name ?? '-',
                'inserted_by' => $d->insertedBy?->name ?? '-',
                'entry_type'  => $d->entry_type === 'sale' ? 'เซลล์' : 'ผู้จัดการ',
                'contact_date'=> $d->contact_date ?? '-',
                'decision'    => $d->decision?->name ?? '-',
                'comment'     => $d->comment_sale ?? '-',
            ];
        })
        ->filter(fn($r) => $r['days'] >= 1)
        ->values()
        ->map(function ($r, $i) {
            return ['no' => $i + 1] + $r;
        });

        return view('customer-tracking.excel-overdue', [
            'rows'          => $rows,
            'dateFormatted' => $monthDate->format('m/Y'),
        ]);
    }
}
