<?php

namespace App\Exports\ssi;

use App\Models\SsiContact;
use App\Models\SsiRecord;
use App\Models\TbProvinces;
use Carbon\Carbon;
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
    public function __construct(private string $date) {}

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
        $ssiRecordIds = SsiContact::whereDate('contact_date', $this->date)
            ->pluck('ssi_record_id')
            ->unique();

        $records = SsiRecord::with([
            'salecar.customer.prefix',
            'salecar.saleUser',
            'salecar.model',
            'salecar.subModel',
            'salecar.carOrder',
            'contacts',
            'assessment',
        ])
            ->whereIn('id', $ssiRecordIds)
            ->orderBy('id')
            ->get();

        $provinces = TbProvinces::all()->keyBy('id');

        $no = 1;
        $rows = $records->map(function ($rec) use (&$no, $provinces) {
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

            $ssiScore = 0;
            $ass = $rec?->assessment;
            if ($ass) {
                if ($s->brand == 2) {
                    $fields = ['gwm_q1', 'gwm_q2', 'gwm_q3', 'gwm_q4', 'gwm_q5', 'gwm_q6', 'gwm_q7', 'gwm_q8'];
                } else {
                    $fields = [
                        'dw_website', 'q11_facilities', 'q15_car_knowledge',
                        'q17_service_responsibility', 'q18_sales_conditions', 'o27_car_condition',
                        'fu_followup', 'recommend_showroom',
                    ];
                }
                $answered = collect($fields)->filter(fn($f) => !is_null($ass->{$f}) && $ass->{$f} > 0);
                $sum = $answered->sum(fn($f) => (int) $ass->{$f});
                $ssiScore = $answered->isNotEmpty() ? round(($sum / 40) * 100, 2) : 0;
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
                'ssi_score'         => $ssiScore,
            ];
        })->filter()->values();

        return view('customer-relation.ssi.excel', [
            'rows' => $rows,
            'date' => Carbon::parse($this->date)->locale('th')->isoFormat('D MMMM YYYY'),
        ]);
    }
}
