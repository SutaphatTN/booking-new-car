<?php

namespace App\Exports\invoice;

use App\Models\InvoiceCustomer;
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

class InvoiceExport implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    protected $fromDate;
    protected $toDate;

    public function __construct($fromDate = null, $toDate = null)
    {
        $this->fromDate = $fromDate;
        $this->toDate   = $toDate;
    }

    public function title(): string
    {
        return 'ข้อมูลใบสั่งซื้อ';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            //แถวบนสุด
            1 => [
                'font' => [],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => 'ffa2be'],
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

                // ฟิลเตอร์เฉพาะ I
                $sheet->setAutoFilter("B1:{$highestCol}{$highestRow}");

                // freeze header
                $sheet->freezePane('A2');

                // สี sheet
                $sheet->getTabColor()->setRGB('ffa2be');

                // format comma
                $numberColumns = [
                    'H'
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

        $from = $this->fromDate;
        $to   = $this->toDate;

        $rows = InvoiceCustomer::with(['accessories.partner', 'insertInvoice'])
            ->whereNotNull('date')
            ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('date', '<=', $to))
            ->orderByDesc('date')
            ->get();

        $data = $rows->map(function ($item, $i) {
            $firstAcc = $item->accessories->first();

            return [
                'date'                 => $item->format_date ?? '-',
                'partner_name'         => $firstAcc?->partner?->name ?? '-',
                'detail'               => $firstAcc?->detail ?? '-',
                'vin_number'           => $item->vin_number ?? '-',
                'engine_number'        => $item->engine_number ?? '-',
                'customer_name'        => $item->customer_name,
                'total_price'          => $item->total_price ?? '-',
                'receipt_confirmed_at' => $item->format_receipt_confirmed ?? '-',
                'user_insert'          => $item->insertInvoice?->name ?? '-',
            ];
        });

        return view('invoice.report.summary', [
            'invoice' => $data
        ]);
    }
}
