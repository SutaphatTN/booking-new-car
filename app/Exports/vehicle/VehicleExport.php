<?php

namespace App\Exports\vehicle;

use App\Models\Salecar;
use App\Models\VehicleLicense;
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

class VehicleExport implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
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
        return 'ส่งเบิก/เคลียร์';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => 'ffacea'],
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
                $sheet->setAutoFilter("I1:I{$highestRow}");

                // freeze header
                $sheet->freezePane('A2');

                // สี sheet
                $sheet->getTabColor()->setRGB('ffacea');

                $numberColumns = [
                    'H',
                    'I',
                    'J',
                    'K',
                    'M',
                    'N',
                    'O',
                    'P',
                    'Q'
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
        $rows = VehicleLicense::with([
            'saleCar',
            'saleCar.customer.prefix',
            'saleCar.carOrder',
            'saleCar.provinces',
            'saleCar.licensePlateRed'
        ])->whereBetween('withdrawal_date', [
            $this->fromDate,
            $this->toDate
        ])->get();

        $data = $rows->map(function ($r) {
            $customerName = trim(
                ($r->saleCar?->customer?->prefix?->Name_TH ?? '') . ' ' .
                    ($r->saleCar?->customer?->FirstName ?? '') . ' ' .
                    ($r->saleCar?->customer?->LastName ?? '')
            );

            $vin = $r->saleCar?->carOrder?->vin_number ?? '-';
            $prov = $r->provincesV?->name ?? '-';
            $redLi = $r->saleCar?->licensePlateRed?->name ?? '-';
            $wli = $r->license_name ?? '' . $r->license_number ?? '';

            $statusMap = [
                'postage' => 'ส่งไปรษณีย์',
                'customer' => 'ลูกค้ารับเอง',
            ];
            return [
                'customer' => $customerName,
                'vin' => $vin,
                'province' => $prov,
                'red_license'     => $redLi,
                'w_license'       => $wli,
                'withdrawal_date'      => $r->format_withdrawal_date ?? '-',
                'withdrawal_check' => $r->withdrawal_check ?? '',
                'withdrawal_channel'       => $r->withdrawal_channel ?? '',
                'withdrawal_bill'       => $r->withdrawal_bill ?? '',
                'withdrawal_total'       => $r->withdrawal_total ?? '',
                'backup_clear_date'       => $r->format_backup_clear_date ?? '-',
                'receipt_check'       => $r->receipt_check  ?? '',
                'receipt_channel'       => $r->receipt_channel ?? '',
                'receipt_bill'       => $r->receipt_bill ?? '',
                'receipt_total'       => $r->receipt_total ?? '',
                'diff'      => $r->diff ?? '',
                'labe_status'       => $statusMap[$r->labe_status] ?? '-'
            ];
        });

        return view('number_register.vehicle.report.summary', [
            'vehicle' => $data
        ]);
    }
}
