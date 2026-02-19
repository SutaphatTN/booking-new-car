<?php

namespace App\Exports\fn;

use App\Models\Salecar;
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

class FirmExport implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    protected $month;
    protected $year;

    public function __construct($month = null, $year = null)
    {
        $this->month = $month ?? now()->month;
        $this->year  = $year  ?? now()->year;
    }

    public function title(): string
    {
        return 'สรุป Firm FN';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            //แถวบนสุด
            1 => [
                'font' => [],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '7ed7f7'],
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
                $sheet->getTabColor()->setRGB('7ed7f7');

                // format comma
                $numberColumns = [
                    'H',
                    'N',
                    'O',
                    'P',
                    'Q',
                    'R',
                    'S',
                    'T',
                    'U',
                    'V'
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
        $rows = Salecar::with([
            'customer.prefix',
            'carOrder.model',
            'carOrder.subModel',
            'carOrder',
            'financeConfirm',
            'remainingPayment',
            'remainingPayment.financeInfo',
        ])
            ->where('payment_mode', 'finance')
            ->where('con_status', '5')
            ->whereMonth('DeliveryDate', $this->month)
            ->whereYear('DeliveryDate', $this->year)
            ->get();

        $data = $rows->map(function ($r) {
            $customerName = trim(
                ($r->customer->prefix->Name_TH ?? '') . ' ' .
                    ($r->customer->FirstName ?? '') . ' ' .
                    ($r->customer->LastName ?? '')
            );

            $model = $r->carOrder->model->Name_TH ?? '-';
            $sub = $r->carOrder->subModel->name ?? '-';
            $detailModel = $r->carOrder->subModel->detail ?? '-';

            $subModel = "{$detailModel} - {$sub}";
            $typeCom = "c{$r->remainingPayment->type_com}";

            return [
                'customer' => $customerName,
                'model' => $model,
                'subModel' => $subModel,
                'option'     => $r->option ?? '-',
                'color'      => $r->color ?? '-',
                'year'       => $r->year ?? '-',
                'alp'       => $r->remainingPayment->total_alp ?? '-',
                'name_fi'       => $r->remainingPayment->financeInfo->FinanceCompany ?? '-',
                'interest'       => $r->remainingPayment->interest . '%' ?? '-',
                'total_alp'       => $typeCom ?? '-',
                'period'       => $r->remainingPayment->period ?? '-',
                'tax'       => $r->remainingPayment->financeInfo->tax . '%' ?? '-',
                'price_sub'       => $r->price_sub ?? '-',
                'com_fin'       => $r->financeConfirm->com_fin ?? '-',
                'com_extra'       => $r->financeConfirm->com_extra ?? '-',
                'kickback'       => $r->kickback ?? '-',
                'com_subsidy'       => $r->financeConfirm->com_subsidy ?? '-',
                'advance_installment'       => $r->financeConfirm->advance_installment ?? '-',
                'total_fi'       => $r->financeConfirm->total ?? '-',
                'actually_received'       => $r->financeConfirm->actually_received ?? '-',
                'diff'       => $r->financeConfirm->diff ?? '-',
                'date'       => $r->financeConfirm->format_date ?? '-',
                'firm_date'       => $r->financeConfirm->format_firm_date ?? '-'
            ];
        });

        return view('purchase-order.report.fn.summary', [
            'firmFN' => $data
        ]);
    }
}
