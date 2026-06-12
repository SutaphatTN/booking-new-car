<?php

namespace App\Exports\campaign;

use App\Models\Salecampaign;
use Carbon\Carbon;
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

class CampaignClaimExport implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    // เฉพาะแคมเปญ On-Top (tb_campaign_type id 10-12, 23-25)
    private const ONTOP_TYPE_IDS = [10, 11, 12, 23, 24, 25];

    protected $fromDate;
    protected $toDate;

    public function __construct($fromDate = null, $toDate = null)
    {
        $this->fromDate = $fromDate ?: '2000-01-01';
        $this->toDate   = $toDate ?: now()->format('Y-m-d');
    }

    public function title(): string
    {
        return 'รายการใช้แคมเปญ';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => 'FFC000'],
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
                    ->getFont()->setName('Angsana New')->setSize(14);

                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->setColor(new Color(Color::COLOR_BLACK));

                $sheet->getRowDimension(1)->setRowHeight(28);
                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }

                $sheet->freezePane('A2');
                $sheet->getTabColor()->setRGB('FFC000');

                // format comma : ยอดเคลมค้างรับ, ยอดรับเคลมในเดือน, ยอดคงเหลือ
                foreach (['G', 'H', 'I'] as $col) {
                    $sheet->getStyle("{$col}2:{$col}{$highestRow}")
                        ->getNumberFormat()->setFormatCode('#,##0.00');
                }
            },
        ];
    }

    public function view(): View
    {
        $rows = Salecampaign::query()
            ->whereIn('CampaignType', self::ONTOP_TYPE_IDS)
            ->whereHas('saleCar', function ($q) {
                $q->where('con_status', 5) // เฉพาะรถที่ส่งมอบแล้ว
                    ->whereDate('DeliveryDate', '>=', $this->fromDate)
                    ->whereDate('DeliveryDate', '<=', $this->toDate);
            })
            ->with([
                'campaignType',
                'saleCar.customer',
                'saleCar.carOrder',
                'claim.status',
            ])
            ->get()
            ->sortBy(fn($sc) => $sc->saleCar?->DeliveryDate)
            ->values();

        $data = $rows->map(function ($sc) {
            $car = $sc->saleCar;
            $cus = $car?->customer;
            $customer = $cus ? trim(($cus->FirstName ?? '') . ' ' . ($cus->LastName ?? '')) : '';

            $delivery = $car?->DeliveryDate ? Carbon::parse($car->DeliveryDate) : null;

            $used = $sc->CashSupportFinal !== null ? (float) $sc->CashSupportFinal : null;

            $claim = $sc->claim;
            $claimAmount = $claim && $claim->claim_amount !== null ? (float) $claim->claim_amount : null;
            $diff = $claimAmount !== null ? (($used ?? 0) - $claimAmount) : null;

            $status = $claim?->status;
            $statusText = $status ? $status->id . '.' . $status->name : '';

            return [
                'delivery_date' => $delivery ? $delivery->format('d/m/Y') : '',
                'year'          => $delivery ? $delivery->format('Y') : '',
                'month'         => $delivery ? $delivery->format('n') : '',
                'claim_type'    => $sc->campaignType?->name ?? '',
                'customer'      => $customer,
                'chassis'       => $car?->carOrder?->vin_number ?? '',
                'used'          => $used,
                'claim_amount'  => $claimAmount,
                'diff'          => $diff,
                'received_date' => $claim?->received_date ? Carbon::parse($claim->received_date)->format('d/m/Y') : '',
                'status'        => $statusText,
                'note'          => $claim?->note ?? '',
            ];
        });

        return view('campaign.claim.report', ['rows' => $data]);
    }
}
