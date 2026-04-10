<?php

namespace App\Exports\vehicle;

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

class VehicleLicensePlateExport implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    public function title(): string
    {
        return 'รายงานป้ายทะเบียน';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => 'BDD7EE'],
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

                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getFont()
                    ->setName('Angsana New')
                    ->setSize(14);

                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->setColor(new Color(Color::COLOR_BLACK));

                $sheet->getRowDimension(1)->setRowHeight(25);
                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }

                $sheet->freezePane('A2');
                $sheet->getTabColor()->setRGB('BDD7EE');
            },
        ];
    }

    public function view(): View
    {
        $records = VehicleLicense::with([
            'saleCar.customer.prefix',
            'saleCar.carOrder',
            'provincesV',
        ])
            ->whereNotNull('license_name')
            ->whereNotNull('license_number')
            ->get();

        $rows = $records->map(function ($r) {
            $customer = trim(
                ($r->saleCar?->customer?->prefix?->Name_TH ?? '') . ' ' .
                ($r->saleCar?->customer?->FirstName ?? '') . ' ' .
                ($r->saleCar?->customer?->LastName ?? '')
            );

            return [
                'customer'       => $customer,
                'vin'            => $r->saleCar?->carOrder?->vin_number ?? '-',
                'engine_number'  => $r->saleCar?->carOrder?->engine_number ?? '-',
                'backup_clear_date' => $r->format_backup_clear_date ?? '-',
                'license_plate'  => trim(($r->license_name ?? '') . ' ' . ($r->license_number ?? '')),
                'license_province' => $r->provincesV?->name ?? '-',
            ];
        });

        return view('number_register.vehicle.report.license-plate', compact('rows'));
    }
}
