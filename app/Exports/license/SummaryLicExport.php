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
                $centerColumns = ['I', 'J', 'K'];
                foreach ($centerColumns as $col) {
                    $sheet->getStyle("{$col}1:{$col}{$highestRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // format comma
                $numberColumns = [
                    'M'
                ];

                foreach ($numberColumns as $col) {
                    $sheet->getStyle("{$col}2:{$col}{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                }
            },
        ];
    }

    /**
     * ลิงก์ไฟล์แนบสำหรับเซลล์ Excel — <a href> จะถูกแปลงเป็น hyperlink กดได้จริงตอนเซฟเป็น xlsx
     * (เช็คแล้วว่า FromView ของ maatwebsite แปลงให้ ; ข้อความในแท็กกลายเป็นค่าของเซลล์)
     * 1 เซลล์มีลิงก์ได้อันเดียว — ถ้าแนบหลายไฟล์จะลิงก์ไฟล์แรกแล้วต่อท้ายว่าเหลืออีกกี่ไฟล์
     */
    private function fileLink($files, ?string $proxyBase): string
    {
        $files = is_array($files) ? array_values($files) : [];

        if (!$files || !$proxyBase) {
            return '-';
        }

        $first = $files[0];
        $url  = is_array($first) ? ($first['url'] ?? '') : $first;
        $name = is_array($first) ? ($first['name'] ?? 'ไฟล์แนบ') : 'ไฟล์แนบ';

        if (!$url) {
            return '-';
        }

        $href  = $proxyBase . '/' . rawurlencode($name) . '?url=' . urlencode($url);
        $label = count($files) > 1 ? $name . ' (+' . (count($files) - 1) . ' ไฟล์)' : $name;

        return '<a href="' . e($href) . '">' . e($label) . '</a>';
    }

    public function view(): View
    {
        $rows = LicensePlateHistory::with([
            // customer.prefix ต้อง eager load ด้วย ไม่งั้นยิง query เพิ่มทุกแถวตอนดึงชื่อลูกค้า
            'saleCarLic.customer.prefix',
            // ข้าม brand scope ของป้าย — ประวัติอาจอ้างป้ายที่ยืมมาแล้วคืนเจ้าของไปแล้ว
            'licenseLic' => fn($q) => $q->withoutGlobalScope('brandAccess'),
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

            $nameSale = $r?->saleCarLic?->report_sale_name ?? '';
            $statusType = [
                'cash' => 'เงินสด',
                'transfer' => 'โอน',
            ];

            // หลักฐานโอนเงินค่าป้ายแดงอยู่บน "ใบขาย" ส่วนสลิปคืนเงินอยู่บน "ประวัติป้าย" — คนละ proxy กัน
            $payProxy = $r->saleCarLic
                ? route('purchase-order.proxy', $r->saleCarLic->id)
                : null;
            $refundProxy = route('vehicle.license.slip-proxy', $r->id);

            return [
                'customer' => $customerName,
                'phone' => $r->saleCarLic?->customer?->formatted_mobile ?? '-',
                'sale_lic' => $nameSale,
                'red_license' => $r->licenseLic?->number ?? '-',
                'pay_date' => $r->saleCarLic?->red_license_pay_date
                    ? \Illuminate\Support\Carbon::parse($r->saleCarLic->red_license_pay_date)->format('d-m-Y')
                    : '-',
                'pay_slip' => $this->fileLink($r->saleCarLic?->red_license_slip_url, $payProxy),
                'delivery_date' => $r?->saleCarLic?->format_delivery_date ?? '-',
                'license_front' => $r->license_red_front ? '✅' : '❌',
                'license_back'  => $r->license_red_back ? '✅' : '❌',
                'license_book'  => $r->license_red_book ? '✅' : '❌',
                'refund_date' => $r->format_cust_refund_date,
                'cost' => $r->refund_amount,
                'type' => $statusType[$r->type_refund] ?? '-',
                'refund_slip' => $this->fileLink($r->refund_slip_url, $refundProxy),
                'finance' => $r->financeUser->name ?? '-',
                'note' => $r->note ?? '-',
            ];
        });

        return view('number_register.license.report.summary', [
            'summary' => $data
        ]);
    }
}
