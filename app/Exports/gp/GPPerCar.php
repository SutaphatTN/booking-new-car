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

  public function __construct($fromDate = null)
  {
    $this->fromDate = $fromDate ?? now()->startOfMonth()->format('Y-m');
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
          'Q',
          'R',
          'S',
          'T',
          'U',
          'V',
          'W',
          'X',
          'Y',
          'Z',
          'AA',
          'AB',
          'AC',
          'AD',
          'AE',
          'AF',
          // 'AG',
          'AH',
          // 'AI',
          'AJ',
          'AK',
          'AL',
          'AM',
          'AN',
          'AO',
          'AP',
          'AQ',
          // 'AR',
          // 'AS',
          // 'AT',
          'AU',
          'AV',
          'AW',
          'AX',
          'AY',
          'AZ',
          'BA',
          'BB',
          'BC',
          'BD',
          'BE',
          'BF',
          'BG',
          'BH',
        ];

        foreach ($numberColumns as $col) {
          $sheet->getStyle("{$col}2:{$col}{$highestRow}")
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');
        }

        // format %
        // $percentColumns = ['Y', 'AL'];

        // foreach ($percentColumns as $col) {
        //   $sheet->getStyle("{$col}2:{$col}{$highestRow}")
        //     ->getNumberFormat()
        //     ->setFormatCode('0.00%');
        // }
      },
    ];
  }

  public function view(): View
  {

    $rows = GPQuery::base($this->fromDate)->get();

    $data = $rows->map(function ($r) {
      $customerName = trim(
        ($r->customer->prefix->Name_TH ?? '') . ' ' .
          ($r->customer->FirstName ?? '') . ' ' .
          ($r->customer->LastName ?? '')
      );

      $model = $r->carOrder->model->Name_TH ?? '-';
      $sub = $r->carOrder->subModel->name ?? '-';
      $detailModel = $r->carOrder->subModel->detail ?? null;

      $subModel = $detailModel
        ? "{$detailModel} - {$sub}"
        : $sub;

      $color = in_array($r->brand, [2, 3])
        ? ($r->carOrder->gwmColor->name ?? '-')
        : ($r->carOrder->color ?? '-');

      $interiorColor = $r->brand == 2
        ? ($r->interiorColor->name ?? '-')
        : null;

      // ราคาขาย
      $totalSalePrice = $r->carOrder->car_MSRP ?? 0;
      // ราคาทุน
      $totalCostPrice = $r->carOrder->car_DNP ?? 0;

      // ส่วนลดราคารถ
      $carDiscount = $r->payment_mode === 'finance'
        ? ($r->discount ?? 0)
        : ($r->PaymentDiscount ?? 0);

      // ราคาขายรวมบวกหัว (ไม่รวม VAT)
      $makePrice = $r->MarkupPrice ?? 0;
      $carSaleDis = $totalSalePrice - $carDiscount; // ราคาขายลบส่วนลด
      $carSaleMake = $carSaleDis + $makePrice;
      //Net price ราคาขาย ลบ ส่วนลด บวก บวกหัว คือ carSaleMake
      $totalSaleMake =  $carSaleMake / 1.07;

      // ราคาทุน (ไม่รวม VAT)
      // ถ้ามีราคาทุนกรอกเอง (gp_cost_price_override) ใช้ค่านั้น + ค่าอุปกรณ์ตกแต่ง ถ้าไม่มี ใช้สูตรเดิม
      $totalCostFund =  $r->gp_cost_price_override !== null
        ? ($r->gp_cost_price_override + ($r->gp_accessory_cost ?? 0))
        : ($totalCostPrice / 1.07);

      //บวกหัก
      $makeVat = $makePrice / 1.07;

      // GP
      // $totalGP = $totalSalePrice - $totalCostPrice;
      $totalGP = ($totalSaleMake - $makePrice) - $totalCostFund;

      // per GP
      $saleDisMake = $totalSaleMake - $makePrice;
      $totalPerGP = ($saleDisMake > 0)
        ? round(($totalGP / $saleDisMake) * 100, 2)
        : 0;

      //WS , RI
      $ws = $r->carOrder?->WS ?? 0;
      $ri = $r->carOrder?->RI ?? 0;

      //campaign
      $campaign = $r->campaigns->filter(fn($c) => $c->campaign?->type?->type == 1)->sum('CashSupportFinal');
      $campaign_top = $r->campaigns->filter(fn($c) => $c->campaign?->type?->type == 2)->sum('CashSupportFinal');
      $campaign_other = $r->campaigns->filter(fn($c) => $c->campaign?->type?->type == 3)->sum('CashSupportFinal');
      $campaign_ck = $r->campaigns->filter(fn($c) => $c->campaign?->type?->type == 4)->sum('CashSupportFinal');

      // group 1 = campaign_type ที่ type = 1 (รวม GWM id=26 อัตโนมัติ), group 2 = type = 2 (On-Top)
      $campaign_detail_1 = $r->campaigns
        ->filter(fn($c) => $c->campaign?->type?->type == 1)
        ->map(fn($c) => ($c->campaign?->appellation?->name ?? '') . ' (' . ($c->campaign?->type?->name ?? '') . ')')
        ->filter()
        ->implode(' / ');

      $campaign_detail_2 = $r->campaigns
        ->filter(fn($c) => $c->campaign?->type?->type == 2)
        ->map(fn($c) => ($c->campaign?->appellation?->name ?? '') . ' (' . ($c->campaign?->type?->name ?? '') . ')')
        ->filter()
        ->implode(' / ');

      // com company
      $downPayment = $r->DownPayment ?? 0;
      $totalAlp = $r->remainingPayment?->total_alp ?? 0;
      $interest = $r->remainingPayment?->interest ?? 0;
      $year = $r->remainingPayment?->financeInfo?->max_year ?? 0;
      $typeCom = $r->remainingPayment?->type_com ?? 0;
      $com_company = ($carSaleMake - $downPayment + $totalAlp) * (($interest/100) * $year * ($typeCom/100)) / 1.07;

      //com ต่างๆ
      $com_fin = $r->financeConfirm?->com_fin ?? 0;
      $com_extra = $r->financeConfirm?->com_extra ?? 0;
      $com_kick = $r->kickback ?? 0;
      $com_subsidy = $r->financeConfirm?->com_subsidy ?? 0;
      $com_bo = $r->financeConfirm?->special_money ?? 0;
      // ค่าใช้จ่ายอื่นๆ หรือจ่ายเพิ่ม
      $acc_extra = $r->payment_mode === 'finance'
        ? ($r->other_cost_fi ?? 0)
        : ($r->other_cost ?? 0);
      // $acc_extra = $r->TotalAccessoryExtra ?? 0;

      // Total Revenue
      // $total_rev = $totalSaleMake + $ws + $ri + $acc_extra + $campaign + $campaign_top + $campaign_other + $campaign_ck + $com_fin + $com_extra + $com_kick +  $com_subsidy;
      $total_rev = $totalSaleMake + $ws + $ri + $acc_extra + $campaign_top + $com_company + $com_extra + $com_kick +  $com_subsidy + $com_bo;

      $down_payDis = $r->DownPaymentDiscount ?? 0;
      $accGiftVat = $r->AccessoryGiftVat ?? 0;
      $downDisAccVat = $down_payDis + $accGiftVat;
      $com_sale = $r->CommissionSale ?? 0;
      $ReferrerAmount = $r->ReferrerAmount ?? 0;

      // ยอดของแถมทั้งหมด — คิดจากราคาทุนอะไหล่ (cost_spare) ของของแถม (gift) ทุกชิ้น ไม่ว่าจะเป็นของแถมมาตรฐานหรือปกติ
      $giftAccCostSpare = $r->accessories
        ->filter(fn($a) => $a->pivot->type === 'gift')
        ->sum(fn($a) => (float) ($a->cost_spare ?? 0));

      // ของเดิม (comment ไว้)
      // $totalTotalAccessoryGift = $r->TotalAccessoryGift ?? 0;
      // ยอดของแถมมาตรฐาน — คิดจากราคาทุนอะไหล่ (cost_spare) ของประดับยนต์ที่ is_standard และเป็นของแถม (gift)
      // $standardGiftAcc = $r->accessories
      //   ->filter(fn($a) => $a->is_standard && $a->pivot->type === 'gift')
      //   ->sum(fn($a) => (float) ($a->cost_spare ?? 0));

      // ส่วนลดแคมเปญ — ยอดหัก cashSupport_deduct จาก campaign ที่ใช้ (เฉพาะ type = 1)
      $campaign_deduct = $r->campaigns
        ->filter(fn($c) => $c->campaign?->type?->type == 1)
        ->sum(fn($c) => (float) ($c->CashSupportDeduct ?? 0));

      //รวมส่วนลด
      $total_discount = $downDisAccVat + $carDiscount + $giftAccCostSpare + $campaign_deduct + $ReferrerAmount;
      $total_discount_re = $total_discount - $down_payDis - $carDiscount;

      //ต้นทุนรวม
      // คอมขาย: ใช้ที่กรอกเอง (gp_commission_sale) ถ้ายังไม่กรอก fallback เป็น 4500
      $comSale = $r->gp_commission_sale ?? 3500;
      // $total_cost = ($totalCostFund + $down_payDis + $com_sale) - $total_discount;
      $total_cost = $totalCostFund + $down_payDis + $comSale + $total_discount_re;
      //P/L
      $total_pl = $total_rev - $total_cost;


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
      // $totalTotalAccessoryGift = $r->TotalAccessoryGift ?? 0;
      $totalCommissionSale = $r->CommissionSale ?? 0;

      $sellingExpense = $totalDiscount + $totalPaymentDiscount + $totalDownPaymentDiscount + $giftAccCostSpare + $totalCommissionSale;

      $netProfit = ($grossProfit + $otherIncome) - $sellingExpense;

      $netProfitPercent = $totalSalePrice > 0
        ? ($netProfit / $totalSalePrice)
        : 0;

      return [
        'deliveryDate' => $r->format_delivery_date ?? '-',
        'firmDate' => $r->financeConfirm?->format_firm_date ?? '-',
        'FNDate' => $r->financeConfirm?->format_date ?? '-',
        'customer' => $customerName,
        'saleName' => optional($r->saleUser)->name ?? '-',
        'model' => $model,
        'subModel' => $subModel,
        'color' => $color,
        'interior_color' => $interiorColor,
        'year' => $r->carOrder?->year ?? '-',
        'option' => $r->carOrder?->option ?? '-',
        'vin_number' => $r->carOrder?->vin_number ?? '-',
        'engine_number' => $r->carOrder?->engine_number ?? '-',
        'finance' => $r->remainingPayment?->financeInfo?->FinanceCompany ?? '-',
        'type_sale' => $r->salePurType?->name ?? '-',
        'branch_sale' => $r->saleUser?->branchInfo?->name ?? '-',
        'sale_price' => $totalSalePrice,
        'cost_price' => $totalCostPrice,
        'car_discount' => $carDiscount,
        'down_payDis' => $down_payDis,
        'down_payment' => $downPayment,
        'net_price' => $carSaleMake,
        'makeUp' => $makePrice,
        'sale_make' => $totalSaleMake,
        'makeVat' => $makeVat,
        'totalCostFund' => $totalCostFund,
        'gp' => $totalGP,
        'per_gp' => $totalPerGP,
        'ws' => $ws,
        'ri' => $ri,
        'acc_extra' => $acc_extra,
        'campaign' => $campaign ?? 0,
        'campaign_top' => $campaign_top ?? 0,
        // 'campaign_other' => $campaign_other ?? 0,
        // 'campaign_ck' => $campaign_ck ?? 0,
        'campaign_detail_1' => $campaign_detail_1 ?? '-',
        'campaign_deduct' => $campaign_deduct,
        'campaign_detail_2' => $campaign_detail_2 ?? '-',
        'total_rev' => $total_rev ?? 0,
        'total_discount' => $total_discount ?? 0,
        'total_discount_re' => $total_discount_re ?? 0,
        'com_sale' => $comSale,
        // 'com_sale' => $com_sale ?? 0,
        'total_cost' => $total_cost ?? 0,
        'total_pl' => $total_pl ?? 0,
        're_interest' => $interest,
        're_type_com' => $typeCom,
        're_period' => $r->remainingPayment?->period ?? '-',
        're_year' => $year,
        're_alp' => $r->remainingPayment?->alp ?? '-',
        'balance_fi' => $r->balanceFinance ?? 0,
        're_total_alp' => $totalAlp,
        'advance_installment' => $r->financeConfirm?->advance_installment ?? 0,
        'com_company' => $com_company,
        'com_fin' => $com_fin,
        'com_fin_accept' => $r->financeConfirm?->com_fin_accept ?? 0,
        'com_extra' => $com_extra,
        'com_kick' => $com_kick,
        'com_subsidy' =>  $com_subsidy,
        'com_bo' =>  $com_bo,
        'fn_total' => $r->financeConfirm?->total ?? 0,
        'actually_received' => $r->financeConfirm?->actually_received ?? 0,
        'fn_diff' => $r->financeConfirm?->diff ?? 0,
        'remark' => $r->financeConfirm?->remark ?? '',

        // 'gross_profit' => $grossProfit,
        // 'gross_percent' => $grossProfitPercent,
        // 'RI' => $totalRI,
        // 'other' => $totalOther,
        // 'other_income' => $otherIncome,
        // 'com' => $totalFinanceCom,
        // 'extra' => $totalFinanceExtra,
        // 'selling_expense' => $sellingExpense,
        // 'net_profit' => $netProfit,
        // 'net_percent' => $netProfitPercent,
      ];
    });

    return view('purchase-order.report.gp.per-car', [
      'gpPer' => $data
    ]);
  }
}
