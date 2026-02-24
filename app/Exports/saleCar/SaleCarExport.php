<?php

namespace App\Exports\saleCar;

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

class SaleCarExport implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function title(): string
    {
        return 'สรุปข้อมูลการจอง';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            //แถวบนสุด
            1 => [
                'font' => [],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => 'be6aff'],
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

                // ฟิลเตอร์เฉพาะ I
                $sheet->setAutoFilter("B1:{$highestCol}{$highestRow}");

                // freeze header
                $sheet->freezePane('A2');

                // สี sheet
                $sheet->getTabColor()->setRGB('be6aff');
            },
        ];
    }

    public function view(): View
    {
        $rows = Salecar::with([
            'customer.prefix',
            'carOrder.model',
            'carOrder.subModel',
            'carOrder.orderStatus',
            'carOrder',
            'gwmColor',
            'interiorColor',
            'financeConfirm',
            'remainingPayment',
            'remainingPayment.financeInfo',
        ])
            ->get();

        $data = $rows->map(function ($r) {
            $customerName = trim(
                ($r->customer->prefix->Name_TH ?? '') . ' ' .
                    ($r->customer->FirstName ?? '') . ' ' .
                    ($r->customer->LastName ?? '')
            );

            $model = $r->carOrder->model->Name_TH ?? '-';
            $sub = $r->carOrder->subModel->name ?? '-';
            $detailModel = $r->carOrder->subModel->detail ?? '-';

            $subModel = "{$detailModel} - {$sub}";

            $color = $r->brand == 2
                ? ($r->gwmColor->name ?? '-')
                : ($r->color ?? '-');

            $interiorColor = $r->brand == 2
                ? ($r->interiorColor->name ?? '-')
                : null;

            return [
                'customer' => $customerName,
                'model' => $model,
                'subModel' => $subModel,
                'option'     => $r->option ?? '-',
                'color'      => $color,
                'interior_color' => $interiorColor,
                'year'       => $r->year ?? '-',
                'bookingDate' => $r?->format_booking_date ?? '',
                'name_fi'       => $r->remainingPayment->financeInfo->FinanceCompany ?? '-',
                'order_status' => $r->carOrder->orderStatus->name ?? '-',
                'contract_date' => $r?->remainingPayment->format_contract_date ?? '',
                'DeliveryEstimateDate' => $r?->format_delivery_estimate_date ?? '',
            ];
        });

        return view('purchase-order.report.saleCar.summary', [
            'sale' => $data
        ]);
    }
}
