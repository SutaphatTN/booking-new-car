<?php

namespace App\Exports\customerTracking;

use App\Models\CustomerTracking;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * 1 sheet = 1 สถานที่ — รายชื่อลูกค้าที่ได้จากงานนั้นในเดือนที่เลือก
 * ข้อมูลถูก query + จัดกลุ่มมาแล้วจาก CustomerTrackingOfflinePlaceReport (ไม่ยิง query ซ้ำต่อ sheet)
 */
class CustomerTrackingOfflinePlaceSheet implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    public function __construct(
        protected string $month,
        protected Collection $trackings,
        protected string $sheetTitle
    ) {}

    public function title(): string
    {
        return $this->sheetTitle;
    }

    /** สถานะของรายการติดตาม — จองแล้วมาก่อนเสมอ (ส่งมอบแล้วระบบปิด tracking ด้วย cancelled_at ทีหลัง) */
    public static function statusLabel(CustomerTracking $t): string
    {
        if ($t->booked_at) {
            return 'จองแล้ว';
        }
        if ($t->cancelled_at) {
            return $t->end_type === 'finished' ? 'จบการติดตาม' : 'ยกเลิกการติดตาม';
        }
        return 'กำลังติดตาม';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font'      => ['bold' => true],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFE0B2']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
            2 => [
                'font'      => ['bold' => true],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
            3 => [
                'font'      => ['bold' => true],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFE0B2']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet      = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();

                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getFont()->setName('Angsana New')->setSize(14);

                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->setColor(new Color(Color::COLOR_BLACK));

                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(22);
                $sheet->getRowDimension(3)->setRowHeight(25);
                for ($row = 4; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }

                $sheet->setAutoFilter("A3:{$highestCol}3");
                $sheet->freezePane('A4');
                $sheet->getTabColor()->setRGB('FFE0B2');
            },
        ];
    }

    public function view(): View
    {
        $user  = Auth::user();
        $first = $this->trackings->first();
        $place = $first?->place;

        $range = '-';
        if ($place?->start_date) {
            $range = $place->start_date->format('d/m/Y');
            if ($place->end_date && $place->end_date->ne($place->start_date)) {
                $range .= ' – ' . $place->end_date->format('d/m/Y');
            }
        }

        // หมายเหตุ: บางคีย์ (created_at / follow_count / last_contact / status / comment) ถูก comment ไว้ในตาราง
        // แต่ยังคำนวณส่งไปเหมือนเดิม — เปิดคอลัมน์กลับที่ blade ได้เลยโดยไม่ต้องแก้ไฟล์นี้
        $no   = 1;
        $rows = $this->trackings->map(function ($t) use (&$no) {
            $customer = $t->customer;
            $fullName = $customer
                ? trim(($customer->prefix->Name_TH ?? '') . ' ' . $customer->FirstName . ' ' . $customer->LastName)
                : '-';

            // ความคืบหน้าล่าสุด — ใช้ detail ตัวท้ายสุด (รายงานนี้ดูผลของงาน ไม่ใช่ดูการกรอกครั้งแรก)
            $lastDetail = $t->details->sortBy('id')->last();

            // ข้อมูลรถ — บาง brand ใช้ field ต่างกัน (สี/สีภายใน/option)
            $color = $t->brand == 1
                ? ($t->color_text ?? '-')          // Mitsubishi: สีเป็น text อิสระ
                : ($t->wuColor?->name ?? '-');     // GWM / Wuling: เลือกจากรายการสี

            return [
                'no'             => $no++,
                'created_at'     => $t->created_at?->format('d/m/Y H:i') ?? '-',
                'full_name'      => $fullName,
                'phone'          => $customer?->formatted_mobile ?? '-',
                'sale'           => $t->sale?->name ?? '-',
                'source'         => $t->source?->name ?? '-',
                'model'          => $t->model?->Name_TH ?? '-',
                'sub_model'      => $t->subModel?->name ?? '-',
                'color'          => $color,
                'year'           => $t->year ?? '-',
                'interior_color' => $t->interiorColor?->name ?? '-', // ใช้เฉพาะ brand 2 (ดู $showInterior)
                'option'         => $t->option ?? '-',               // ใช้เฉพาะ brand 1 (ดู $showOption)
                'follow_count'   => $t->details->count(),
                'last_contact'   => $lastDetail?->contact_date ?? '-',
                'last_decision'  => $lastDetail?->decision?->name ?? '-',
                'test_date'      => $t->format_test_drive_date ?? '-',
                'status'         => self::statusLabel($t),
                'comment'        => $lastDetail?->comment_sale ?? '-',
            ];
        });

        return view('customer-tracking.excel-offline-place', [
            'rows'          => $rows,
            'monthLabel'    => Carbon::parse($this->month . '-01')->format('m/Y'),
            'placeName'     => $place?->location ?: '-',
            'placeType'     => $place?->source?->name ?? ($first?->source?->name ?? '-'),
            'placeRange'    => $range,
            'placeLasNo'    => $place?->las_number ?: '-',
            // คุมการแสดงคอลัมน์ตาม brand: สีภายใน = GWM(2), Option = Mitsubishi(1), Wuling(3) ไม่มีทั้งคู่
            'showInterior'  => $user->brand == 2,
            'showOption'    => $user->brand == 1,
        ]);
    }
}
