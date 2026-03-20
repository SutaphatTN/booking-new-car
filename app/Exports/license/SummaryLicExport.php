<?php

namespace App\Exports\license;

use App\Models\LicensePlateHistory;
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

class SummaryLicExport implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    protected $fromDate;
    protected $toDate;

    public function __construct($fromDate = null, $toDate = null)
    {
        $this->fromDate = $fromDate ?? now()->startOfMonth()->format('Y-m-d');
        $this->toDate   = $toDate   ?? now()->format('Y-m-d');
    }

    public function title(): string
    {
        return 'History ป้ายแดง';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '944b4b'],
                ],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center',
                ]
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
                $sheet->getTabColor()->setRGB('944b4b');

                //check box ตรงกลาง
                $centerColumns = ['G', 'H', 'I'];
                foreach ($centerColumns as $col) {
                    $sheet->getStyle("{$col}1:{$col}{$highestRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // format comma
                $numberColumns = [
                    'K'
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
        $rows = LicensePlateHistory::with([
            'saleCarLic',
            'licenseLic'
        ])->where(function ($query) {
            $query->whereHas('saleCarLic', function ($q) {
                $q->whereBetween('DeliveryDate', [
                    $this->fromDate,
                    $this->toDate
                ]);
            });
        })->get();

        $data = $rows->map(function ($r) {
            $customerName = trim(
                ($r->saleCarLic?->customer?->prefix?->Name_TH ?? '') . ' ' .
                    ($r->saleCarLic?->customer?->FirstName ?? '') . ' ' .
                    ($r->saleCarLic?->customer?->LastName ?? '')
            );

            $nameSale = $r?->saleCarLic?->saleUser?->name ?? '';
            $statusType = [
                'cash' => 'เงินสด',
                'transfer' => 'โอน',
            ];

            return [
                'customer' => $customerName,
                'phone' => $r->saleCarLic?->customer?->formatted_mobile ?? '-',
                'sale_lic' => $nameSale,
                'red_license' => $r->licenseLic->number,
                'delivery_date' => $r?->saleCarLic?->format_delivery_date ?? '-',
                'license_front' => $r->license_red_front ? '✅' : '❌',
                'license_back'  => $r->license_red_back ? '✅' : '❌',
                'license_book'  => $r->license_red_book ? '✅' : '❌',
                // 'license_front' => $r->license_red_front ? '☑' : '☐',
                // 'license_back'  => $r->license_red_back ? '☑' : '☐',
                // 'license_book'  => $r->license_red_book ? '☑' : '☐',
                'refund_date' => $r->format_cust_refund_date,
                'cost' => $r->refund_amount,
                'type' => $statusType[$r->type_refund] ?? '-',
                'finance' => $r->financeUser->name ?? '-',
                'note' => $r->note ?? '-',
            ];
        });

        return view('number_register.license.report.summary', [
            'summary' => $data
        ]);
    }
}
