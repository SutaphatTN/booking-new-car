<?php

namespace App\Exports\ssi;

use App\Models\SsiRecord;
use App\Models\TbProvinces;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SsiReportExport implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    public function __construct(private string $dateFrom, private string $dateTo) {}

    public function title(): string
    {
        return 'SSI หลังส่งมอบ';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFF9C4']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
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
                    ->getFont()->setName('Angsana New')->setSize(14);

                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->setColor(new Color(Color::COLOR_BLACK));

                $sheet->getStyle("A1:{$highestCol}1")
                    ->getAlignment()->setWrapText(true);

                $sheet->getRowDimension(1)->setRowHeight(35);
                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(-1);
                    $sheet->getStyle("A{$row}:{$highestCol}{$row}")
                        ->getAlignment()->setWrapText(true);
                }

                $sheet->setAutoFilter("A1:{$highestCol}1");
                $sheet->freezePane('A2');
                $sheet->getTabColor()->setRGB('FFF176');
            },
        ];
    }

    public function view(): View
    {
        $records = SsiRecord::with([
            'salecar.customer.prefix',
            'salecar.saleUser',
            'salecar.model',
            'salecar.subModel',
            'salecar.carOrder',
            'salecar.preDeliveryInspection',
            'contacts',
            'assessment',
        ])
            ->whereHas('salecar', fn($q) => $q
                ->whereDate('DeliveryDate', '>=', $this->dateFrom)
                ->whereDate('DeliveryDate', '<=', $this->dateTo)
            )
            ->get()
            ->sortBy(fn($r) => $r->salecar?->DeliveryDate)
            ->values();

        $provinces = TbProvinces::all()->keyBy('id');

        // คอลัมน์คะแนน SSI รายข้อ (1-5) แยกตาม brand
        $brand        = Auth::user()->brand ?? 1;
        $scoreColumns = $this->scoreColumns($brand);

        $no = 1;
        $rows = $records->map(function ($rec) use (&$no, $provinces, $scoreColumns) {
            $s = $rec->salecar;
            if (!$s) return null;

            $c        = $s->customer;
            $fullName = $c
                ? trim(($c->prefix->Name_TH ?? '') . ' ' . $c->FirstName . ' ' . $c->LastName)
                : '-';

            $model     = $s->model?->Name_TH ?? '-';
            $subModel  = $s->subModel?->name ?? '';
            $modelFull = $subModel ? "{$model} / {$subModel}" : $model;

            $contacts = $rec->contacts->sortBy('contact_date')->values();

            $latestContact     = $contacts->last();
            $latestContactDate = $latestContact
                ? Carbon::parse($latestContact->contact_date)->format('d/m/Y')
                : '-';

            $contactHistory = $contacts->map(function ($cnt, $i) {
                $d = $cnt->contact_date
                    ? Carbon::parse($cnt->contact_date)->format('d/m/Y')
                    : '-';
                $line = 'โทรครั้งที่ ' . ($i + 1) . ' วันที่ ' . $d;
                if (!empty($cnt->remark)) {
                    $line .= ' (' . $cnt->remark . ')';
                }
                return $line;
            })->implode("\n") ?: '-';

            $contactStatus = $contacts->map(function ($cnt) {
                if (!$cnt->contacted) return 'ติดต่อไม่ได้';
                if ($cnt->interview_success === null) return 'ติดต่อได้ ยังไม่ได้สัมภาษณ์';
                return $cnt->interview_success ? 'ติดต่อได้ สัมภาษณ์เรียบร้อย' : 'ติดต่อได้ ไม่สะดวกคุย';
            })->implode("\n") ?: '-';

            $ssiScore = $rec?->ssiScorePercent() ?? 0;

            // คะแนนรายข้อ (1-5) — ช่องที่ยังไม่ได้ให้คะแนนแสดง "-"
            $ass    = $rec->assessment;
            $scores = [];
            foreach (array_keys($scoreColumns) as $key) {
                $val = $ass?->{$key};
                $scores[$key] = ($val !== null && $val !== '') ? (int) $val : '-';
            }

            $pdi = $s->preDeliveryInspection;
            if (!$pdi) {
                $pdiStatus = 'ไม่ตรวจรถก่อนส่งมอบ';
            } elseif ($pdi->accessories_complete && $pdi->exterior_clean && $pdi->interior_clean && $pdi->issues_resolved) {
                $pdiStatus = 'เรียบร้อย';
            } else {
                $pdiStatus = 'ไม่เรียบร้อย';
            }

            return [
                'no'                => $no++,
                'timestamp'         => $rec?->created_at?->format('d/m/Y H:i:s') ?? '-',
                'delivery_date'     => $s->DeliveryDate ? Carbon::parse($s->DeliveryDate)->format('d/m/Y') : '-',
                'full_name'         => $fullName,
                'sale_name'         => $s->saleUser?->name ?? '-',
                'model'             => $modelFull,
                'delivery_location' => $s->delivery_location ?? '-',
                'delivery_province' => $provinces->get($s->delivery_province)?->name ?? '-',
                'phone'             => $c?->formatted_mobile ?? '-',
                'vin'               => $s->carOrder?->vin_number ?? '-',
                'latest_contact_date' => $latestContactDate,
                'contact_history'   => $contactHistory,
                'contact_status'    => $contactStatus,
                'pdi_status'        => $pdiStatus,
                'scores'            => $scores,
                'ssi_score'         => $ssiScore,
            ];
        })->filter()->values();

        $dateLabel = Carbon::parse($this->dateFrom)->locale('th')->isoFormat('D MMMM YYYY')
            . ' - '
            . Carbon::parse($this->dateTo)->locale('th')->isoFormat('D MMMM YYYY');

        return view('customer-relation.ssi.excel', [
            'rows'         => $rows,
            'date'         => $dateLabel,
            'scoreColumns' => $scoreColumns,
        ]);
    }

    /** หัวข้อคะแนน SSI รายข้อ (1-5) แยกตาม brand */
    private function scoreColumns(int $brand): array
    {
        if ($brand == 2) {
            return [
                'gwm_q1' => 'Q1 การต้อนรับและการดูแลของเจ้าหน้าที่',
                'gwm_q2' => 'Q2 ความสามารถของที่ปรึกษาการขาย (iAM)',
                'gwm_q3' => 'Q3 ประสบการณ์การทดลองขับ',
                'gwm_q4' => 'Q4 ความสะอาด/เรียบร้อยของรถใหม่',
                'gwm_q5' => 'Q5 การอธิบายคุณสมบัติ/การใช้งานรถ',
                'gwm_q6' => 'Q6 บรรยากาศ/สิ่งอำนวยความสะดวกในโชว์รูม',
                'gwm_q7' => 'Q7 ความพึงพอใจโดยรวมต่อการซื้อรถ',
                'gwm_q8' => 'Q8 แนวโน้มแนะนำ iAM/ศูนย์บริการ',
            ];
        }

        return [
            'dw_website'                 => 'DW เว็บไซต์',
            'q11_facilities'             => 'Q11 สิ่งอำนวยความสะดวก',
            'q15_car_knowledge'          => 'Q15 ความรอบรู้เกี่ยวกับรถยนต์ของที่ปรึกษาการขาย',
            'q17_service_responsibility' => 'Q17 ความรับผิดชอบในการให้บริการ',
            'q18_sales_conditions'       => 'Q18 การชี้แจงรายละเอียดเงื่อนไขการขาย',
            'o27_car_condition'          => 'O27 รถที่ส่งมอบอยู่ในสภาพเรียบร้อยสมบูรณ์',
            'fu_followup'                => 'FU การติดตามหลังจากส่งมอบ',
            'recommend_showroom'         => 'แนวโน้มที่จะแนะนำโชว์รูม',
        ];
    }
}
