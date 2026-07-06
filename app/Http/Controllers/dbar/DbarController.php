<?php

namespace App\Http\Controllers\dbar;

use App\Http\Controllers\Controller;
use App\Models\CarOrder;
use App\Models\Salecar;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * D/Bar — คำนวณยอดรถที่ต้องสั่งเดือนถัดไป
 *
 * แยกตาม brand + branch อัตโนมัติผ่าน UserAccessScope ของ CarOrder/Salecar
 * (ไม่ต้องเลือกเอง — user เห็นเฉพาะ brand+branch ของตัวเอง)
 *
 * นิยามสถานะ:
 *   - car_order.car_status : Available / Booked / Delivered
 *   - salecar.con_status   : 3=ผ่านสัญญา, 4=ระหว่างแต่งรถ(ส่งแต่ง), 5=ส่งมอบ
 */
class DbarController extends Controller
{
    // เห็น/ใช้เมนูนี้ได้เฉพาะ role เหล่านี้
    private const ALLOWED_ROLES = ['admin', 'audit', 'audit_lead', 'gm', 'manager', 'md'];

    public function index()
    {
        abort_unless(in_array(Auth::user()->role, self::ALLOWED_ROLES), 403);

        return view('dbar.view');
    }

    public function calculate(Request $request)
    {
        abort_unless(in_array(Auth::user()->role, self::ALLOWED_ROLES), 403);

        $validated = $request->validate([
            'order_month'    => 'required|date_format:Y-m', // เดือนที่จะสั่งรถ
            'target_current' => 'required|numeric|min:0',   // D : เป้าการขายเดือนปัจจุบัน
        ]);

        $brand = Auth::user()->brand;

        // เดือนที่จะสั่ง (เช่น 2026-07)
        $orderMonth   = Carbon::createFromFormat('Y-m', $validated['order_month'])->startOfMonth();
        // เดือนปัจจุบัน = เดือนก่อนเดือนที่จะสั่ง (สั่งเดือน 7 → ปัจจุบัน = เดือน 6)
        $currentMonth = $orderMonth->copy()->subMonth();
        // ช่วง mix = 3 เดือนก่อนเดือนที่จะสั่ง (สั่งเดือน 7 → เดือน 4,5,6)
        $mixStart = $orderMonth->copy()->subMonths(3)->startOfMonth();
        $mixEnd   = $orderMonth->copy()->subMonth()->endOfMonth();

        /* ===== ส่วนที่ 1 : คำนวณยอดที่ต้องสั่ง (A–H) ===== */

        // A : Stock ทั้งหมด (ทุกสถานะ) = car_order ที่ยังไม่ส่งมอบ
        $stockAll = CarOrder::where('car_status', '!=', 'Delivered')->count();

        // B : Stock ไม่รวม ส่งแต่ง(4)/ผ่านสัญญา(3)
        $stockNet = CarOrder::where('car_status', '!=', 'Delivered')
            ->whereDoesntHave('salecars', fn($q) => $q->whereIn('con_status', [3, 4]))
            ->count();

        // C : ส่งมอบแล้ว (ยอด Company) ในเดือนปัจจุบัน
        $deliveredCurrent = Salecar::where('con_status', 5)
            ->whereYear('DeliveryInDMSDate', $currentMonth->year)
            ->whereMonth('DeliveryInDMSDate', $currentMonth->month)
            ->count();

        $targetCurrent = (int) round($validated['target_current']); // D

        $needMore   = $targetCurrent - $deliveredCurrent; // E = D - C
        $stockAfter = $stockNet - $needMore;              // F = B - E
        $targetNext = $stockAll - $stockAfter;            // G = A - F
        $toOrder    = $targetNext - $stockAfter;          // H = G - F

        /* ===== ส่วนที่ 2 : ยอดขาย 3 เดือน แยกรุ่น/สี → เกลี่ย H ตามสัดส่วน ===== */
        $mixRows = $this->salesMix($brand, $mixStart, $mixEnd);
        $mixRows = $this->allocate($mixRows, max($toOrder, 0)); // ติดลบ = ไม่ต้องสั่ง

        return response()->json([
            'success' => true,
            'brand'   => $brand,
            'summary' => [
                'stock_all'         => $stockAll,         // A
                'stock_net'         => $stockNet,         // B
                'delivered_current' => $deliveredCurrent, // C
                'target_current'    => $targetCurrent,    // D
                'need_more'         => $needMore,         // E
                'stock_after'       => $stockAfter,       // F
                'target_next'       => $targetNext,       // G
                'to_order'          => $toOrder,          // H
                'current_month'     => $currentMonth->format('m/Y'),
                'order_month'       => $orderMonth->format('m/Y'),
                'mix_range'         => $mixStart->format('m/Y') . ' – ' . $mixEnd->format('m/Y'),
            ],
            'mix'             => $mixRows,
            'mix_total_order' => array_sum(array_column($mixRows, 'should_order')),
        ]);
    }

