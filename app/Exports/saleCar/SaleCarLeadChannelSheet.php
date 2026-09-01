<?php

namespace App\Exports\saleCar;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

/**
 * sheet "สรุป Lead Channel" — ตารางไขว้ 2 ตาราง
 *   A) แหล่งที่มาย่อย × แหล่งที่มาหลัก + แถว %Lead Mix
 *   B) ฝ่ายขาย × แหล่งที่มาหลัก
 *
 * คอลัมน์ = แหล่งที่มาหลักที่ "มีข้อมูลจริง" เท่านั้น เรียงตามลำดับใน config/source.php
 * (ถ้าโชว์ทุกกลุ่มเสมอจะมีคอลัมน์ว่างรกตา) ใบจองที่ไม่ได้ระบุแหล่งที่มาไปรวมที่คอลัมน์ "ไม่ระบุ" ท้ายสุด
 */
class SaleCarLeadChannelSheet implements FromArray, WithTitle, WithEvents, ShouldAutoSize
{
    /** key แทน "ไม่ได้ระบุแหล่งที่มา" — ตั้งให้ไม่ชน key ใดใน config/source.php */
    private const UNSET_KEY = '__none__';

    protected Collection $rows;
    protected ?string $fromDate;
    protected ?string $toDate;

    /** ผลลัพธ์ที่ build แล้ว — array() ถูกเรียกซ้ำได้ ต้องจำไว้ */
    protected ?array $built = null;

    /** key ของแหล่งที่มาหลักที่กลายเป็นคอลัมน์ (เรียงแล้ว) */
    protected array $mainKeys = [];

    /** เลขแถวที่ต้องจัดสไตล์ทีหลัง — เก็บตอน build เพราะความสูงตารางไม่คงที่ */
    protected array $marks = ['section' => [], 'header' => [], 'total' => [], 'percent' => []];

    public function __construct(Collection $rows, ?string $fromDate = null, ?string $toDate = null)
    {
        $this->rows     = $rows;
        $this->fromDate = $fromDate;
        $this->toDate   = $toDate;
    }

    public function title(): string
    {
        return 'สรุป Lead Channel';
    }

    public function array(): array
    {
        if ($this->built !== null) {
            return $this->built;
        }

        $this->mainKeys = $this->columnKeys();
        $labels = array_map(fn($k) => $this->mainLabel($k), $this->mainKeys);

        $out = [];
        $out[] = ['สรุป Lead Channel'];
        $out[] = [$this->periodText()];
        $out[] = [''];   // แถวคั่น — ต้องไม่ใช่ array ว่าง เพราะ Sheet::appendRows ใช้ flatMap ซึ่งกลืนแถวว่างหายไปทั้งแถว

        // ── A) แยกตามแหล่งที่มาย่อย ──
        $this->marks['section'][] = count($out) + 1;
        $out[] = ['A) สรุป ChannelGroup ย่อย'];

        $this->marks['header'][] = count($out) + 1;
        $out[] = array_merge(['แหล่งที่มาย่อย'], $labels, ['รวมทั้งหมด']);

        $bySub = $this->crosstab(fn($r) => $r['source_sub'] ?: '-');
        foreach ($bySub as $name => $counts) {
            $out[] = $this->dataRow((string) $name, $counts);
        }

        $subTotals = $this->totalsOf($bySub);
        $this->marks['total'][] = count($out) + 1;
        $out[] = $this->dataRow('รวมทั้งหมด', $subTotals);

        $grand = array_sum($subTotals);
        $this->marks['percent'][] = count($out) + 1;
        $out[] = array_merge(
            ['%Lead Mix'],
            array_map(fn($k) => $grand ? ($subTotals[$k] ?? 0) / $grand : 0, $this->mainKeys),
            [$grand ? 1 : 0]
        );

        $out[] = [''];   // แถวคั่น — ต้องไม่ใช่ array ว่าง เพราะ Sheet::appendRows ใช้ flatMap ซึ่งกลืนแถวว่างหายไปทั้งแถว

        // ── B) แยกตามฝ่ายขาย ──
        $this->marks['section'][] = count($out) + 1;
        $out[] = ['B) สรุป ChannelGroup [แยกตามฝ่ายขาย]'];

        $this->marks['header'][] = count($out) + 1;
        $out[] = array_merge(['ฝ่ายขาย'], $labels, ['รวมทั้งหมด']);

        $bySale = $this->crosstab(fn($r) => $r['sale'] ?: '-');
        foreach ($bySale as $name => $counts) {
            $out[] = $this->dataRow((string) $name, $counts);
        }

        $this->marks['total'][] = count($out) + 1;
        $out[] = $this->dataRow('รวมทั้งหมด', $this->totalsOf($bySale));

        return $this->built = $out;
    }

