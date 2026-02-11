<?php

namespace App\Exports;

use App\Models\CarOrder;
use App\Models\Salecar;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BookingByModelSheet implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize, WithColumnFormatting
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

  public function columnFormats(): array
  {
    return [
      'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
      'D' => NumberFormat::FORMAT_TEXT,
    ];
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
      ->with([
        'model',
        'subModel',
        'orderStatus',
        'salecars.customer.prefix',
        'salecars.saleUser',
        'salecars.carOrderHistories',
        'salecars.conStatus',
      ])
      ->where('model_id', $this->model->id)
      ->whereIn('status', ['approved', 'finished'])
      ->whereIn('purchase_type', ['2'])
      ->whereNot('car_status', 'Delivered')
      ->get()
      ->sortBy([
        fn($o) => $o->subModel->detail ?? '',
        fn($o) => $o->subModel->name ?? '',
        fn($o) => $o->color ?? '',
        fn($o) => $o->year ?? '',
        fn($o) => $o->option ?? '',
      ])
      ->values();

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
        'car_MSRP' => $order->car_MSRP ?? null,
        // 'purchase_type' =>  $order->purchase_type ?? '-',
        'order_status'     => $order->orderStatus->name ?? '-',
        'vin_number' => $order->vin_number ?? '-',
        'j_number'   => $order->j_number ?? '-',

        'order_stock_date'   => $order->format_order_stock_date ?? '-',
        'aging_date' => $order->order_stock_date
          ? Carbon::parse($order->order_stock_date)
          ->startOfDay()
          ->diffInDays(now()->startOfDay()) . ' วัน'
          : '-',

        // 'order_stock_date'   => $order->format_order_stock_date ?? '-',
        // 'aging_date' => $order->order_stock_date
        //   ? Carbon::parse($order->order_stock_date)
        //   ->startOfDay()
        //   ->diffInDays(now()->startOfDay()) . ' วัน'
        //   : '-',

        'customer' => $sale?->customer
          ? trim(
            ($sale->customer->prefix->Name_TH ?? '') . ' ' .
              ($sale->customer->FirstName ?? '') . ' ' .
              ($sale->customer->LastName ?? '')
          )
          : '',
        'con_status'  => $sale?->conStatus?->name ?? '',
        'sale'        => $sale?->saleUser?->name ?? '',
        'bookingDate' => $sale?->format_booking_date ?? '',
      ]);
    }

    //ยังไม่ผูกรถ
    $orphanSales = Salecar::with([
      'customer.prefix',
      'saleUser',
      'conStatus'
    ])
      ->whereNull('CarOrderID')
      ->where('model_id', $this->model->id)
      ->whereNotIn('con_status', [5, 9])
      ->get();

    foreach ($orphanSales as $sale) {

      $rows->push([
        'subModel'    => $sale->subModel
          ? $sale->subModel->detail . ' - ' . $sale->subModel->name
          : 'ยังไม่ผูกรถ',

        'color'       => $sale->Color ?? '-',
        'year'        => $sale->Year ?? '-',
        'option'      => $sale->option ?? '-',

        'car_MSRP'    => $sale->price_sub ?? '-',
        'order_status' => '',
        'vin_number'  => '',
        'j_number'    => '',
        'order_stock_date'   => '',
        'aging_date'  => '',

        'customer' => $sale->customer
          ? trim(
            ($sale->customer->prefix->Name_TH ?? '') . ' ' .
              ($sale->customer->FirstName ?? '') . ' ' .
              ($sale->customer->LastName ?? '')
          )
          : '',

        'con_status'  => $sale->conStatus?->name ?? '',
        'sale'        => $sale->saleUser?->name ?? '',
        'bookingDate' => $sale->format_booking_date ?? '',
      ]);
    }

    return view('purchase-order.report.booking-model', [
      'rows' => $rows
    ]);
  }
}
