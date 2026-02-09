<?php

namespace App\Exports;

use App\Models\CarOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
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
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BookingSummarySheet implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize, WithColumnFormatting
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function title(): string
    {
        return 'สต็อกรถรวม';
    }

    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'F' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            //แถวบนสุด
            1 => [
                'font' => [],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => 'd8e4bc'],
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

                // ไม่เอา filter ที่ no
                $sheet->setAutoFilter("B1:{$highestCol}{$highestRow}");

                // freeze header
                $sheet->freezePane('A2');

                // สี sheet
                $sheet->getTabColor()->setRGB('2F75B5');
            },
        ];
    }

    public function view(): View
    {
        $carOrders = CarOrder::query()
            ->with([
                'model',
                'subModel',
                'orderStatus',
                'salecars.customer.prefix',
                'salecars.saleUser',
                'salecars.carOrderHistories',
                'salecars.remainingPayment',
                'salecars.conStatus',
            ])
            ->whereIn('status', ['approved', 'finished'])
            ->whereIn('purchase_type', ['2'])
            ->whereNot('car_status', 'Delivered')
            ->get()

            ->sortBy([
                fn($o) => $o->model->Name_TH ?? '',
                fn($o) => $o->subModel->detail ?? '',
                fn($o) => $o->subModel->name ?? '',
                fn($o) => $o->color ?? '',
                fn($o) => $o->year ?? '',
                fn($o) => $o->option ?? '',
            ])

            //reset index
            ->values();

        $data = collect();
        $no = 1;

        foreach ($carOrders as $order) {

            $sale = $order->salecars
                ->first(fn($s) => !in_array($s->con_status, [5, 9]));

            //เงื่อนไข การจัดสรรรถใหม่
            $changedAt = $sale?->carOrderHistories?->changed_at
                ? Carbon::parse($sale->carOrderHistories->changed_at)->startOfDay()
                : null;

            $daysBindInt = $changedAt
                ? $changedAt->diffInDays(now()->startOfDay())
                : null;

            $allocationDate = $changedAt
                ? $changedAt->copy()->addDays(7)->format('d-m-Y')
                : '';

            $allocationStatus = '';
            if ($daysBindInt !== null && $daysBindInt > 7) {
                $allocationStatus = 'จัดสรรใหม่';
            }

            $data->push([
                'No'         => $no++,

                'model'      => $order->model->Name_TH ?? '-',
                'subModel'   => $order->subModel
                    ? $order->subModel->detail . ' - ' . $order->subModel->name
                    : '-',

                'color'      => $order->color ?? '-',
                'year'       => $order->year ?? '-',
                'option'     => $order->option ?? '-',
                'car_MSRP' => $order->car_MSRP ?? null,
                'purchase_type'     => $order->purchaseType->name ?? '-',
                'order_status'     => $order->orderStatus->name ?? '-',
                'system_date'     => $order->format_system_date ?? '-',
                'estimated_stock_date'     => $order->format_estimated_stock_date ?? '-',
                'vin_number' => $order->vin_number ?? '-',
                'j_number'   => $order->j_number ?? '-',

                'order_stock_date'   => $order->format_order_stock_date ?? '-',
                'aging_date' => $order->order_stock_date
                    ? Carbon::parse($order->order_stock_date)
                    ->startOfDay()
                    ->diffInDays(now()->startOfDay()) . ' วัน'
                    : '-',

                'customer'   => $sale
                    ? $sale->customer->prefix->Name_TH ?? '' . ' '
                    . $sale->customer->FirstName ?? '' . ' '
                    . $sale->customer->LastName ?? ''
                    : '',
                'sale'        => $sale?->saleUser?->name ?? '',
                'bookingDate' => $sale?->format_booking_date ?? '',
                'status'      => $sale?->conStatus?->name ?? '',
                'daysBind' => $sale && $sale->carOrderHistories?->changed_at
                    ? Carbon::parse($sale->carOrderHistories->changed_at)
                    ->startOfDay()
                    ->diffInDays(now()->startOfDay()) . ' วัน'
                    : '',
                'po_date'      => $sale?->format_po_date ?? '',

                'allocation_status' => $allocationStatus,
                'allocation_date' => $allocationDate,

                // 'statusCar' => $sale ? 'ผูกรถแล้ว' : 'รถว่าง',
                // 1 คัน = 1 แถว เสมอ
                // 'count'      => 1,

            ]);
        }

        return view('purchase-order.report.booking', [
            'saleCar' => $data
        ]);
    }
}
