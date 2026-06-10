<?php

namespace App\Exports\preDeliveryInspection;

use App\Models\Salecar;
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
    public function __construct(private string $dateFrom, private string $dateTo) {}

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

                // แถวสรุป 4 แถวท้าย — highlight สีเขียวอ่อน
                for ($r = $highestRow - 3; $r <= $highestRow; $r++) {
                    $sheet->getStyle("A{$r}:{$highestCol}{$r}")
                        ->getFill()->setFillType('solid')
                        ->getStartColor()->setRGB('E8F5E9');
                }

                $sheet->setAutoFilter("A1:{$highestCol}1");
                $sheet->freezePane('A2');
                $sheet->getTabColor()->setRGB('C8E6C9');
            },
        ];
    }

    public function view(): View
    {
        $salecars = Salecar::with([
            'customer.prefix',
            'saleUser',
            'model',
            'subModel',
            'preDeliveryInspection',
        ])
            ->whereNotNull('AdminSignature')
            ->whereDate('DeliveryDate', '>=', $this->dateFrom)
            ->whereDate('DeliveryDate', '<=', $this->dateTo)
            ->orderBy('DeliveryDate')
            ->get();

        $no   = 1;
        $rows = $salecars->map(function ($s) use (&$no) {
            $c        = $s->customer;
            $fullName = $c
                ? trim(($c->prefix->Name_TH ?? '') . ' ' . $c->FirstName . ' ' . $c->LastName)
                : '-';

            $model     = $s->model?->Name_TH ?? '-';
            $subModel  = $s->subModel?->name ?? '';
            $modelFull = $subModel ? "{$model} / {$subModel}" : $model;

            $ins = $s->preDeliveryInspection;

            if (!$ins) {
                $status         = 'ไม่ได้ตรวจ';
                $inspectionDate = '-';
                $ok             = null;
            } else {
                $allOk = $ins->accessories_complete
                      && $ins->exterior_clean
                      && $ins->interior_clean
                      && $ins->issues_resolved;
                $status         = $allOk ? 'เรียบร้อย' : 'ไม่เรียบร้อย';
                $inspectionDate = $ins->created_at?->format('d/m/Y') ?? '-';
                $ok             = $allOk;
            }

            return [
                'no'              => $no++,
                'sale_name'       => $s->saleUser?->name ?? '-',
                'full_name'       => $fullName,
                'model'           => $modelFull,
                'delivery_date'   => $s->DeliveryDate
                    ? Carbon::parse($s->DeliveryDate)->format('d/m/Y')
                    : '-',
                'inspection_date' => $inspectionDate,
                'status'          => $status,
                'ok'              => $ok,
            ];
        });

        $total          = $rows->count();
        $totalInspected = $rows->whereNotNull('ok')->count();
        $totalOk        = $rows->where('ok', true)->count();
        $totalNot       = $rows->where('ok', false)->count();

        $dateLabel = Carbon::parse($this->dateFrom)->locale('th')->isoFormat('D MMMM YYYY')
            . ' - '
            . Carbon::parse($this->dateTo)->locale('th')->isoFormat('D MMMM YYYY');

        return view('customer-relation.pre-delivery-inspection.excel', [
            'rows'            => $rows->values(),
            'date'            => $dateLabel,
            'total'           => $total,
            'total_inspected' => $totalInspected,
            'total_ok'        => $totalOk,
            'total_not'       => $totalNot,
        ]);
    }
}
