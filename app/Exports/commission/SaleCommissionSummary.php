<?php

namespace App\Exports\commission;

use App\Services\SaleCommissionQuery;
use App\Services\HeldCommissionQuery;
use Illuminate\Support\Carbon;
use App\Models\User;
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
    protected $fromDate;
    protected $toDate;

    public function __construct($user, $fromDate = null, $toDate = null)
    {
        if (in_array($user->role, ['sale', 'lead_sale'])) {
            abort(403, 'Unauthorized');
        }

        $this->user = $user;
        $this->fromDate = $fromDate ?? now()->startOfMonth()->format('Y-m-d');
        $this->toDate   = $toDate   ?? now()->format('Y-m-d');
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
        $sales = SaleCommissionQuery::base($this->user, false, $this->fromDate, $this->toDate)
            ->get()
            ->groupBy('SaleID');

        // คอมกั๊ก (brand 1) — คิดตามเดือนของช่วงรายงาน (net = ยกมาจากเดือนก่อน − กั๊กเดือนนี้)
        $periodMonth = Carbon::parse($this->fromDate);
        $held = HeldCommissionQuery::forMonth((int) $periodMonth->year, (int) $periodMonth->month)['perSale'];

        $data = $sales->map(function ($rows, $saleId) use ($held) {

            $saleUser = $rows->first()->saleUser;
            $branch = $saleUser->branchInfo->name ?? '-';
            $brand = (int) ($saleUser->brand ?? 0);

            $totalCars = $rows->count();

            $testDrive = $rows->where('carOrder.purchase_type', 1)->count();
            $retail = $rows->where('carOrder.purchase_type', 2)->count();

            // คอมงบเหลือคิดสด (รองรับเคสเกิน over_budget → ใช้ยอดหักของ manager = −D)
            $balanceCampaign = $rows->sum(fn($r) => $r->effectiveBalanceCommission());

            // เกินงบ → ไม่คิดคอมประดับยนต์
            $accessoryCom = $rows->sum(fn($r) => $r->effectiveAccessoryCommission());

            $specialCom = $rows->sum(fn($r) => $r->effectiveSpecialCommission());

            $interestCom = $rows->sum(function ($r) {
                return $r->remainingPayment->total_com ?? 0;
            });

            $turnCarCom = $rows->sum(function ($r) {
                return $r->turnCar->com_turn ?? 0;
            });

            $ssi = $totalCars * 1000;

            // ค่าคอมกั๊ก (เฉพาะ brand 1) — net เข้ายอด "รวมค่าคอมรับ"
            $heldNet = $brand === HeldCommissionQuery::BRAND
                ? (float) ($held[$saleId]['net'] ?? 0)
                : null;

            return [
                'branch' => $branch,
                'saleName' => optional($saleUser)->name ?? '-',
                'totalCars' => $totalCars,
                'retail' => $retail,
                'testDrive' => $testDrive,

                'balanceCampaign' => $balanceCampaign,
                'accessoryCom' => $accessoryCom,
                'specialCom' => $specialCom,
                'interestCom' => $interestCom,
                'turnCarCom' => $turnCarCom,
                'held' => $heldNet,
                'ssi' => $ssi
            ];
        });

        // เซลล์ brand 1 ที่มีคอมกั๊กยกมาจากเดือนก่อน แต่เดือนนี้ไม่มีรถส่งมอบ → เพิ่มเข้ารายงานด้วย
        $carryOnlyIds = $held->filter(fn($h) => ($h['carried'] ?? 0) > 0)->keys()->diff($data->keys());
        if ($carryOnlyIds->isNotEmpty()) {
            $extraUsers = User::with('branchInfo')
                ->whereIn('id', $carryOnlyIds)
                ->where('brand', HeldCommissionQuery::BRAND)
                ->get()->keyBy('id');
            foreach ($carryOnlyIds as $sid) {
                $u = $extraUsers->get($sid);
                if (!$u) {
                    continue;
                }
                $data->put($sid, [
                    'branch' => $u->branchInfo->name ?? '-',
                    'saleName' => $u->name ?? '-',
                    'totalCars' => 0,
                    'retail' => 0,
                    'testDrive' => 0,
                    'balanceCampaign' => 0,
                    'accessoryCom' => 0,
                    'specialCom' => 0,
                    'interestCom' => 0,
                    'turnCarCom' => 0,
                    'held' => (float) ($held[$sid]['net'] ?? 0),
                    'ssi' => 0,
                ]);
            }
        }

        return view('purchase-order.report.commission.sale-report', [
            'commission' => $data->values()
        ]);
    }
}
