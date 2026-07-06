<?php

namespace App\Exports\license;

use App\Models\Salecar;
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

        $brandNames = config('brand.names', []);

        // brand ของการขายที่ผูกป้ายแต่ละใบ (ข้าม scope) เพื่อ detect การใช้ข้ามแบรนด์ในกองเดียวกัน (brand 3/Wuling)
        // โดยไม่เปิดเผยข้อมูลลูกค้าของอีกแบรนด์ในคอลัมน์อื่น
        $saleIds = $rows->pluck('currentHistory.saleID')->filter()->unique()->values();
        $occupantBrand = $saleIds->isNotEmpty()
            ? Salecar::withoutGlobalScopes()->whereIn('id', $saleIds)->pluck('brand', 'id')
            : collect();

        // brand 2 (GWM) ใช้ stock คนละกอง แต่เลข number อาจซ้ำ — ถ้าเลขเดียวกันถูก GWM ใช้อยู่ = GWM ครองเลขนั้น
        $gwmUsedNumbers = TbLicensePlate::withoutGlobalScopes()
            ->where('brand', 2)
            ->where('is_used', 1)
            ->pluck('number')
            ->flip();

        $data = $rows->map(function ($r) use ($brandNames, $occupantBrand, $gwmUsedNumbers) {
            $history = $r->currentHistory;
            $customerName = $r->is_used
                ? trim(
                    ($history->saleCarLic?->customer?->prefix?->Name_TH ?? '') . ' ' .
                        ($history->saleCarLic?->customer?->FirstName ?? '') . ' ' .
                        ($history->saleCarLic?->customer?->LastName ?? '')
                ) : '';

            $nameSale = $history?->saleCarLic?->saleUser?->name ?? '';

            // flag เมื่อป้ายเลขนี้ถูกแบรนด์อื่นใช้อยู่
            $usedBy = [];

            // 1) แบรนด์อื่นในกองเดียวกัน (Wuling/brand 3) ยืมใช้ป้ายใบนี้
            $occBrand = $history ? ($occupantBrand[$history->saleID] ?? null) : null;
            if ($r->is_used && $occBrand !== null && (int) $occBrand !== (int) $r->brand) {
                $usedBy[] = ($brandNames[$occBrand] ?? 'แบรนด์อื่น') . ' ใช้อยู่';
            }

            // 2) GWM (brand 2) กองแยก แต่เลขเดียวกันถูกใช้อยู่
            if ((int) $r->brand !== 2 && $gwmUsedNumbers->has($r->number)) {
                $usedBy[] = ($brandNames[2] ?? 'GWM') . ' ใช้อยู่';
            }

            $boundBrand = $usedBy ? implode(' / ', $usedBy) : '-';

            return [
                'customer' => $customerName,
                'bound_brand' => $boundBrand,
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
