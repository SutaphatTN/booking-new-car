<?php

namespace App\Exports\saleCar\estimated;

use App\Models\TbBranch;
use App\Models\TbConStatus;
use App\Services\SaleBookingQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SaleCarEstimatedSummaryExport implements FromView, WithTitle, WithEvents, ShouldAutoSize
{
    protected $fromDate;
    protected $branchId;
    protected $rowMeta = [];

    protected $saleColors = [
        'F4B183',
        '9DC3E6',
        'A9D18E',
        'FFD966',
        'C9B1D0',
        'FF9999',
        'B4E5A2',
        'FFCC99',
    ];

    public function __construct($fromDate, $branchId = null)
    {
        $this->fromDate = $fromDate;
        $this->branchId = $branchId;
    }

    public function title(): string
    {
        $branch = TbBranch::find($this->branchId);
        return 'สรุปรวม' . ($branch ? ' - ' . $branch->name : '');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet      = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();

                // font + vertical align for entire sheet
                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getFont()
                    ->setName('Angsana New')
                    ->setSize(14);

                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // borders
                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->setColor(new Color(Color::COLOR_BLACK));

                // row heights
                $sheet->getRowDimension(1)->setRowHeight(30);
                for ($r = 2; $r <= $highestRow; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(20);
                }

                // freeze under header rows
                $sheet->freezePane('A3');

                // tab color
                $sheet->getTabColor()->setRGB('FFC000');

                // per-row styling
                foreach ($this->rowMeta as $rowNum => $meta) {
                    $type     = is_array($meta) ? $meta['type'] : $meta;
                    $range    = "A{$rowNum}:{$highestCol}{$rowNum}";

                    if ($type === 'title') {
                        $sheet->getStyle($range)->getFont()->setBold(true)->setSize(16);
                        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle($range)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('D9D9D9');

                    } elseif ($type === 'header') {
                        $sheet->getStyle($range)->getFont()->setBold(true);
                        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle($range)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('BDD7EE');

                    } elseif ($type === 'sale') {
                        $color = $this->saleColors[$meta['colorIdx'] % count($this->saleColors)];
                        $sheet->getStyle($range)->getFont()->setBold(true);
                        $sheet->getStyle($range)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($color);
                        $sheet->getStyle("B{$rowNum}:{$highestCol}{$rowNum}")
                            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    } elseif ($type === 'model') {
                        $sheet->getStyle("B{$rowNum}:{$highestCol}{$rowNum}")
                            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    } elseif ($type === 'total') {
                        $sheet->getStyle($range)->getFont()->setBold(true);
                        $sheet->getStyle($range)->getFont()->getColor()->setRGB('FFFFFF');
                        $sheet->getStyle($range)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('404040');
                        $sheet->getStyle("B{$rowNum}:{$highestCol}{$rowNum}")
                            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                }
            },
        ];
    }

    public function view(): View
    {
        $date  = Carbon::createFromFormat('Y-m', $this->fromDate)->startOfMonth();
        $month = $date->month;
        $year  = $date->year;

        $query = SaleBookingQuery::base()
            ->with(['saleUser', 'model', 'conStatus'])
            ->whereMonth('DeliveryEstimateDate', $month)
            ->whereYear('DeliveryEstimateDate', $year)
            ->whereNotIn('con_status', [7, 8, 9]);

        if ($this->branchId) {
            $query->where('branch', $this->branchId);
        }

        $rows     = $query->get();
        $statuses = TbConStatus::whereNotIn('id', [7, 8, 9])->orderBy('id')->get();

        // [sale_name][model_name][status_id] = count
        $summary = [];
        foreach ($rows as $r) {
            $saleName  = $r->saleUser?->name ?? '-';
            $modelName = $r->model?->Name_TH ?? '-';
            $statusId  = $r->con_status;

            $summary[$saleName][$modelName][$statusId] =
                ($summary[$saleName][$modelName][$statusId] ?? 0) + 1;
        }

        // [sale_name][status_id] = total count
        $saleTotals = [];
        foreach ($summary as $saleName => $models) {
            foreach ($models as $statusCounts) {
                foreach ($statusCounts as $statusId => $count) {
                    $saleTotals[$saleName][$statusId] =
                        ($saleTotals[$saleName][$statusId] ?? 0) + $count;
                }
            }
        }

        // [status_id] = grand total count
        $grandTotal = [];
        foreach ($saleTotals as $statusCounts) {
            foreach ($statusCounts as $statusId => $count) {
                $grandTotal[$statusId] = ($grandTotal[$statusId] ?? 0) + $count;
            }
        }

        // build rowMeta for AfterSheet styling
        $rowNum = 1;
        $this->rowMeta[$rowNum++] = 'title';
        $this->rowMeta[$rowNum++] = 'header';

        $colorIdx = 0;
        foreach ($summary as $saleName => $models) {
            $this->rowMeta[$rowNum++] = ['type' => 'sale', 'colorIdx' => $colorIdx++];
            foreach ($models as $modelName => $statusCounts) {
                $this->rowMeta[$rowNum++] = 'model';
            }
        }

        if (!empty($summary)) {
            $this->rowMeta[$rowNum] = 'total';
        }

        $colCount = $statuses->count() + 2;

        return view('purchase-order.report.saleCar.estimated.sale-summary', [
            'summary'    => $summary,
            'saleTotals' => $saleTotals,
            'grandTotal' => $grandTotal,
            'statuses'   => $statuses,
            'colCount'   => $colCount,
        ]);
    }
}
