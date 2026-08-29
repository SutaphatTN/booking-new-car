<?php

namespace App\Exports\carOrder;

use App\Exports\carOrder\Concerns\CarOrderSheetStyle;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * ชีทรายคันของรุ่นหลัก 1 รุ่น — ข้อมูลมาจาก CarOrderByOrderDateExport ที่โหลดมาให้แล้ว
 */
class OrderDateModelSheet implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    use CarOrderSheetStyle;

    protected $rows;
    protected $title;

    public function __construct(Collection $rows, string $title)
    {
        $this->rows  = $rows;
        $this->title = $title;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function view(): View
    {
        return view('car-order.report.order-date.model', ['rows' => $this->rows]);
    }
}
