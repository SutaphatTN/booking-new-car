<?php

namespace App\Exports\gwm;

use App\Models\CarOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
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

class StockGWMSheet implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
  protected $fromDate;

  public function __construct($fromDate = null)
  {
    $this->fromDate = $fromDate ?? now()->startOfMonth()->format('Y-m-d');
  }

  public function title(): string
  {
    return 'stock';
  }

  public function styles(Worksheet $sheet)
  {
    return [
      //แถวบนสุด
      1 => [
        'font' => [],
        'fill' => [
          'fillType' => 'solid',
          'startColor' => ['rgb' => 'bee5f7'],
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

        // ไม่เอา filter ที่ no
        $sheet->setAutoFilter("B1:{$highestCol}{$highestRow}");

        // freeze header
        $sheet->freezePane('A2');

        // สี sheet
        $sheet->getTabColor()->setRGB('bee5f7');
      },
    ];
  }

  public function view(): View
  {
    $date = Carbon::createFromFormat('Y-m', $this->fromDate);

    $month = $date->month;
    $year  = $date->year;

    $startOfMonth = $date->copy()->startOfMonth();
    $endOfMonth = $date->copy()->endOfMonth();

    $carOrders = CarOrder::with([
      'historyCar' => function ($q) use ($month, $year) {
        $q->whereMonth('changed_at', $month)
          ->whereYear('changed_at', $year);
      },
      'salecars',
      'model',
      'subModel',
      'gwmColor',
      'interiorColor'
    ])
      ->whereNotNull('approver_date')
      ->whereNot('status', 'rejected')
      ->get();

    $data = $carOrders
      ->groupBy('subModel_id')
      ->map(function ($rows) use ($startOfMonth, $endOfMonth) {
        $first = $rows->first();

        $model = $first->model->Name_TH ?? '';
        $sub   = $first->subModel->name ?? '';
        $car   = "{$model} {$sub}";

        $color = $first->gwmColor->name ?? '-';
        $interiorColor = $first->interiorColor->name ?? '-';
        $his = $rows->flatMap(function ($r) {
          return $r->historyCar;
        })
          ->sortByDesc('changed_at')
          ->first();

        $stock = $rows->filter(function ($r) use ($startOfMonth, $endOfMonth) {

          if (!$r->approver_date || $r->approver_date > $endOfMonth) {
            return false;
          }

          $deliveredBefore = $r->salecars
            ->where('DeliveryDate', '<', $startOfMonth)
            ->count();

          return $deliveredBefore == 0;
        })->count();

        $historyIds = $rows->flatMap(function ($r) {
          return $r->historyCar->pluck('CarOrderID');
        });

        $deliveryIds = $rows->flatMap(function ($r) use ($startOfMonth, $endOfMonth) {
          return $r->salecars
            ->whereBetween('DeliveryDate', [$startOfMonth, $endOfMonth])
            ->pluck('CarOrderID');
        });

        $totalBooking = $historyIds
          ->merge($deliveryIds)
          ->unique()
          ->count();

        return [
          'model' => $car,
          'color' => $color,
          'interiorColor' => $interiorColor,
          'date' => $his?->format_changed_date ?? '-',
          'stock' => $stock,
          'total_booking' => $totalBooking,
          'total' => $stock - $totalBooking
        ];
      });

    return view('purchase-order.report.gwm.stock', [
      'stock' => $data
    ]);
  }
}
