<?php

namespace App\Exports\commission;

use App\Models\Salecar;
use App\Services\HeldCommissionQuery;
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

/**
 * ชีท "คอมกั๊ก (รายคัน)" — brand 1 เท่านั้น (DRAFT)
 * โชว์การแตกค่าคอมรายคันตามรอบจ่าย (รอบหลัก 10 + รอบกั๊ก 10/20/เดือนถัดไป)
 * เลือกเดือน = เดือนที่ตัด CK (DeliveryInCKDate)
 */
class HeldCommissionSheet implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    protected $user;
    protected $fromDate;
    protected $moneyCols = ['G', 'I', 'J', 'L'];

    public function __construct($user, $fromDate = null)
    {
        $this->user = $user;
        $this->fromDate = $fromDate ?? now()->startOfMonth()->format('Y-m-d');
    }

    public function title(): string
    {
        return 'คอมกั๊ก (รายคัน)';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'c00000']],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
        ];
    }

    public function registerEvents(): array
    {
        $moneyCols = $this->moneyCols;

        return [
            AfterSheet::class => function (AfterSheet $event) use ($moneyCols) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();

                $sheet->getStyle("A1:{$highestCol}{$highestRow}")->getFont()->setName('Angsana New')->setSize(14);
                $sheet->getStyle("A1:{$highestCol}{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A1:{$highestCol}{$highestRow}")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)->setColor(new Color(Color::COLOR_BLACK));

                $sheet->getRowDimension(1)->setRowHeight(30);
                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }

                $sheet->setAutoFilter("B1:B{$highestRow}");
                $sheet->freezePane('A2');
                $sheet->getTabColor()->setRGB('c00000');

                foreach ($moneyCols as $col) {
                    $sheet->getStyle("{$col}2:{$col}{$highestRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                }
            },
        ];
    }

    public function view(): View
    {
        [$year, $month] = [(int) Carbon::parse($this->fromDate)->year, (int) Carbon::parse($this->fromDate)->month];

        $detail = HeldCommissionQuery::perCarForCkMonth($year, $month);

        // ข้อมูลแสดงผลต่อคัน (ลูกค้า/รุ่น/เซลล์) — โหลดครั้งเดียว
        $meta = Salecar::withoutGlobalScopes()
            ->with(['saleUser.branchInfo', 'customer.prefix', 'carOrder.model', 'carOrder.subModel'])
            ->whereIn('id', $detail->pluck('salecar_id'))
            ->get()->keyBy('id');

        $headers = [
            'สาขา', 'ชื่อฝ่ายขาย', 'ชื่อลูกค้า', 'รุ่นรถ',
            'DeliveryInCKDate', 'DeliveryDate',
            'ค่าคอมรถ', 'เข้ากั๊ก?', 'ยอดกั๊ก',
            'รอบหลัก (ยอด)', 'รอบหลัก (วันจ่าย)',
            'รอบกั๊ก (ยอด)', 'รอบกั๊ก (วันจ่าย)',
        ];

        $rows = [];
        $sumC = $sumH = $sumMain = $sumHeld = 0.0;

        foreach ($detail->sortBy(fn($d) => optional(optional($meta->get($d['salecar_id']))->saleUser)->name) as $d) {
            $s = $meta->get($d['salecar_id']);
            $cust = $s ? trim(($s->customer->prefix->Name_TH ?? '') . ' ' . ($s->customer->FirstName ?? '') . ' ' . ($s->customer->LastName ?? '')) : '';
            $model = $s->carOrder->model->Name_TH ?? '-';
            $sub = $s->carOrder->subModel->name ?? '';

            $sumC += $d['car_commission'];
            $sumH += $d['held_amount'];
            $sumMain += $d['main_amount'];
            $sumHeld += $d['held_amount'];

            $rows[] = [
                optional(optional($s)->saleUser?->branchInfo)->name ?? '-',
                optional(optional($s)->saleUser)->name ?? '-',
                $cust ?: '-',
                trim($model . ' ' . $sub),
                $this->d($d['ck']),
                $d['dd'] ? $this->d($d['dd']) : '-',
                $d['car_commission'] ?: '',
                $d['held'] ? '✓' : '–',
                $d['held_amount'] ?: '',
                $d['main_amount'],
                $this->d($d['main_payday']),
                $d['held_amount'] ?: '',
                $d['held'] ? ($d['held_payday'] ? $this->d($d['held_payday']) : 'รอส่งมอบ (DD ว่าง)') : '–',
            ];
        }

        $totalRow = empty($rows) ? null : [
            'รวม', '', '', '', '', '',
            $sumC, '', $sumH, $sumMain, '', $sumHeld, '',
        ];

        return view('purchase-order.report.commission.sale-report-generic', [
            'headers' => $headers,
            'rows' => $rows,
            'totalRow' => $totalRow,
        ]);
    }

    /** วันที่รูปแบบ d/m/Y */
    private function d(?string $ymd): string
    {
        return $ymd ? Carbon::parse($ymd)->format('d/m/Y') : '';
    }
}
