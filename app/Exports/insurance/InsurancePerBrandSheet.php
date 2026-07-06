<?php

namespace App\Exports\insurance;

use App\Models\Salecar;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

class InsurancePerBrandSheet implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize, WithColumnFormatting
{
  // 1 = MITSUBISHI, 2 = GWM, 3 = Wuling
  public const BRANDS = [
    1 => 'MITSUBISHI',
    2 => 'GWM',
    3 => 'Wuling',
  ];

  protected $brand;
  protected $fromDate;

  public function __construct($brand, $fromDate = null)
  {
    $this->brand    = $brand;
    $this->fromDate = $fromDate ?? now()->startOfMonth()->format('Y-m');
  }

  public function title(): string
  {
    return self::BRANDS[$this->brand] ?? ('Brand ' . $this->brand);
  }

  public function columnFormats(): array
  {
    // เลขบัตรประชาชน / เบอร์โทร / เลขตัวถัง / เลขเครื่อง = ข้อความ กันเลขยาวกลายเป็น scientific
    return [
      'C' => NumberFormat::FORMAT_TEXT,
      'D' => NumberFormat::FORMAT_TEXT,
      'Q' => NumberFormat::FORMAT_TEXT,
      'R' => NumberFormat::FORMAT_TEXT,
    ];
  }

