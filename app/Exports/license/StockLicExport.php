<?php

namespace App\Exports\license;

use App\Models\TbLicensePlate;
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

class StockLicExport implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function title(): string
    {
        return 'Stock ป้ายแดง';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => 'ff8585'],
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

                // สี sheet
                $sheet->getTabColor()->setRGB('ff8585');
            },
        ];
    }

    public function view(): View
    {
        $rows = TbLicensePlate::with([
            'currentHistory.saleCarLic.customer.prefix',
            'currentHistory.saleCarLic.saleUser'
        ])->get();

        $data = $rows->map(function ($r) {
            $history = $r->currentHistory;
            $customerName = $r->is_used
                ? trim(
                    ($history->saleCarLic?->customer?->prefix?->Name_TH ?? '') . ' ' .
                        ($history->saleCarLic?->customer?->FirstName ?? '') . ' ' .
                        ($history->saleCarLic?->customer?->LastName ?? '')
                ) : '';

            $nameSale = $history?->saleCarLic?->saleUser?->name ?? '';

            return [
                'customer' => $customerName,
                'phone' => $r->is_used
                    ? ($history->saleCarLic?->customer?->formatted_mobile ?? '-')
                    : '-',
                'sale_lic' => $r->is_used
                    ? $nameSale
                    : '-',
                'red_license'     => $r->number,
                'delivery_date'       => $r->is_used
                    ? ($history?->saleCarLic?->format_delivery_date ?? '-')
                    : '-',
            ];
        });

        return view('number_register.license.report.stock', [
            'stockLic' => $data
        ]);
    }
}
