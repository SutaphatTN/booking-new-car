<?php

namespace App\Exports\carOrder;

use App\Exports\carOrder\Concerns\CarOrderSheetStyle;
use App\Support\BrandFeature;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * ชีทสรุป — จำนวนคันที่สั่งในช่วงวันที่ที่เลือก แยกตามรุ่นหลัก/รุ่นย่อย
 */
class OrderDateSummarySheet implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    use CarOrderSheetStyle;

    protected $rows;
    protected $fromDate;
    protected $toDate;

    public function __construct(Collection $rows, $fromDate, $toDate)
    {
        $this->rows     = $rows;
        $this->fromDate = $fromDate;
        $this->toDate   = $toDate;
    }

    public function title(): string
    {
        return 'สรุปรวม';
    }

    protected function headerColor(): string
    {
        return 'ffd966';
    }

    /** ชีทนี้ไม่มีคอลัมน์เงิน */
    protected function moneyHeaders(): array
    {
        return [];
    }

    public function view(): View
    {
        // brand ที่มีหลายสาขา รายงานดึงมาทุกสาขา → สรุปแยกสาขาด้วย
        $showBranch = BrandFeature::hasMultipleBranches();

        // สรุปเป็น (สาขา →) รุ่นหลัก → รุ่นย่อย เรียงตามชื่อ
        $summary = $this->rows
            ->groupBy(fn($r) => ($showBranch ? ($r->branchInfo->name ?? 'ไม่ระบุสาขา') : '') . '|'
                . ($r->model->Name_TH ?? 'ไม่ระบุรุ่น') . '|'
                . ($r->subModel->name ?? 'ไม่ระบุรุ่นย่อย'))
            ->map(function ($group, $key) {
                [$branch, $model, $subModel] = explode('|', $key, 3);

                return [
                    'branch'   => $branch,
                    'model'    => $model,
                    'subModel' => $subModel,
                    'count'    => $group->count(),
                ];
            })
            // sortBy([]) ที่ส่ง closure เข้าไป Laravel จะมองเป็น "ตัวเปรียบเทียบ" ไม่ใช่ "ตัวดึงค่า"
            // แล้วกลายเป็นไม่เรียงอะไรเลย → ใช้ closure เดียวต่อคีย์รวมแทน
            ->sortBy(fn($a) => $a['branch'] . '|' . $a['model'] . '|' . $a['subModel'])
            ->values();

        return view('car-order.report.order-date.summary', [
            'summary'    => $summary,
            'showBranch' => $showBranch,
            'total'      => $this->rows->count(),
            'fromDate'   => $this->fromDate,
            'toDate'     => $this->toDate,
        ]);
    }
}
