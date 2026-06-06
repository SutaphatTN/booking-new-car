<?php

namespace App\Exports\preDeliveryInspection;

use App\Models\PreDeliveryInspection;
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

class PdiReportExport implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    public function __construct(private string $date) {}

    public function title(): string
    {
        return 'ตรวจรถก่อนส่งมอบ';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'C8E6C9']],
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
                    ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->setColor(new Color(Color::COLOR_BLACK));

                $sheet->getRowDimension(1)->setRowHeight(25);
                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }

                // แถวสรุปท้าย — highlight สีเขียวอ่อน
                $sheet->getStyle("A{$highestRow}:{$highestCol}{$highestRow}")
                    ->getFill()->setFillType('solid')
                    ->getStartColor()->setRGB('E8F5E9');

                $sheet->setAutoFilter("A1:{$highestCol}1");
                $sheet->freezePane('A2');
                $sheet->getTabColor()->setRGB('C8E6C9');
            },
        ];
    }

    public function view(): View
    {
        $inspections = PreDeliveryInspection::with([
            'salecar.customer.prefix',
            'salecar.saleUser',
            'salecar.model',
            'salecar.subModel',
        ])
            ->whereDate('created_at', $this->date)
            ->orderBy('created_at')
            ->get();

        $no   = 1;
        $rows = $inspections->map(function ($ins) use (&$no) {
            $s = $ins->salecar;
            if (!$s) return null;

            $c        = $s->customer;
            $fullName = $c
                ? trim(($c->prefix->Name_TH ?? '') . ' ' . $c->FirstName . ' ' . $c->LastName)
                : '-';

            $model    = $s->model?->Name_TH ?? '-';
            $subModel = $s->subModel?->name ?? '';
            $modelFull = $subModel ? "{$model} / {$subModel}" : $model;

            $allOk = $ins->accessories_complete
                  && $ins->exterior_clean
                  && $ins->interior_clean
                  && $ins->issues_resolved;

            return [
                'no'            => $no++,
                'sale_name'     => $s->saleUser?->name ?? '-',
                'full_name'     => $fullName,
                'model'         => $modelFull,
                'delivery_date' => $s->DeliveryDate
                    ? Carbon::parse($s->DeliveryDate)->format('d/m/Y')
                    : '-',
                'status'        => $allOk ? 'เรียบร้อย' : 'ไม่เรียบร้อย',
                'ok'            => $allOk,
            ];
        })->filter()->values();

        $total       = $rows->count();
        $totalOk     = $rows->where('ok', true)->count();
        $totalNotOk  = $rows->where('ok', false)->count();

        return view('customer-relation.pre-delivery-inspection.excel', [
            'rows'       => $rows,
            'date'       => Carbon::parse($this->date)->locale('th')->isoFormat('D MMMM YYYY'),
            'total'      => $total,
            'total_ok'   => $totalOk,
            'total_not'  => $totalNotOk,
        ]);
    }
}
