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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class MonthlyDeliveryExport implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    protected $fromDate;
    protected $toDate;
    protected $dateType;

    public function __construct($fromDate = null, $toDate = null, $dateType = 'dms')
    {
        $this->fromDate = $fromDate ?? now()->startOfMonth()->format('Y-m');
        $this->toDate   = $toDate   ?? now()->startOfMonth()->format('Y-m');
        $this->dateType = $dateType;
    }

    public function title(): string
    {
        return 'รายงานส่งมอบประจำเดือน';
    }

    /**
     * ตำแหน่งคอลัมน์ ราคาขาย / เงินจอง — ขยับตาม brand เพราะ Option กับ สีภายใน โผล่ไม่เท่ากัน
     * ต้องตรงกับลำดับใน blade monthlyDelivery/summary.blade.php
     */
    private function moneyColumns(): array
    {
        $brand = auth()->user()->brand;

        $next = 9; // A-H = No, ลูกค้า, ที่อยู่, ฝ่ายขาย, รุ่นหลัก, รุ่นย่อย, Vin, เลขเครื่อง
        if (!in_array($brand, [2, 3, 4])) $next++; // Option
        $next++;                                    // สี
        if ($brand == 2) $next++;                   // สีภายใน
        $next++;                                    // ปี
        $msrp = $next++;                            // ราคาขาย
        $next += 3;                                 // แหล่งที่มา + ประเภทการขาย + ประเภทการซื้อรถ
        $deposit = $next++;                         // เงินจอง

        return array_map(
            fn($i) => Coordinate::stringFromColumnIndex($i),
            ['msrp' => $msrp, 'deposit' => $deposit]
        );
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

                $numberColumns = $this->moneyColumns();
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
        $start = Carbon::createFromFormat('Y-m', $this->fromDate)->startOfMonth();
        $end   = Carbon::createFromFormat('Y-m', $this->toDate)->endOfMonth();

        // เผื่อกรอกช่วงกลับด้าน (เดือนเริ่ม > เดือนสิ้นสุด) ให้สลับให้ถูกต้อง
        if ($start->greaterThan($end)) {
            [$start, $end] = [$end->copy()->startOfMonth(), $start->copy()->endOfMonth()];
        }

        $dateField = $this->dateType === 'ck' ? 'DeliveryInCKDate' : 'DeliveryInDMSDate';

        $rows = SaleBookingQuery::base()
            ->with(['salePurType', 'carOrder.purchaseType'])
            ->whereBetween($dateField, [$start, $end])
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

            $color = in_array($r->brand, [2, 3, 4])
                ? ($r->gwmColor->name ?? '-')
                : ($r->Color ?? '-');

            $interiorColor = $r->brand == 2
                ? ($r->interiorColor->name ?? '-')
                : null;

            // ชื่อไฟแนนซ์: non-finance = ซื้อสด, finance = ชื่อบริษัทไฟแนนซ์ (ถ้าไม่ได้เลือก = -)
            $financeName = $r->payment_mode === 'finance'
                ? ($r->remainingPayment?->financeInfo?->FinanceCompany ?? '-')
                : 'ซื้อสด';

            return [
                'customer'            => $customerName,
                'address'             => ($r->customer?->documentAddress ?? $r->customer?->currentAddress)?->full_address ?? '',
                'sale'                => $r->saleUser?->name ?? '-',
                'model'               => $model,
                'subModel'            => $subModel,
                'vin'                 => $r->carOrder?->vin_number,
                'engine'              => $r->carOrder?->engine_number,
                'option'              => $r->option ?? '-',
                'color'               => $color,
                'interior_color'      => $interiorColor,
                'year'                => $r->Year ?? '-',
                'car_MSRP'            => $r->carOrder?->car_MSRP ?? '-',
                // ประเภทการขาย (salecars.type_sale) : Normal / Test Drive / Dealer
                'type_sale'           => $r->salePurType?->name ?? '-',
                // ประเภทการซื้อรถ (car_order.purchase_type) : Retail / TestDrive / ActivityCar / Company
                'purchase_type'       => $r->carOrder?->purchaseType?->name ?? '-',
                'reservation_cost'    => $r->CashDeposit ?? '-',
                'bookingDate'         => $r?->format_booking_date ?? '-',
                'name_fi'             => $financeName,
                'order_status'        => $r->carOrder->orderStatus->name ?? '-',
                'contract_date'       => $r?->remainingPayment->format_contract_date ?? '',
                'ck_date'             => $r?->format_ck_date ?? '-',
                'dms_date'            => $r?->format_dms_date ?? '-',
                'DeliveryEstimateDate' => $r?->format_delivery_estimate_date ?? '-',
                'DeliveryDate'        => $r?->format_delivery_date ?? '-',
                'status'              => $r?->conStatus?->name ?? '-',
                'type'                => $r->getRelation('type')?->name ?? '-',
            ];
        });

        return view('purchase-order.report.saleCar.monthlyDelivery.summary', [
            'sale' => $data
        ]);
    }
}
