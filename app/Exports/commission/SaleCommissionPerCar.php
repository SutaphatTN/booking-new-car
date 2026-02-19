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

class SaleCommissionPerCar implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
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
    return 'ค่าคอมรายคัน';
  }

  public function styles(Worksheet $sheet)
  {
    return [
      //แถวบนสุด
      1 => [
        'font' => [],
        'fill' => [
          'fillType' => 'solid',
          'startColor' => ['rgb' => 'ffc000'],
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

        // ฟิลเตอร์เฉพาะ B1
        $sheet->setAutoFilter("B1:B{$highestRow}");

        // freeze header
        $sheet->freezePane('A2');

        // สี sheet
        $sheet->getTabColor()->setRGB('ffc000');

        // format comma
        $numberColumns = [
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
          'T'
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

    $rows = SaleCommissionQuery::base($this->user, true, $this->month, $this->year)
      ->get()
      ->sortBy(fn($r) => $r->saleUser->name);

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

      $carType = match ((int) ($r->carOrder->purchase_type ?? 0)) {
        1 => 'รถทดลองขับ',
        2 => 'รถปกติ',
        default => '-',
      };

      $balanceCampaign = min($r->balanceCampaign ?? 0, 2500);

      $accessoryCom = ($r->AccessoryGiftCom ?? 0) + ($r->AccessoryExtraCom ?? 0);
      $specialCom   = $r->CommissionSpecial ?? 0;
      $interestCom  = $r->remainingPayment->total_com ?? 0;
      $turnCarCom   = $r->turnCar->com_turn ?? 0;

      return [
        'branch' => $r->saleUser->branchInfo->name ?? '-',
        'saleName' => $r->saleUser->name ?? '-',

        'customer' => $customerName,
        'model' => $model,
        'subModel' => $subModel,
        'carType' => $carType,

        'balanceCampaign' => $balanceCampaign,
        'accessoryCom' => $accessoryCom,
        'specialCom' => $specialCom,
        'interestCom' => $interestCom,
        'turnCarCom' => $turnCarCom,
      ];
    });

    return view('purchase-order.commission.sale-report-per-car', [
      'commission' => $data
    ]);
  }
}
