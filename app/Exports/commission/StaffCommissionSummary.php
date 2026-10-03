<?php

namespace App\Exports\commission;

use App\Exports\commission\Concerns\StaffSheetStyle;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * ชีท "สรุปรายคน" — คนละแถว ยอดตรงกับตารางในหน้าค่าคอมมิชชั่นฝ่ายสนับสนุน
 */
class StaffCommissionSummary implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    use StaffSheetStyle;

    /** @param array $rows ผลจาก StaffCommissionQuery::forMonth() ที่ผูกชื่อคนไว้แล้ว */
    public function __construct(protected array $rows) {}

    public function title(): string
    {
        return 'สรุปรายคน';
    }

    protected function moneyCols(): array
    {
        return ['D', 'E', 'F'];
    }

    public function view(): View
    {
        // "รายการเพิ่มเติม / หัก" = ยอดสุทธิของช่องกรอกเอง (ช่องหักติดลบอยู่แล้ว)
        $headers = ['ชื่อ', 'ฝ่าย', 'ฐานที่นับ', 'คอมตามยอดขาย', 'รายการเพิ่มเติม / หัก', 'รวมสุทธิ', 'หมายเหตุ'];

        $rows = [];
        $sumCar = $sumExtra = $sumTotal = 0.0;

        foreach ($this->rows as $r) {
            $d = $r['data'];

            $base = collect($d['buckets'])
                ->map(fn($b) => $b['name'] . ' ' . number_format($b['count']) . ' คัน')
                ->implode(' / ') ?: '-';

            // หมายเหตุ = หมายเหตุของรายการที่มียอด (เช่น ค่าคอมอื่นๆ) + หมายเหตุทั่วไป
            $notes = collect($d['extras'] ?? [])
                ->filter(fn($e) => !empty($e['note_required']) && (float) $e['amount'] != 0 && ($e['note_value'] ?? '') !== '')
                ->map(fn($e) => $e['label'] . ' ' . number_format((float) $e['amount'], 2) . ': ' . $e['note_value'])
                ->push($d['note'] ?? '')
                ->filter()
                ->implode(' / ');

            $rows[] = [
                $r['name'],
                $d['label'],
                $base,
                (float) $d['car_total'],
                (float) $d['extra_total'],
                (float) $d['total'],
                $notes,
            ];

            $sumCar   += (float) $d['car_total'];
            $sumExtra += (float) $d['extra_total'];
            $sumTotal += (float) $d['total'];
        }

        $totalRow = $rows ? ['Total', '', '', $sumCar, $sumExtra, $sumTotal, ''] : null;

        return view('purchase-order.report.commission.sale-report-generic', compact('headers', 'rows', 'totalRow'));
    }
}
