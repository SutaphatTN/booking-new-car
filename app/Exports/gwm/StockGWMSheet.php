<?php

namespace App\Exports\gwm;

use App\Models\CarOrder;
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

class StockGWMSheet implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
  protected $request;

  public function __construct($request)
  {
    $this->request = $request;
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

        $sheet->setAutoFilter("A1:{$highestCol}{$highestRow}");

        // freeze header
        $sheet->freezePane('A2');

        // สี sheet
        $sheet->getTabColor()->setRGB('bee5f7');
      },
    ];
  }

  public function view(): View
  {
    $carOrders = CarOrder::withoutGlobalScope('userAccess')
      ->with([
        'historyCar',
        'salecars',
        'model',
        'subModel',
        'gwmColor',
        'interiorColor',
        'branchInfo',
      ])
      ->where('brand', 2)
      ->whereNotNull('approver_date')
      ->whereNot('status', 'rejected')
      ->whereNot('car_status', 'Delivered')
      ->get();

    $data = $carOrders
      ->groupBy(function ($r) {
        return $r->subModel_id . '_' . $r->gwm_color . '_' . $r->interior_color . '_' . $r->branch;
      })
      ->map(function ($rows) {
        $first = $rows->first();

        $branch        = $first->branchInfo->name ?? '-';
        $mainModel     = $first->model->Name_TH ?? '';
        $subModel      = $first->subModel->name ?? '';
        $color         = $first->gwmColor->name ?? '-';
        $interiorColor = $first->interiorColor->name ?? '-';

        $stock = $rows->filter(function ($r) {
          $deliveredBefore = $r->salecars
            ->where(function ($q) {
              $q->whereNull('DeliveryDate')
                ->orWhere('DeliveryDate', '');
            })
            ->count();
          return $deliveredBefore == 0;
        })->count();

        $historyIds = $rows->flatMap(function ($r) {
          return $r->historyCar->pluck('CarOrderID');
        });

        $deliveryIds = $rows->flatMap(function ($r) {
          return $r->salecars
            ->where(function ($q) {
              $q->whereNull('DeliveryDate')
                ->orWhere('DeliveryDate', '');
            })
            ->pluck('CarOrderID');
        });

        $withCustomer = $historyIds->merge($deliveryIds)->unique()->count();

        return [
          'branch'        => $branch,
          'mainModel'     => $mainModel,
          'subModel'      => $subModel,
          'color'         => $color,
          'interiorColor' => $interiorColor,
          'total'         => $stock,
          'withCustomer'  => $withCustomer,
          'available'     => $stock - $withCustomer,
        ];
      });

    return view('purchase-order.report.gwm.stock', [
      'stock' => $data
    ]);
  }
}
