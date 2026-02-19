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

class GPPerCar implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
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
    return 'GP รายคัน';
  }

  public function styles(Worksheet $sheet)
  {
    return [
      //แถวบนสุด
      1 => [
        'font' => [],
        'fill' => [
          'fillType' => 'solid',
          'startColor' => ['rgb' => '6af59d'],
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
        $sheet->getTabColor()->setRGB('6af59d');

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
          'S'
        ];

        foreach ($numberColumns as $col) {
          $sheet->getStyle("{$col}2:{$col}{$highestRow}")
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');
        }

        // format %
        $percentColumns = ['I', 'S'];

        foreach ($percentColumns as $col) {
          $sheet->getStyle("{$col}2:{$col}{$highestRow}")
            ->getNumberFormat()
            ->setFormatCode('0.00%');
        }
      },
    ];
  }

  public function view(): View
  {

    $rows = GPQuery::base($this->fromDate, $this->toDate)->get();

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

      $totalSalePrice = $r->carOrder->car_MSRP ?? 0;
      $totalCostPrice = $r->carOrder->car_DNP ?? 0;
      $grossProfit = $totalSalePrice - $totalCostPrice;
      // % กำไรขั้นต้น 
      $grossProfitPercent = $totalSalePrice > 0
        ? ($grossProfit / $totalSalePrice)
        : 0;

      // รายได้อื่นๆ
      $totalRI = $r->carOrder->RI ?? 0;

      $totalFinanceCom = $r->financeConfirm->com_fin ?? 0;
      $totalFinanceExtra = $r->financeConfirm->com_extra ?? 0;
      $totalFinance = $totalFinanceCom + $totalFinanceExtra;

      $totalMarkupPrice = $r->MarkupPrice ?? 0;
      $totalKickback = $r->kickback ?? 0;
      $totalAccessoryExtra = $r->TotalAccessoryExtra ?? 0;
      $totalOther = $totalMarkupPrice + $totalKickback + $totalAccessoryExtra;

      // รวมรายได้อื่นๆ
      $otherIncome = $totalFinanceCom + $totalFinanceExtra + $totalRI + $totalMarkupPrice + $totalKickback + $totalAccessoryExtra;

      // ค่าใช้จ่ายการขาย 
      $totalDiscount = $r->discount ?? 0;
      $totalPaymentDiscount = $r->PaymentDiscount ?? 0;
      $totalDownPaymentDiscount = $r->DownPaymentDiscount ?? 0;
      $totalTotalAccessoryGift = $r->TotalAccessoryGift ?? 0;
      $totalCommissionSale = $r->CommissionSale ?? 0;

      $sellingExpense = $totalDiscount + $totalPaymentDiscount + $totalDownPaymentDiscount + $totalTotalAccessoryGift + $totalCommissionSale;

      $netProfit = ($grossProfit + $otherIncome) - $sellingExpense;

      $netProfitPercent = $totalSalePrice > 0
        ? ($netProfit / $totalSalePrice)
        : 0;

      return [
        'customer' => $customerName,
        'saleName' => optional($r->saleUser)->name ?? '-',
        'model' => $model,
        'subModel' => $subModel,
        'sale_price' => $totalSalePrice,
        'cost_price' => $totalCostPrice,
        'gross_profit' => $grossProfit,
        'gross_percent' => $grossProfitPercent,
        'RI' => $totalRI,
        'other' => $totalOther,
        'other_income' => $otherIncome,
        'com' => $totalFinanceCom,
        'extra' => $totalFinanceExtra,
        'selling_expense' => $sellingExpense,
        'net_profit' => $netProfit,
        'net_percent' => $netProfitPercent,
      ];
    });

    return view('purchase-order.report.gp.per-car', [
      'gpPer' => $data
    ]);
  }
}
