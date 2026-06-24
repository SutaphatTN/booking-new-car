<?php

namespace App\Imports;

use App\Models\CarOrder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * นำเข้าค่า WS เข้าตาราง car_order โดยจับคู่จากเลขตัวถัง (vin_number)
 * ไฟล์ต้นฉบับมีหัวคอลัมน์: vin_number, WS  (WithHeadingRow จะแปลง "WS" -> "ws")
 *
 * หมายเหตุ: ใช้ query ปกติของ CarOrder ซึ่งติด UserAccessScope อยู่
 *           ผู้ใช้จึงอัปเดตได้เฉพาะใบสั่งซื้อที่อยู่ในสิทธิ์ (brand/branch/zone) ของตนเอง
 *           VIN ที่อยู่นอกสิทธิ์จะถูกรายงานว่า "ไม่พบ"
 */
class CarOrderWsImport implements ToCollection, WithHeadingRow
{
    public int $updated = 0;
    public int $skipped = 0;
    public array $notFound = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $vin   = trim((string) ($row['vin_number'] ?? ''));
            $wsRaw = $row['ws'] ?? null;

            // ข้ามแถวว่าง (ไม่มี VIN)
            if ($vin === '') {
                continue;
            }

            // ไม่มีค่า WS -> ข้าม ไม่ไปทับค่าเดิมด้วยค่าว่าง
            if ($wsRaw === null || trim((string) $wsRaw) === '') {
                $this->skipped++;
                continue;
            }

            $ws = (float) str_replace(',', '', (string) $wsRaw);

            $order = CarOrder::where('vin_number', $vin)->first();

            if (!$order) {
                $this->notFound[] = $vin;
                continue;
            }

            $order->WS = $ws;
            $order->save();
            $this->updated++;
        }
    }
}
