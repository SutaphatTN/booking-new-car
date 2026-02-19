<?php

namespace App\Exports\gp;

use App\Services\GPQuery;
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

class GPSummary implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    protected $fromDate;
    protected $toDate;

    public function __construct($fromDate = null, $toDate = null)
    {
        $this->fromDate = $fromDate ?? now()->startOfMonth()->format('Y-m-d');
        $this->toDate   = $toDate   ?? now()->format('Y-m-d');
    }


    public function title(): string
    {
        return 'สรุป GP';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            //แถวบนสุด
            1 => [
                'font' => [],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '7ed7f7'],
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
                $sheet->getTabColor()->setRGB('7ed7f7');
            },
        ];
    }

    public function view(): View
    {
        $rows = GPQuery::base($this->fromDate, $this->toDate)->get();

        $totalSalePrice = $rows->sum(fn($r) => $r->carOrder->car_MSRP ?? 0);
        $totalCostPrice = $rows->sum(fn($r) => $r->carOrder->car_DNP ?? 0);
        $grossProfit = $totalSalePrice - $totalCostPrice;
        // % กำไรขั้นต้น 
        $grossProfitPercent = $totalSalePrice > 0
            ? ($grossProfit / $totalSalePrice) * 100
            : 0;

        // รวมรายได้อื่นๆ
        $totalFinanceCom = $rows->sum(fn($r) => $r->financeConfirm->com_fin ?? 0);
        $totalFinanceExtra = $rows->sum(fn($r) => $r->financeConfirm->com_extra ?? 0);
        $totalFinance = $totalFinanceCom + $totalFinanceExtra;

        $totalRI = $rows->sum(fn($r) => $r->carOrder->RI ?? 0);

        $totalMarkupPrice = $rows->sum(fn($r) => $r->MarkupPrice ?? 0);
        $totalKickback = $rows->sum(fn($r) => $r->kickback ?? 0);
        $totalAccessoryExtra = $rows->sum(fn($r) => $r->TotalAccessoryExtra ?? 0);

        $otherIncome = $totalFinance + $totalRI + $totalMarkupPrice + $totalKickback + $totalAccessoryExtra;

        // ค่าใช้จ่ายการขาย 
        $totalDiscount = $rows->sum(fn($r) => $r->discount ?? 0);
        $totalPaymentDiscount = $rows->sum(fn($r) => $r->PaymentDiscount ?? 0);
        $totalDownPaymentDiscount = $rows->sum(fn($r) => $r->DownPaymentDiscount ?? 0);
        $totalTotalAccessoryGift = $rows->sum(fn($r) => $r->TotalAccessoryGift ?? 0);
        $totalCommissionSale = $rows->sum(fn($r) => $r->CommissionSale ?? 0);

        $sellingExpense = $totalDiscount + $totalPaymentDiscount + $totalDownPaymentDiscount + $totalTotalAccessoryGift + $totalCommissionSale;

        $netProfit = ($grossProfit + $otherIncome) - $sellingExpense;

        $netProfitPercent = $totalSalePrice > 0
            ? ($netProfit / $totalSalePrice) * 100
            : 0;

        $data = [
            'sale_price' => $totalSalePrice,
            'cost_price' => $totalCostPrice,
            'gross_profit' => $grossProfit,
            'gross_percent' => $grossProfitPercent,
            'other_income' => $otherIncome,
            'selling_expense' => $sellingExpense,
            'net_profit' => $netProfit,
            'net_percent' => $netProfitPercent,
        ];

        return view('purchase-order.report.gp.summary', compact('data'));
    }
}
