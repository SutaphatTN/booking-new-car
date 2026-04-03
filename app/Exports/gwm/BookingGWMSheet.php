<?php

namespace App\Exports\gwm;

use App\Models\Salecar;
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

class BookingGWMSheet implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
  protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

  public function title(): string
  {
    return 'BookingWOStock';
  }

  public function styles(Worksheet $sheet)
  {
    return [
      //แถวบนสุด
      1 => [
        'font' => [],
        'fill' => [
          'fillType' => 'solid',
          'startColor' => ['rgb' => '84f196'],
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
        $sheet->getTabColor()->setRGB('84f196');
      },
    ];
  }

  public function view(): View
  {
    $salecars = Salecar::with([
      'model',
      'subModel',
      'gwmColor',
      'interiorColor',
    ])
      ->where('brand', 2)
      ->whereNULL('carOrderID')
      ->whereNotIn('con_status', [5, 9])
      ->get();

    $data = $salecars
      ->groupBy(function ($r) {
        return ($r->subModel_id ?? 'null') . '_' . ($r->gwm_color ?? 'null') . '_' . ($r->interior_color ?? 'null');
      })
      ->map(function ($rows) {
        $first = $rows->first();

        $mainModel     = $first->model->Name_TH ?? '-';
        $subModel      = $first->subModel->name ?? '-';
        $color         = $first->gwmColor->name ?? '-';
        $interiorColor = $first->interiorColor->name ?? '-';

        $total        = $rows->count();
        $withCar      = $rows->whereNotNull('CarOrderID')->count();
        $withoutCar   = $rows->whereNull('CarOrderID')->count();

        return [
          'mainModel'     => $mainModel,
          'subModel'      => $subModel,
          'color'         => $color,
          'interiorColor' => $interiorColor,
          'total'         => $total,
          'withCustomer'  => $withCar,
          'available'     => $withoutCar,
        ];
      });

    return view('purchase-order.report.gwm.booking', [
      'book' => $data
    ]);
  }
}
