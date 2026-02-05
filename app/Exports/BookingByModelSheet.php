<?php

namespace App\Exports;

use App\Models\CarOrder;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BookingByModelSheet implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
  protected $model;

  public function __construct($model)
  {
    $this->model = $model;
  }

  public function title(): string
  {
    return $this->model->initials;
  }

  public function styles(Worksheet $sheet)
  {
    return [
      //แถวบนสุด
      1 => [
        'font' => [],
        'fill' => [
          'fillType' => 'solid',
          'startColor' => ['rgb' => 'd8e4bc'],
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

        $sheet->setAutoFilter("B1:{$highestCol}{$highestRow}");

        // freeze header
        $sheet->freezePane('A2');

        $colorMap = [
          'ELL' => 'ff0000',
          'EL' => 'ffff00',
          'QX' => 'e26b0a',
          'SU-DC' => '92cddc',
          'SU-MC' => '963634',
          'SU-SC' => '7030a0',
          'X-FORCE' => '0070c0',
          'RN' => '00b050',
        ];

        $sheet->getTabColor()->setRGB(
          $colorMap[$this->model->initials] ?? '808080'
        );
      },
    ];
  }

  public function view(): View
  {
    $carOrders = CarOrder::query()
      ->select('car_order.*')
      ->leftJoin('tb_subcarmodels as sm', 'sm.id', '=', 'car_order.subModel_id')
      ->with([
        'model',
        'subModel',
        'salecars.customer.prefix',
        'salecars.saleUser',
      ])
      ->where('car_order.model_id', $this->model->id)
      ->whereIn('car_order.status', ['approved', 'finished'])
      ->whereNot('car_order.car_status', 'Delivered')
      ->orderBy('sm.detail')
      ->orderBy('sm.name')
      ->orderBy('car_order.color')
      ->orderBy('car_order.year')
      ->orderBy('car_order.option')
      ->get();

    $rows = collect();

    foreach ($carOrders as $order) {

      $sale = $order->salecars
        ->first(fn($s) => !in_array($s->con_status, [5, 9]));

      $rows->push([
        'subModel'    => $order->subModel
          ? $order->subModel->detail . ' - ' . $order->subModel->name
          : '-',
        'color'       => $order->color ?? '-',
        'year'        => $order->year ?? '-',
        'option'      => $order->option ?? '-',
        'car_MSRP'     => $order->car_DNP !== null ? number_format($order->car_DNP, 2) : '-',
        'purchase_type' =>  $order->purchase_type ?? '-',
        'order_status'     => $order->orderStatus->name ?? '-',
        'vin_number' => $order->vin_number ?? '-',
        'j_number'   => $order->j_number ?? '-',

        'customer'    => $sale
          ? $sale->customer->prefix->Name_TH . ' '
          . $sale->customer->FirstName . ' '
          . $sale->customer->LastName
          : '',
        'con_status'  => $sale?->conStatus?->name ?? '',
        'sale'        => $sale?->saleUser?->name ?? '',
        'bookingDate' => $sale?->format_booking_date ?? '',
      ]);
    }

    return view('purchase-order.report.booking-model', [
      'rows' => $rows
    ]);
  }
}