  public function styles(Worksheet $sheet)
  {
    return [
      1 => [
        'font' => ['bold' => true],
        'fill' => [
          'fillType' => 'solid',
          'startColor' => ['rgb' => 'a4d4ae'],
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

        // กึ่งกลางแนวตั้ง
        $sheet->getStyle("A1:{$highestCol}{$highestRow}")
          ->getAlignment()
          ->setVertical(Alignment::VERTICAL_CENTER);

        // เส้นกรอบ
        $sheet->getStyle("A1:{$highestCol}{$highestRow}")
          ->getBorders()
          ->getAllBorders()
          ->setBorderStyle(Border::BORDER_THIN)
          ->setColor(new Color(Color::COLOR_BLACK));

        // ความสูง row
        $sheet->getRowDimension(1)->setRowHeight(25);
        for ($row = 2; $row <= $highestRow; $row++) {
          $sheet->getRowDimension($row)->setRowHeight(20);
        }

        // freeze header
        $sheet->freezePane('A2');

        // สี tab
        $sheet->getTabColor()->setRGB('a4d4ae');

        // ราคาขายรถ (คอลัมน์ S) = มี comma
        $sheet->getStyle("S2:S{$highestRow}")
          ->getNumberFormat()
          ->setFormatCode('#,##0.00');

        // บังคับ เลขบัตรประชาชน (C) / เลขตัวถัง (Q) / เลขเครื่อง (R) เป็นข้อความ
        // กันเลขยาวกลายเป็น scientific (E+12) — แสดงเลขเต็ม
        for ($row = 2; $row <= $highestRow; $row++) {
          foreach (['C', 'Q', 'R'] as $col) {
            $cell = $sheet->getCell("{$col}{$row}");
            $val = $cell->getValue();
            if ($val !== null && $val !== '' && is_numeric($val)) {
              $cell->setValueExplicit(sprintf('%.0f', $val), DataType::TYPE_STRING);
            }
          }
        }
      },
    ];
  }

  public function view(): View
  {
    [$year, $month] = explode('-', $this->fromDate);

    // ดึงทุก brand ข้ามขอบเขต brand ของ user ที่ล็อกอิน (รายงานนี้ admin เห็นทุก brand)
    $rows = Salecar::withoutGlobalScope('userAccess')
      ->with([
        'customer.prefix',
        'customer.documentAddress',
        'customer.currentAddress',
        // ดึง รุ่น/รุ่นย่อย/ปี/สี/เลขวิน/เลขเครื่อง/ราคา ผ่าน car_order ที่ผูกไว้ (CarOrderID)
        // carOrder มี global scope userAccess (brand/branch) — ต้อง bypass ไม่งั้น brand อื่นได้ null
        // model/subModel/gwmColor มี global scope brandAccess (กรอง brand) — ต้อง bypass ด้วย
        // ไม่งั้น admin brand=1 ดึงรุ่น/สี ของ brand 2,3 ไม่ได้ (กลายเป็น null)
        'carOrder' => fn($q) => $q->withoutGlobalScope('userAccess')->with([
          'model'    => fn($q) => $q->withoutGlobalScope('brandAccess'),
          'subModel' => fn($q) => $q->withoutGlobalScope('brandAccess'),
          'gwmColor' => fn($q) => $q->withoutGlobalScope('brandAccess'),
        ]),
      ])
      ->where('brand', $this->brand)
      ->whereNotNull('CarOrderID')
      ->whereNotNull('DeliveryDate')
      ->whereYear('DeliveryDate', $year)
      ->whereMonth('DeliveryDate', $month)
      ->orderBy('DeliveryDate')
      ->get();

    $data = $rows->map(function ($r) {
      $deliveryDate = $r->DeliveryDate ? Carbon::parse($r->DeliveryDate) : null;
      $order = $r->carOrder;

      // สีรถ(ไทย): brand 2,3 ใช้ gwmColor, นอกนั้นใช้ color — ดึงจาก car_order
      $color = in_array($r->brand, [2, 3, 4])
        ? ($order?->gwmColor?->name ?? '-')
        : ($order?->color ?? '-');

      $sub = $order?->subModel?->name ?? '-';
      $detailModel = $order?->subModel?->detail ?? null;
      $subModel = $detailModel ? "{$detailModel} - {$sub}" : $sub;

      $customerName = trim(
        ($r->customer->FirstName ?? '') . ' ' . ($r->customer->LastName ?? '')
      );

      return [
        'prefix'        => $r->customer?->prefix?->Name_TH ?? '',
        'customer'      => $customerName,
        'id_card'       => $r->customer?->IDNumber ?? '',
        'phone'         => $r->customer?->formatted_mobile ?? '',
        // ที่อยู่เอกสารก่อน ถ้าไม่มีให้ใช้ที่อยู่ปัจจุบันแทน
        'address'       => ($r->customer?->documentAddress ?? $r->customer?->currentAddress)?->full_address ?? '',
        'insurer'       => '',                  // บริษัทประกัน — เว้นว่าง
        'insurer_class' => '',                  // ชั้นประกัน — เว้นว่าง
        'insure_start'  => $deliveryDate ? $deliveryDate->format('d/m/Y') : '',
        'insure_end'    => $deliveryDate ? $deliveryDate->copy()->addYear()->format('d/m/Y') : '',
        'insure_sum'    => '',                  // ทุนประกัน — เว้นว่าง
        'usage_code'    => '',                  // รหัสประเภทการใช้งานรถ — เว้นว่าง
        'brand'         => self::BRANDS[$r->brand] ?? '',
        'model'         => $order?->model?->Name_TH ?? '-',
        'subModel'      => $subModel,
        'year'          => $order?->year ?? '-',
        'color'         => $color,
        'vin_number'    => $order?->vin_number ?? '',
        'engine_number' => $order?->engine_number ?? '',
        'sale_price'    => $order?->car_MSRP ?? '',
        'delivery_date' => $deliveryDate ? $deliveryDate->format('d/m/Y') : '',
        'has_accessory' => '',                  // ติดอุปกรณ์หรือไม่ — เว้นว่าง
        'extra_accessory' => '',                // อุปกรณ์ติดตั้งเพิ่มเติม — เว้นว่าง
        'responsible'   => '',                  // ผู้รับผิดชอบ — เว้นว่าง
      ];
    });

    return view('purchase-order.report.insurance.summary', [
      'rows' => $data,
    ]);
  }
}
