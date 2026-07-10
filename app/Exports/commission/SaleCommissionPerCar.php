<?php

namespace App\Exports\commission;

use App\Services\SaleCommissionQuery;
use App\Services\CarCommissionQuery;
use App\Services\ExtraBudgetLedger;
use App\Services\HeldCommissionQuery;
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

class SaleCommissionPerCar implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
  use BuildsCommissionReport;

  protected $user;
  protected $fromDate;
  protected $toDate;

  public function __construct($user, $fromDate = null, $toDate = null)
  {
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
    return 'ค่าคอมรายคัน';
  }

  /**
   * คอลัมน์ตาม brand — รายการเสริมรายเซลล์ (กั๊กยกมา/SSI/วินัย/lead/clip/หักอื่นๆ)
   * โชว์ "แถวแรกของเซลล์" ครั้งเดียว กัน Total ซ้ำ ; ส่วนค่าคอมรถ/กั๊กหัก เป็นรายคัน
   */
  protected function columns(): array
  {
    $b = $this->brand();

    $cols = [
      ['label' => 'สาขา',       'key' => 'branch',   'role' => 'info'],
      ['label' => 'ชื่อฝ่ายขาย', 'key' => 'saleName', 'role' => 'info'],
      ['label' => 'ชื่อลูกค้า',  'key' => 'customer', 'role' => 'info'],
      ['label' => 'ประเภทรถ',   'key' => 'carType',  'role' => 'info'],
      ['label' => 'รุ่นรถหลัก',  'key' => 'model',    'role' => 'info'],
      ['label' => 'รุ่นรถย่อย',  'key' => 'subModel', 'role' => 'info'],

      ['label' => 'ค่าคอมรถ',       'key' => 'carCommission',   'role' => 'recv', 'money' => true],
      ['label' => 'ยอดแบ่งงบเหลือ', 'key' => 'balanceCampaign', 'role' => 'recv', 'money' => true],
      // info เท่านั้น — ยอดนี้ถูกหักออกจาก "ยอดแบ่งงบเหลือ" ไปแล้ว (โชว์ให้รู้ว่าโดนหักอะไร ไม่หักซ้ำ)
      ['label' => 'หักเก็บงบเพิ่มเติม', 'key' => 'extraDeduct', 'role' => 'info', 'money' => true, 'num' => true],
      ['label' => 'คอมประดับยนต์',  'key' => 'accessoryCom',    'role' => 'recv', 'money' => true],
      ['label' => 'คอมอื่นๆ',       'key' => 'specialCom',      'role' => 'recv', 'money' => true],
      ['label' => 'คอมดอกเบี้ย',    'key' => 'interestCom',     'role' => 'recv', 'money' => true],
      ['label' => 'คอมรถเทิร์น',    'key' => 'turnCarCom',      'role' => 'recv', 'money' => true],
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
        'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'ffc000']],
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

        $sheet->setAutoFilter("B1:B{$highestRow}");
        $sheet->freezePane('A2');
        $sheet->getTabColor()->setRGB('ffc000');

        foreach ($moneyCols as $col) {
          $sheet->getStyle("{$col}2:{$col}{$highestRow}")->getNumberFormat()->setFormatCode('#,##0.00');
        }
      },
    ];
  }

  public function view(): View
  {
    $brand = $this->brand();

    $rows = SaleCommissionQuery::base($this->user, true, $this->fromDate, $this->toDate)
      ->get()
      ->sortBy(fn($r) => optional($r->saleUser)->name)
      ->values();

    $period = Carbon::parse($this->fromDate);
    $year   = (int) $period->year;
    $month  = (int) $period->month;

    $carCom = CarCommissionQuery::forMonth($year, $month)['perSale'];
    $ssiPer = SsiCommissionQuery::forPeriod($year, $month)['perSale'];
    $adjust = SaleCommissionMonthly::where('year', $year)->where('month', $month)
      ->get()->keyBy('SaleID');

    // ค่ารายเซลล์/เดือน โชว์ครั้งเดียว (แถวแรกของเซลล์) กัน Total ซ้ำ
    $seen = [];

    $data = $rows->map(function ($r) use ($brand, $carCom, $ssiPer, $adjust, &$seen) {

      $customerName = trim(
        ($r->customer->prefix->Name_TH ?? '') . ' ' .
          ($r->customer->FirstName ?? '') . ' ' .
          ($r->customer->LastName ?? '')
      );

      $saleId = (int) $r->SaleID;
      $sub = $r->carOrder->subModel->name ?? '-';
      $detailModel = $r->carOrder->subModel->detail ?? null;

      // ค่าคอมรถ (คอมรายคันรถปกติ) รายคัน — นับเฉพาะ Retail + Normal + ไม่ใช่ dealer (ตรงกับ CarCommissionQuery)
      $entry = CarCommissionQuery::entry($carCom, $saleId, (int) $r->brand);
      $src = optional($r->carOrder)->purchase_source;
      $isCounted = ((int) $r->type_sale === CarCommissionQuery::SALE_TYPE_NORMAL)
        && ((int) optional($r->carOrder)->purchase_type === CarCommissionQuery::PURCHASE_TYPE_RETAIL)
        && ($src !== CarCommissionQuery::SOURCE_DEALER);
      $carCommission = 0.0;
      if ($entry && $isCounted) {
        $carCommission = ($entry['mode'] ?? 'volume') === 'model'
          ? CarCommissionQuery::modelRate((int) $r->brand, $r->model_id !== null ? (int) $r->model_id : null)
          : (float) ($entry['rate'] ?? 0);
      }

      $row = [
        'branch'   => optional($r->saleUser?->branchInfo)->name ?? '-',
        'saleName' => optional($r->saleUser)->name ?? '-',
        'customer' => $customerName ?: '-',
        'carType'  => match ((int) ($r->carOrder->purchase_type ?? 0)) {
          1 => 'รถทดลองขับ',
          2 => 'รถปกติ',
          default => '-',
        },
        'model'    => $r->carOrder->model->Name_TH ?? '-',
        'subModel' => $detailModel ? "{$detailModel} - {$sub}" : $sub,

        'carCommission'   => $carCommission,
        'balanceCampaign' => $r->effectiveBalanceCommission(),
        'extraDeduct'     => ExtraBudgetLedger::absorbedFor($r) ?: null,
        'accessoryCom'    => $r->effectiveAccessoryCommission(),
        'specialCom'      => $r->effectiveSpecialCommission(),
        'interestCom'     => $r->remainingPayment->total_com ?? 0,
        'turnCarCom'      => $r->turnCar->com_turn ?? 0,
      ];

      // per-sale : โชว์แถวแรกของเซลล์ครั้งเดียว
      $first = !isset($seen[$saleId]);
      $seen[$saleId] = true;
      $adj = $adjust->get($saleId);

      $row['deductAbsence'] = $first ? (float) ($adj->deduct_absence ?? 0) : 0.0;

      if ($brand === 1) {
        $row['ssi'] = $first ? (float) ($ssiPer[$saleId]['amount'] ?? 0) : 0.0;
      } elseif ($brand === 2) {
        $row['comDiscipline'] = $first ? (float) ($adj->com_discipline ?? 0) : 0.0;
        $row['comLead']       = $first ? (float) ($adj->com_lead ?? 0) : 0.0;
        $row['comClip']       = $first ? (float) ($adj->com_clip ?? 0) : 0.0;
      }

      return $row;
    });

    $payload = $this->buildReport($this->columns(), $data);

    return view('purchase-order.report.commission.sale-report-generic', $payload);
  }
}
