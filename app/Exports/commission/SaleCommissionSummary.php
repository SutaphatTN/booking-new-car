<?php

namespace App\Exports\commission;

use App\Services\SaleCommissionQuery;
use App\Services\CarCommissionQuery;
use App\Services\ExtraBudgetLedger;
use App\Services\SsiCommissionQuery;
use App\Models\SaleCommissionMonthly;
use App\Exports\commission\Concerns\BuildsCommissionReport;
use Illuminate\Support\Carbon;
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
    use BuildsCommissionReport;

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

    /** brand ของผู้เปิดรายงาน (base query ถูก scope ตาม brand นี้อยู่แล้ว) */
    protected function brand(): int
    {
        return (int) ($this->user->brand ?: 1);
    }

    public function title(): string
    {
        return 'ค่าคอมรวม';
    }

    /**
     * นิยามคอลัมน์ตาม brand
     *  - brand 1 : + คอมกั๊ก (ยกมา/หัก) + SSI
     *  - brand 2 : + คอมวินัย + คอม Lead + คอม Clip (ไม่มี SSI/กั๊ก)
     *  - brand 3 : ไม่มีรายการเสริม (คอมตัวรถคิดตามรุ่น)
     */
    protected function columns(): array
    {
        $b = $this->brand();

        $cols = [
            ['label' => 'สาขา',       'key' => 'branch',    'role' => 'info'],
            ['label' => 'ชื่อฝ่ายขาย', 'key' => 'saleName',  'role' => 'info'],
            ['label' => 'จำนวนคัน',   'key' => 'totalCars', 'role' => 'info', 'num' => true],

            ['label' => 'คอมรายคันรถปกติ',  'key' => 'carCommission',   'role' => 'recv', 'money' => true],
            ['label' => 'ยอดแบ่งงบเหลือ',   'key' => 'balanceCampaign', 'role' => 'recv', 'money' => true],
            // info เท่านั้น — ยอดนี้ถูกหักออกจาก "ยอดแบ่งงบเหลือ" ไปแล้ว (โชว์ให้รู้ว่าโดนหักอะไร ไม่หักซ้ำ)
            ['label' => 'หักเก็บงบเพิ่มเติม', 'key' => 'extraDeduct', 'role' => 'info', 'money' => true, 'num' => true],
            ['label' => 'คอมประดับยนต์',    'key' => 'accessoryCom',    'role' => 'recv', 'money' => true],
            ['label' => 'คอมอื่นๆ',         'key' => 'specialCom',      'role' => 'recv', 'money' => true],
            ['label' => 'คอมดอกเบี้ย',      'key' => 'interestCom',     'role' => 'recv', 'money' => true],
            ['label' => 'คอมรถเทิร์น',      'key' => 'turnCarCom',      'role' => 'recv', 'money' => true],
        ];

        if ($b === 1) {
            $cols[] = ['label' => 'SSI', 'key' => 'ssi', 'role' => 'recv', 'money' => true];
        } elseif ($b === 2) {
            $cols[] = ['label' => 'คอมวินัย',  'key' => 'comDiscipline', 'role' => 'recv', 'money' => true];
            $cols[] = ['label' => 'คอม Lead',  'key' => 'comLead',       'role' => 'recv', 'money' => true];
            $cols[] = ['label' => 'คอม Clip',  'key' => 'comClip',       'role' => 'recv', 'money' => true];
        }

        $cols[] = ['label' => 'รวมค่าคอมรับ', 'key' => '__recv', 'role' => 'sum_recv', 'money' => true];

        $cols[] = ['label' => 'หักอื่นๆ (หักเงินเดือน/ สาย)', 'key' => 'deductAbsence', 'role' => 'ded', 'money' => true];

        $cols[] = ['label' => 'รวมยอดหัก', 'key' => '__ded', 'role' => 'sum_ded', 'money' => true];
        $cols[] = ['label' => 'คอมสุทธิ',  'key' => '__net', 'role' => 'net',     'money' => true];

        return $cols;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '92d050']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
        ];
    }

    public function registerEvents(): array
    {
        $moneyCols = $this->moneyColumnLetters($this->columns());

        return [
            AfterSheet::class => function (AfterSheet $event) use ($moneyCols) {

                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();

                $sheet->getStyle("A1:{$highestCol}{$highestRow}")->getFont()->setName('Angsana New')->setSize(14);
                $sheet->getStyle("A1:{$highestCol}{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A1:{$highestCol}{$highestRow}")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)->setColor(new Color(Color::COLOR_BLACK));

                $sheet->getRowDimension(1)->setRowHeight(25);
                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }

                $sheet->freezePane('A2');
                $sheet->getTabColor()->setRGB('92d050');

                foreach ($moneyCols as $col) {
                    $sheet->getStyle("{$col}2:{$col}{$highestRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                }
            },
        ];
    }

    public function view(): View
    {
        $brand = $this->brand();

        $sales = SaleCommissionQuery::base($this->user, false, $this->fromDate, $this->toDate)
            ->get()
            ->groupBy('SaleID');

        // คิดตามเดือนของช่วงรายงาน (คอมตัวรถ / คอมกั๊ก / SSI เป็นราย "เดือน")
        $period = Carbon::parse($this->fromDate);
        $year   = (int) $period->year;
        $month  = (int) $period->month;

        $carCom = CarCommissionQuery::forMonth($year, $month)['perSale'];
        $ssiPer = SsiCommissionQuery::forPeriod($year, $month)['perSale'];
        $adjust = SaleCommissionMonthly::where('year', $year)->where('month', $month)
            ->get()->keyBy('SaleID');

        $data = $sales->map(function ($rows, $saleId) use ($brand, $carCom, $ssiPer, $adjust) {

            $saleUser = $rows->first()->saleUser;
            $adj = $adjust->get($saleId);

            // ยอดสุทธิ = ยอดที่ได้ทั้งเดือน (คอมกั๊กเป็นเรื่องเวลาจ่าย ดูละเอียดในชีท "คอมกั๊ก (รายคัน)")
            $row = [
                'branch'    => $saleUser->branchInfo->name ?? '-',
                'saleName'  => optional($saleUser)->name ?? '-',
                'totalCars' => $rows->count(),
                'retail'    => $rows->where('carOrder.purchase_type', 2)->count(),
                'testDrive' => $rows->where('carOrder.purchase_type', 1)->count(),

                'carCommission'   => (float) (CarCommissionQuery::entry($carCom, (int) $saleId, $brand)['amount'] ?? 0),
                'balanceCampaign' => $rows->sum(fn($r) => $r->effectiveBalanceCommission()),
                'extraDeduct'     => $rows->sum(fn($r) => ExtraBudgetLedger::absorbedFor($r)) ?: null,
                'accessoryCom'    => $rows->sum(fn($r) => $r->effectiveAccessoryCommission()),
                'specialCom'      => $rows->sum(fn($r) => $r->effectiveSpecialCommission()),
                'interestCom'     => $rows->sum(fn($r) => $r->remainingPayment->total_com ?? 0),
                'turnCarCom'      => $rows->sum(fn($r) => $r->turnCar->com_turn ?? 0),

                'deductAbsence'   => (float) ($adj->deduct_absence ?? 0),
            ];

            if ($brand === 1) {
                $row['ssi'] = (float) ($ssiPer[$saleId]['amount'] ?? 0);
            } elseif ($brand === 2) {
                $row['comDiscipline'] = (float) ($adj->com_discipline ?? 0);
                $row['comLead']       = (float) ($adj->com_lead ?? 0);
                $row['comClip']       = (float) ($adj->com_clip ?? 0);
            }

            return $row;
        });

        $payload = $this->buildReport($this->columns(), $data->values());

        return view('purchase-order.report.commission.sale-report-generic', $payload);
    }
}
