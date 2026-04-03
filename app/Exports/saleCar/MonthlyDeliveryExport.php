<?php

namespace App\Exports\saleCar;

use App\Services\SaleBookingQuery;
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

class MonthlyDeliveryExport implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    protected $fromDate;
    protected $dateType;

    public function __construct($fromDate = null, $dateType = 'dms')
    {
        $this->fromDate = $fromDate ?? now()->startOfMonth()->format('Y-m');
        $this->dateType = $dateType;
    }

    public function title(): string
    {
        return 'รายงานส่งมอบประจำเดือน';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => 'a2d4ff'],
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

                $sheet->setAutoFilter("B1:{$highestCol}{$highestRow}");

                $sheet->freezePane('A2');

                $sheet->getTabColor()->setRGB('a2d4ff');

                $numberColumns = ['H', 'I', 'J'];
                foreach ($numberColumns as $col) {
                    $sheet->getStyle("{$col}2:{$col}{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                }
            },
        ];
    }

    public function view(): View
    {
        $date = Carbon::createFromFormat('Y-m', $this->fromDate)->startOfMonth();

        $month = $date->month;
        $year  = $date->year;

        $dateField = $this->dateType === 'ck' ? 'DeliveryInCKDate' : 'DeliveryInDMSDate';

        $rows = SaleBookingQuery::base()
            ->whereMonth($dateField, $month)
            ->whereYear($dateField, $year)
            ->where('con_status', 5)
            ->get();

        $data = $rows->map(function ($r) {
            $customerName = trim(
                ($r->customer->prefix->Name_TH ?? '') . ' ' .
                    ($r->customer->FirstName ?? '') . ' ' .
                    ($r->customer->LastName ?? '')
            );

            $model = $r->model->Name_TH ?? '-';
            $sub = $r->subModel->name ?? '-';
            $detailModel = $r->subModel->detail ?? null;

            $subModel = $detailModel
                ? "{$detailModel} - {$sub}"
                : $sub;

            $color = in_array($r->brand, [2, 3])
                ? ($r->gwmColor->name ?? '-')
                : ($r->Color ?? '-');

            $interiorColor = $r->brand == 2
                ? ($r->interiorColor->name ?? '-')
                : null;

            return [
                'customer'            => $customerName,
                'sale'                => $r->saleUser?->name ?? '-',
                'model'               => $model,
                'subModel'            => $subModel,
                'option'              => $r->option ?? '-',
                'color'               => $color,
                'interior_color'      => $interiorColor,
                'year'                => $r->Year ?? '-',
                'car_MSRP'            => $r->carOrder?->car_MSRP ?? '-',
                'reservation_cost'    => $r->CashDeposit ?? '-',
                'bookingDate'         => $r?->format_booking_date ?? '-',
                'name_fi'             => $r->remainingPayment->financeInfo->FinanceCompany ?? '-',
                'order_status'        => $r->carOrder->orderStatus->name ?? '-',
                'contract_date'       => $r?->remainingPayment->format_contract_date ?? '',
                'ck_date'             => $r?->format_ck_date ?? '-',
                'dms_date'            => $r?->format_dms_date ?? '-',
                'DeliveryEstimateDate' => $r?->format_delivery_estimate_date ?? '-',
                'DeliveryDate'        => $r?->format_delivery_date ?? '-',
                'status'              => $r?->conStatus?->name ?? '-',
            ];
        });

        return view('purchase-order.report.saleCar.monthlyDelivery.summary', [
            'sale' => $data
        ]);
    }
}
