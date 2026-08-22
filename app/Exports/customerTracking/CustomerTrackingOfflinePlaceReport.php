<?php

namespace App\Exports\customerTracking;

use App\Models\CustomerTracking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * รายงานลูกค้าจากงาน Offline แยกตามสถานที่ (รายเดือน)
 *  - เดือนกรองจาก created_at ของรายการติดตาม (วันที่เพิ่มลูกค้า) — ตรงกับรายงานอื่นในหน้านี้
 *  - เอาเฉพาะแหล่งที่มาหลัก = offline (Booth / ใบปลิว / Dep-Store / KOL / Showroom Event) และต้องมีสถานที่ (place_id)
 *  - sheet แรก = สรุปจำนวนต่อสถานที่ จากนั้น 1 สถานที่ = 1 sheet
 *    สถานที่ที่ไม่มีลูกค้าในเดือนนั้นจะไม่มี sheet (มติผู้ใช้ — ไม่ต้องการ sheet เปล่า)
 */
class CustomerTrackingOfflinePlaceReport implements WithMultipleSheets
{
    public function __construct(protected string $month) {}

    public function sheets(): array
    {
        $user  = Auth::user();
        $start = Carbon::parse($this->month . '-01')->startOfMonth();
        $end   = (clone $start)->endOfMonth();

        // brand มาจาก global scope (UserAccessScope) อยู่แล้ว — ใส่ซ้ำกัน role ที่ scope ไม่ครอบหลุดข้ามแบรนด์
        $trackings = CustomerTracking::with([
            'customer.prefix',
            'sale',
            'source',
            'place.source',
            'model',
            'subModel',
            'wuColor',
            'interiorColor',
            'details.decision',
        ])
            ->where('brand', $user->brand)
            ->whereNotNull('place_id')
            ->whereBetween('created_at', [$start, $end])
            ->whereHas('source', fn($q) => $q->where('main_source', 'offline'))
            ->orderBy('created_at')
            ->get();

        // เรียงสถานที่ตามวันเริ่มงาน → sheet ไล่ตามไทม์ไลน์ของเดือน (ไม่มีวันเริ่ม = ไปท้ายสุด)
        $groups = $trackings->groupBy('place_id')
            ->sortBy(fn($rows) => $rows->first()->place?->start_date?->format('Y-m-d') ?? '9999-12-31');

        // ตั้งชื่อ sheet ล่วงหน้าทีเดียว เพื่อให้ sheet สรุปอ้างชื่อเดียวกับ tab จริงได้
        $used   = [];
        $titles = [];
        foreach ($groups as $placeId => $rows) {
            $titles[$placeId] = self::sheetTitle(
                $rows->first()->place?->location ?: ('สถานที่ ' . $placeId),
                $used
            );
        }

        $sheets = [new CustomerTrackingOfflinePlaceSummarySheet($this->month, $groups, $titles)];

        foreach ($groups as $placeId => $rows) {
            $sheets[] = new CustomerTrackingOfflinePlaceSheet($this->month, $rows, $titles[$placeId]);
        }

        return $sheets;
    }

    /**
     * ชื่อ sheet ที่ Excel รับได้ : ตัดอักขระต้องห้าม \ / ? * [ ] : ออก, ยาวไม่เกิน 31 ตัว, ห้ามซ้ำกัน
     * ชื่อสถานที่แบบเต็มไปอยู่หัวตารางของ sheet นั้นแล้ว ตรงนี้เอาแค่พอให้กด tab ถูก
     */
    public static function sheetTitle(string $name, array &$used): string
    {
        $clean = preg_replace('/[\\\\\/\?\*\[\]:]+/u', ' ', $name);
        $clean = trim(preg_replace('/\s+/u', ' ', $clean));
        if ($clean === '') {
            $clean = 'สถานที่';
        }

        // rtrim: ตัด 31 ตัวแล้วอาจเหลือช่องว่างท้าย ทำให้ชื่อ tab ดูแปลก
        $title = rtrim(mb_substr($clean, 0, 31));

        // ชื่อซ้ำ (สถานที่คนละรายการแต่ตั้งชื่อขึ้นต้นเหมือนกัน เช่น "Krabi Test Drive/Outdoor event …") → ต่อท้ายด้วยลำดับ
        $n = 2;
        while (in_array($title, $used, true)) {
            $suffix = ' (' . $n++ . ')';
            $title  = rtrim(mb_substr($clean, 0, 31 - mb_strlen($suffix))) . $suffix;
        }

        $used[] = $title;

        return $title;
    }
}
