<?php

namespace App\Exports;

use App\Models\CarOrder;
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

class AgingReportSheet  implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
  public function title(): string
  {
    return 'Aging Report';
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

        $sheet->setAutoFilter("B1:{$highestCol}{$highestRow}");

        // freeze header
        $sheet->freezePane('A2');

        $sheet->getTabColor()->setRGB('f985e2');
      },
    ];
  }

  public function view(): View
  {
    $today = Carbon::today();
    $orders = CarOrder::with('model')
      ->whereIn('status', ['approved', 'finished'])
      ->whereIn('purchase_type', ['2'])
      ->whereNot('car_status', 'Delivered')
      ->whereNotNull('order_stock_date')
      ->get();

    $grouped = [];

    foreach ($orders as $order) {

      $model = $order->model->Name_TH ?? 'ไม่ระบุ';

      $aging = Carbon::parse($order->order_stock_date)
        ->startOfDay()
        ->diffInDays($today);

      if (!isset($grouped[$model])) {
        $grouped[$model] = [
          'model' => $model,
          'b1' => 0,
          'b2' => 0,
          'b3' => 0,
          'b4' => 0,
          'b5' => 0,
          'total' => 0,
        ];
      }

      if ($aging <= 90) {
        $grouped[$model]['b1']++;
      } elseif ($aging <= 180) {
        $grouped[$model]['b2']++;
      } elseif ($aging <= 270) {
        $grouped[$model]['b3']++;
      } elseif ($aging <= 365) {
        $grouped[$model]['b4']++;
      } else {
        $grouped[$model]['b5']++;
      }

      $grouped[$model]['total']++;
    }

    //ยอดรวมด้านล่างสุด
    $grand = ['model' => 'รวมทั้งหมด', 'b1' => 0, 'b2' => 0, 'b3' => 0, 'b4' => 0, 'b5' => 0, 'total' => 0];
    foreach ($grouped as $g) {
      $grand['b1'] += $g['b1'];
      $grand['b2'] += $g['b2'];
      $grand['b3'] += $g['b3'];
      $grand['b4'] += $g['b4'];
      $grand['b5'] += $g['b5'];
      $grand['total'] += $g['total'];
    }

    $rows = collect($grouped)->sortBy('model')->values();
    $rows->push($grand);

    return view('purchase-order.report.aging-report', [
      'rows' => $rows
    ]);
  }
}
