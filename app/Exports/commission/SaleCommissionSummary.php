<?php

namespace App\Exports\commission;

use App\Services\SaleCommissionQuery;
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

class SaleCommissionSummary implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    protected $user;
    protected $month;
    protected $year;

    public function __construct($user, $month, $year)
    {
        $this->user = $user;
        $this->month = $month;
        $this->year  = $year;
    }

    public function title(): string
    {
        return 'ค่าคอมรวม';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            //แถวบนสุด
            1 => [
                'font' => [],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '92d050'],
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

                // freeze header
                $sheet->freezePane('A2');

                // สี sheet
                $sheet->getTabColor()->setRGB('92d050');

                // format comma
                $numberColumns = [
                    'F',
                    'G',
                    'H',
                    'I',
                    'J',
                    'K',
                    'L',
                    'M',
                    'N',
                    'O',
                    'P',
                    'Q',
                    'R',
                    'S',
                    'T',
                    'U'
                ];

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
        $sales = SaleCommissionQuery::base($this->user, false, $this->month, $this->year)
            ->get()
            ->groupBy('SaleID');

        $data = $sales->map(function ($rows) {

            $saleUser = $rows->first()->saleUser;
            $branch = $saleUser->branchInfo->name ?? '-';
            $saleName = $saleUser->name ?? '-';

            $totalCars = $rows->count();

            $testDrive = $rows->where('carOrder.purchase_type', 1)->count();
            $retail = $rows->where('carOrder.purchase_type', 2)->count();

            $balanceCampaign = $rows->sum(function ($r) {
                return min($r->balanceCampaign ?? 0, 2500);
            });

            $accessoryCom = $rows->sum(function ($r) {
                return ($r->AccessoryGiftCom ?? 0) + ($r->AccessoryExtraCom ?? 0);
            });

            $specialCom = $rows->sum('CommissionSpecial');

            $interestCom = $rows->sum(function ($r) {
                return $r->remainingPayment->total_com ?? 0;
            });

            $turnCarCom = $rows->sum(function ($r) {
                return $r->turnCar->com_turn ?? 0;
            });

            $ssi = $totalCars * 1000;

            return [
                'branch' => $branch,
                'saleName' => $saleName,
                'totalCars' => $totalCars,
                'retail' => $retail,
                'testDrive' => $testDrive,

                'balanceCampaign' => $balanceCampaign,
                'accessoryCom' => $accessoryCom,
                'specialCom' => $specialCom,
                'interestCom' => $interestCom,
                'turnCarCom' => $turnCarCom,
                'ssi' => $ssi
            ];
        })->values();

        return view('purchase-order.commission.sale-report', [
            'commission' => $data
        ]);
    }
}
