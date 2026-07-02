<?php

namespace App\Http\Controllers\forecast;

use App\Http\Controllers\Controller;
use App\Models\CarOrder;
use App\Models\Salecar;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ForecastController extends Controller
{
    public function forecastForm()
    {
        return view('forecast.view');
    }

    /**
     * คำนวณจำนวนรถที่ "ควรสั่ง" เดือนนี้ ตามสัดส่วนการขายย้อนหลัง 3 เดือน (sales mix)
     *
     * สูตรหลัก ต่อรุ่น/สี:
     *   Mix %    = ยอดขายรุ่นนี้ ÷ ยอดขายรวมทุกรุ่น (ย้อนหลัง 3 เดือน)
     *   ควรสั่ง  = max( round(Mix% × target) − สต็อกที่มีอยู่, 0 )
     *
     * target = จำนวนรถที่ผู้ใช้อยากสั่งรวมทั้งเดือน (กรอกจากหน้า forecast)
     * การจัดกลุ่ม (model + subModel + สี) ต่างกันตาม brand:
     *   brand 2 (GWM)   : + สีภายใน (interior_color)
     *   brand 3 (Wuling): ใช้ gwm_color
     *   อื่นๆ (Mitsu)   : ใช้ Color (text)
     */
    public function forecastCalculate(Request $request)
    {
        $request->validate([
            'target' => 'required|numeric|min:1'
        ]);

        $target = $request->target;
        $brand = Auth::user()->brand;

        // ── เงื่อนไขช่วงเวลา: ย้อนหลัง 3 เดือน นับจากต้นเดือนของเดือนที่ 3 ──
        $startDate = Carbon::now()->subMonths(3)->startOfMonth();

        // ── ยอดขาย = รถที่ "ส่งมอบแล้ว" (มี DeliveryDate) ภายในช่วง 3 เดือน ──
        $query = Salecar::with(['model', 'subModel'])
            ->whereNotNull('DeliveryDate')
            ->where('DeliveryDate', '>=', $startDate);

        // ── นิยามสต็อก = รถที่พร้อมขาย (Available) และผ่านสถานะ finished/approved ──
        $stockQuery = CarOrder::where('car_status', 'Available')
            ->whereIn('status', ['finished', 'approved']);

        // ── จัดกลุ่ม + นับจำนวน แยกตาม brand (key ของยอดขายและสต็อกต้องตรงกันเพื่อ match ทีหลัง) ──
        if ($brand == 2) {

            // GWM: แยกตาม รุ่น + รุ่นย่อย + สีภายนอก + สีภายใน
            $query->with(['gwmColor', 'interiorColor'])
                ->selectRaw('model_id, subModel_id, gwm_color, interior_color, COUNT(*) as total')
                ->groupBy('model_id', 'subModel_id', 'gwm_color', 'interior_color');

            $stocks = $stockQuery
                ->selectRaw('model_id, subModel_id, gwm_color, interior_color, COUNT(*) as stock_total')
                ->groupBy('model_id', 'subModel_id', 'gwm_color', 'interior_color')
                ->get()
                ->keyBy(function ($item) {
                    return $item->model_id . '_' .
                        $item->subModel_id . '_' .
                        $item->gwm_color . '_' .
                        $item->interior_color;
                });
        } elseif ($brand == 3) {

            // Wuling: แยกตาม รุ่น + รุ่นย่อย + สี (gwm_color)
            $query->with(['gwmColor'])
                ->selectRaw('model_id, subModel_id, gwm_color, COUNT(*) as total')
                ->groupBy('model_id', 'subModel_id', 'gwm_color');

            $stocks = $stockQuery
                ->selectRaw('model_id, subModel_id, gwm_color, COUNT(*) as stock_total')
                ->groupBy('model_id', 'subModel_id', 'gwm_color')
                ->get()
                ->keyBy(function ($item) {
                    return $item->model_id . '_' .
                        $item->subModel_id . '_' .
                        $item->gwm_color;
                });
        } else {

            // Mitsubishi/อื่นๆ: แยกตาม รุ่น + รุ่นย่อย + สี (Color เป็น text)
            $query->selectRaw('model_id, subModel_id, Color, COUNT(*) as total')
                ->groupBy('model_id', 'subModel_id', 'Color');

            $stocks = $stockQuery
                ->selectRaw('model_id, subModel_id, color, COUNT(*) as stock_total')
                ->groupBy('model_id', 'subModel_id', 'color')
                ->get()
                ->keyBy(function ($item) {
                    return $item->model_id . '_' .
                        $item->subModel_id . '_' .
                        $item->color;
                });
        }

        $sales = $query->get();

        // ── ยอดขายรวมทุกรุ่น (ตัวหารของ Mix %) ──
        $grandTotal = $sales->sum('total');

        if ($grandTotal == 0) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่มีข้อมูลยอดส่งมอบย้อนหลัง 3 เดือน'
            ]);
        }

        $result = [];

        foreach ($sales as $sale) {

            // สัดส่วนยอดขายของรุ่น/สีนี้ เทียบยอดขายรวม (0–1)
            $mixPercent = $sale->total / $grandTotal;

            $modelOrder = optional($sale->model)->Name_TH ?? '';
            $subModelOrder = optional($sale->subModel)->name ?? '';
            $subDetail = optional($sale->subModel)->detail ?? '';

            $car = "รุ่นหลัก : {$modelOrder}<br>รุ่นย่อย : {$subDetail} - {$subModelOrder}";

            if ($brand == 2) {

                $color = optional($sale->gwmColor)->name ?? '-';
                $interior = optional($sale->interiorColor)->name ?? '-';

                $key = $sale->model_id . '_' .
                    $sale->subModel_id . '_' .
                    $sale->gwm_color . '_' .
                    $sale->interior_color;
            } elseif ($brand == 3) {

                $color = optional($sale->gwmColor)->name ?? '-';

                $key = $sale->model_id . '_' .
                    $sale->subModel_id . '_' .
                    $sale->gwm_color;
            } else {

                $color = $sale->Color ?? '-';
                $interior = '-';

                $key = $sale->model_id . '_' .
                    $sale->subModel_id . '_' .
                    $sale->Color;
            }

            // สต็อกที่มีอยู่ของรุ่น/สีนี้ (match ด้วย key เดียวกับตอนจัดกลุ่ม) ไม่เจอ = 0
            $currentStock = $stocks[$key]->stock_total ?? 0;

            // ควรสั่ง = โควต้าตาม mix (Mix% × target) − สต็อกที่มีอยู่ ; ถ้าติดลบปัดเป็น 0
            $forecastUnits = max(
                round($mixPercent * $target) - $currentStock,
                0
            );

            $result[] = [
                'subModel' => $car,
                'color' => $color,
                'interior_color' => $interior,
                'sold_last_3m' => $sale->total,
                'stock_available' => $currentStock,
                'mix_percent' => round($mixPercent * 100, 2),
                'forecast_units' => $forecastUnits
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $result,
            'brand' => $brand
        ]);
    }
}