    /**
     * ยอดขาย (ส่งมอบ con_status 5) ในช่วง mix แยกตาม รุ่น + รุ่นย่อย + สี
     * เรียงตาม รุ่นหลัก → รุ่นย่อย → สี
     */
    private function salesMix($brand, Carbon $from, Carbon $to): array
    {
        $query = Salecar::with(['model', 'subModel'])
            ->where('con_status', 5)
            ->whereDate('DeliveryInDMSDate', '>=', $from->toDateString())
            ->whereDate('DeliveryInDMSDate', '<=', $to->toDateString());

        if ($brand == 2) {
            $query->with(['gwmColor', 'interiorColor'])
                ->selectRaw('model_id, subModel_id, gwm_color, interior_color, COUNT(*) as sold')
                ->groupBy('model_id', 'subModel_id', 'gwm_color', 'interior_color');
        } elseif (in_array($brand, [3, 4])) {
            $query->with(['gwmColor'])
                ->selectRaw('model_id, subModel_id, gwm_color, COUNT(*) as sold')
                ->groupBy('model_id', 'subModel_id', 'gwm_color');
        } else {
            $query->selectRaw('model_id, subModel_id, Color, COUNT(*) as sold')
                ->groupBy('model_id', 'subModel_id', 'Color');
        }

        $rows = $query->get()->map(function ($r) use ($brand) {
            if ($brand == 2) {
                $color    = optional($r->gwmColor)->name ?? '-';
                $interior = optional($r->interiorColor)->name ?? '-';
            } elseif (in_array($brand, [3, 4])) {
                $color    = optional($r->gwmColor)->name ?? '-';
                $interior = null;
            } else {
                $color    = $r->Color ?? '-';
                $interior = null;
            }

            $subDetail = optional($r->subModel)->detail;
            $subName   = optional($r->subModel)->name ?? '-';

            return [
                'model'          => optional($r->model)->Name_TH ?? '-',
                'sub_model'      => $subDetail ? "{$subDetail} - {$subName}" : $subName,
                'color'          => $color,
                'interior_color' => $interior,
                'sold_3m'        => (int) $r->sold,
                'should_order'   => 0,
            ];
        })->all();

        // เรียง รุ่นหลัก → รุ่นย่อย → สี
        usort($rows, fn($a, $b) =>
            [$a['model'], $a['sub_model'], $a['color']] <=> [$b['model'], $b['sub_model'], $b['color']]);

        return $rows;
    }

    /**
     * เกลี่ยยอดที่ต้องสั่ง (H) ตามสัดส่วนยอดขาย ด้วยวิธี largest remainder
     * (ปัดให้ผลรวม should_order = H พอดี)
     */
    private function allocate(array $rows, int $total): array
    {
        $totalSold = array_sum(array_column($rows, 'sold_3m'));
        if ($total <= 0 || $totalSold <= 0) {
            return $rows; // should_order = 0 ทุกแถว
        }

        $remainders = [];
        $allocated  = 0;
        foreach ($rows as $i => &$r) {
            $raw   = $total * $r['sold_3m'] / $totalSold;
            $floor = (int) floor($raw);
            $r['should_order'] = $floor;
            $allocated += $floor;
            $remainders[$i] = $raw - $floor;
        }
        unset($r);

        // กระจายเศษที่เหลือให้แถวที่มีเศษมากสุดก่อน
        arsort($remainders);
        $left = $total - $allocated;
        foreach (array_keys($remainders) as $i) {
            if ($left <= 0) break;
            $rows[$i]['should_order']++;
            $left--;
        }

        return $rows;
    }
}