    /** ข้อความช่วงวันที่บนหัวรายงาน — ไม่ได้เลือกช่วง = ทั้งหมด */
    private function periodText(): string
    {
        if ($this->fromDate && $this->toDate) {
            return 'วันที่จอง ' . $this->fromDate . ' ถึง ' . $this->toDate;
        }

        return 'วันที่จอง : ทั้งหมด';
    }

    /** แหล่งที่มาหลักที่มีข้อมูลจริง เรียงตาม config แล้วต่อท้ายด้วย "ไม่ระบุ" ถ้ามี */
    private function columnKeys(): array
    {
        $used = $this->rows->map(fn($r) => $r['source_main_key'] ?: self::UNSET_KEY)->unique()->all();

        $keys = array_values(array_filter(
            array_keys(config('source.main', [])),
            fn($k) => in_array($k, $used, true)
        ));

        if (in_array(self::UNSET_KEY, $used, true)) {
            $keys[] = self::UNSET_KEY;
        }

        return $keys;
    }

    private function mainLabel(string $key): string
    {
        return $key === self::UNSET_KEY
            ? 'ไม่ระบุ'
            : config("source.main.$key", $key);
    }

    /**
     * นับใบจองแยกตาม (กลุ่มแถวที่ $rowKey กำหนด) × แหล่งที่มาหลัก
     * คืนค่า [ชื่อแถว => [main key => จำนวน]] เรียงตามชื่อแถว
     */
    private function crosstab(callable $rowKey): array
    {
        $table = [];

        foreach ($this->rows as $r) {
            $name = $rowKey($r);
            $main = $r['source_main_key'] ?: self::UNSET_KEY;

            $table[$name][$main] = ($table[$name][$main] ?? 0) + 1;
        }

        ksort($table);

        return $table;
    }

    /** รวมยอดทุกแถวของตารางไขว้ ออกมาเป็น [main key => จำนวน] */
    private function totalsOf(array $table): array
    {
        $totals = [];

        foreach ($table as $counts) {
            foreach ($counts as $main => $n) {
                $totals[$main] = ($totals[$main] ?? 0) + $n;
            }
        }

        return $totals;
    }

    /** แถวข้อมูล 1 บรรทัด — ช่องที่ไม่มียอดเว้นว่างไว้ให้อ่านง่ายเหมือน pivot */
    private function dataRow(string $name, array $counts): array
    {
        $cells = array_map(fn($k) => $counts[$k] ?? '', $this->mainKeys);

        return array_merge([$name], $cells, [array_sum($counts)]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastCol = Coordinate::stringFromColumnIndex(count($this->mainKeys) + 2);
                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("A1:{$lastCol}{$lastRow}")
                    ->getFont()->setName('Angsana New')->setSize(14);

                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
                $sheet->getStyle('A2')->getFont()->setItalic(true);

                foreach ($this->marks['section'] as $row) {
                    $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                    $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(16);
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()
                        ->setFillType('solid')->getStartColor()->setRGB('ffdaa2');
                }

                foreach ($this->marks['header'] as $row) {
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()
                        ->setFillType('solid')->getStartColor()->setRGB('dce6f1');
                }

                foreach ($this->marks['total'] as $row) {
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFont()->setBold(true);
                }

                foreach ($this->marks['percent'] as $row) {
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()
                        ->setFillType('solid')->getStartColor()->setRGB('ffd966');
                    $sheet->getStyle("B{$row}:{$lastCol}{$row}")
                        ->getNumberFormat()->setFormatCode('0%');
                }

                // เส้นกรอบเฉพาะช่วงตาราง (หัวรายงาน 3 บรรทัดแรกกับแถวคั่นไม่ต้องมี)
                foreach ($this->tableRanges() as [$start, $end]) {
                    $sheet->getStyle("A{$start}:{$lastCol}{$end}")
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->setColor(new Color(Color::COLOR_BLACK));
                }

                $sheet->getStyle("B4:{$lastCol}{$lastRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getTabColor()->setRGB('ffd966');
            },
        ];
    }

    /** ช่วงแถวของแต่ละตาราง (หัวตาราง → แถวสรุปสุดท้าย) ใช้ตีกรอบ */
    private function tableRanges(): array
    {
        $ends = array_merge($this->marks['percent'], [max($this->marks['total'])]);
        sort($ends);

        $ranges = [];
        foreach ($this->marks['header'] as $i => $start) {
            $ranges[] = [$start, $ends[$i]];
        }

        return $ranges;
    }
}
