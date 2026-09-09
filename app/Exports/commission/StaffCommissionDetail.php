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
 * ชีท "รายละเอียดตามเกณฑ์" — กางทีละก้อน (ฐานที่นับ) และทีละรายการที่กรอกเอง
 * ให้เห็นว่ายอดในชีทสรุปมาจากขั้นไหน จำนวนคันเท่าไร โดนตัดเพดานหรือไม่
 */
class StaffCommissionDetail implements FromView, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    use StaffSheetStyle;

    public function __construct(protected array $rows) {}

    public function title(): string
    {
        return 'รายละเอียดตามเกณฑ์';
    }

    protected function moneyCols(): array
    {
        return ['G'];
    }

    public function view(): View
    {
        $headers = ['ชื่อ', 'ฝ่าย', 'ประเภท', 'รายการ', 'จำนวนคัน', 'เกณฑ์ที่เข้า', 'ยอด'];

        $rows = [];
        $sum = 0.0;

        foreach ($this->rows as $r) {
            $d = $r['data'];

            foreach ($d['buckets'] as $b) {
                $rows[] = [
                    $r['name'],
                    $d['label'],
                    'คอมตามยอดขาย',
                    $b['name'],
                    (int) $b['count'],
                    $b['note'] . (!empty($b['capped']) ? ' (ตัดเพดาน)' : ''),
                    (float) $b['amount'],
                ];
                $sum += (float) $b['amount'];
            }

            foreach ($d['extras'] as $e) {
                $note = ($e['type'] ?? 'money') === 'bool'
                    ? ($e['value'] ? 'ติ๊กว่าได้รับ' : 'ไม่ได้ติ๊ก')
                    : 'กรอกเอง';

                $rows[] = [
                    $r['name'],
                    $d['label'],
                    'รายการเพิ่มเติม',
                    $e['label'],
                    '',
                    $note,
                    (float) $e['amount'],
                ];
                $sum += (float) $e['amount'];
            }
        }

        $totalRow = $rows ? ['Total', '', '', '', '', '', $sum] : null;

        return view('purchase-order.report.commission.sale-report-generic', compact('headers', 'rows', 'totalRow'));
    }
}
