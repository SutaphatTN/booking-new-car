<?php

namespace App\Exports\commission\Concerns;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * สร้างข้อมูลรายงานค่าคอมแบบ data-driven ตาม "นิยามคอลัมน์" (columns())
 *  - แต่ละคอลัมน์: ['label','key','role','money'(bool),'num'(bool)]
 *      role: info | recv (รวมเข้ายอดรับ) | ded (รวมเข้ายอดหัก) | sum_recv | sum_ded | net
 *  - คิดยอด "รวมค่าคอมรับ / รวมยอดหัก / คอมสุทธิ" + แถว Total ให้อัตโนมัติใน PHP
 *  - คอลัมน์เงิน (money) โชว์ว่างเมื่อเป็น 0 (เฉพาะ recv/ded), ส่วน sum/net โชว์เสมอ
 */
trait BuildsCommissionReport
{
    /** ตัวอักษรคอลัมน์ (A, B, ...) ของคอลัมน์ที่เป็นเงิน — ใช้ set number format */
    protected function moneyColumnLetters(array $columns): array
    {
        $letters = [];
        foreach (array_values($columns) as $i => $c) {
            if (!empty($c['money'])) {
                $letters[] = Coordinate::stringFromColumnIndex($i + 1);
            }
        }
        return $letters;
    }

    /**
     * @param  array     $columns   นิยามคอลัมน์
     * @param  iterable  $dataRows  แต่ละแถวเป็น assoc (key ตรงกับ columns[].key)
     * @return array{headers:array, rows:array, totalRow:array|null}
     */
    protected function buildReport(array $columns, iterable $dataRows): array
    {
        $headers = array_map(fn($c) => $c['label'], $columns);
        $rows = [];
        $grand = array_fill(0, count($columns), null);

        foreach ($dataRows as $d) {
            // ยอดรับ / ยอดหัก / สุทธิ
            $recv = 0.0;
            $ded = 0.0;
            foreach ($columns as $c) {
                $role = $c['role'] ?? 'info';
                if ($role === 'recv') {
                    $recv += (float) ($d[$c['key']] ?? 0);
                } elseif ($role === 'ded') {
                    $ded += (float) ($d[$c['key']] ?? 0);
                }
            }
            $d['__recv'] = $recv;
            $d['__ded'] = $ded;
            $d['__net'] = $recv - $ded;

            $cells = [];
            foreach (array_values($columns) as $i => $c) {
                $role = $c['role'] ?? 'info';
                $val = $d[$c['key']] ?? null;

                if ($role === 'recv' || $role === 'ded') {
                    $num = (float) ($val ?? 0);
                    $cells[] = $num == 0.0 ? '' : $num;      // เงินเสริม 0 → เว้นว่าง
                    $grand[$i] = (float) ($grand[$i] ?? 0) + $num;
                } elseif ($role === 'sum_recv' || $role === 'sum_ded' || $role === 'net') {
                    $num = (float) ($val ?? 0);
                    $cells[] = $num;                          // ยอดรวม/สุทธิ โชว์เสมอ
                    $grand[$i] = (float) ($grand[$i] ?? 0) + $num;
                } elseif (!empty($c['num'])) {
                    $num = (float) ($val ?? 0);
                    $cells[] = $val === null ? '' : $val;     // จำนวนคัน ฯลฯ
                    $grand[$i] = (float) ($grand[$i] ?? 0) + $num;
                } else {
                    $cells[] = $val === null ? '' : $val;     // ข้อความ
                }
            }
            $rows[] = $cells;
        }

        $totalRow = null;
        if (!empty($rows)) {
            $totalRow = [];
            foreach (array_values($columns) as $i => $c) {
                if ($i === 0) {
                    $totalRow[] = 'Total';
                } else {
                    $totalRow[] = $grand[$i] === null ? '' : (float) $grand[$i];
                }
            }
        }

        return ['headers' => $headers, 'rows' => $rows, 'totalRow' => $totalRow];
    }
}
