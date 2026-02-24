<?php

namespace App\Exports\booking;

use App\Services\BookingReportQuery;
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
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TestDriveSheet  implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize, WithColumnFormatting
{
  public function title(): string
  {
    return 'Test Drive';
  }

  public function columnFormats(): array
  {
    return [
      'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
      'E' => NumberFormat::FORMAT_TEXT,
    ];
  }

  public function styles(Worksheet $sheet)
  {
    return [
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

        // freeze header
        $sheet->freezePane('A2');

        $sheet->getTabColor()->setRGB('ffc000');
      },
    ];
  }

  public function view(): View
  {
    $carOrders = BookingReportQuery::testDriveCars()
      ->get()
      ->sortBy([
        fn($o) => $o->model->Name_TH ?? '',
        fn($o) => $o->subModel->detail ?? '',
        fn($o) => $o->subModel->name ?? '',
        fn($o) => $o->color ?? '',
        fn($o) => $o->year ?? '',
        fn($o) => $o->option ?? '',
      ])
      ->values();

    $data = collect();

    foreach ($carOrders as $order) {

      $sale = $order->salecars
        ->first(fn($s) => !in_array($s->con_status, [5, 9]));

      $color = $order->brand == 2
        ? ($order->gwmColor->name ?? '-')
        : ($order->color ?? '-');

      $interiorColor = $order->brand == 2
        ? ($order->interiorColor->name ?? '-')
        : null;

      $data->push([
        'model'      => $order->model->Name_TH ?? '-',
        'subModel'   => $order->subModel
          ? $order->subModel->detail . ' - ' . $order->subModel->name
          : '-',

        'color'      => $color,
        'interior_color' => $interiorColor,
        'year'       => $order->year ?? '-',
        'option'     => $order->option ?? '-',
        'car_MSRP' => $order->car_MSRP ?? null,
        'cam_testdrive' => $order->cam_testdrive ?? '-',
        'mileage_test' => $order->mileage_test ?? '-',
        'order_status'     => $order->orderStatus->name ?? '-',
        'vin_number' => $order->vin_number ?? '-',
        // 'j_number'   => $order->j_number ?? '-',

        'customer' => $sale?->customer
          ? trim(
            ($sale->customer->prefix->Name_TH ?? '') . ' ' .
              ($sale->customer->FirstName ?? '') . ' ' .
              ($sale->customer->LastName ?? '')
          )
          : '',
        'status'      => $sale?->conStatus?->name ?? '',
        'sale'        => $sale?->saleUser?->name ?? '',
        'bookingDate' => $sale?->format_booking_date ?? '',
        'note_accessory' => $order->note_accessory ?? '-',

      ]);
    }

    return view('purchase-order.report.booking.test-drive', [
      'testD' => $data
    ]);
  }
}
