<?php

namespace App\Http\Controllers\forecast;

use App\Http\Controllers\Controller;
use App\Models\CarOrder;
use App\Models\Salecar;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Support\BrandFeature;

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
     *   brand ที่มีสีภายใน (config/brand.php interior_color_brands) : + สีภายใน (interior_color)
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
        $showInterior = BrandFeature::hasInteriorColor($brand);

        if (in_array($brand, [2, 3, 4])) {

            // GWM / Wuling / Lepas: แยกตาม รุ่น + รุ่นย่อย + สี (gwm_color)
            // brand ที่มีสีภายใน (ดู config/brand.php) แยกย่อยด้วย interior_color อีกชั้น
            $query->with($showInterior ? ['gwmColor', 'interiorColor'] : ['gwmColor'])
                ->selectRaw('model_id, subModel_id, gwm_color' . ($showInterior ? ', interior_color' : '') . ', COUNT(*) as total')
                ->groupBy(...array_filter(['model_id', 'subModel_id', 'gwm_color', $showInterior ? 'interior_color' : null]));

            $stocks = $stockQuery
                ->selectRaw('model_id, subModel_id, gwm_color' . ($showInterior ? ', interior_color' : '') . ', COUNT(*) as stock_total')
                ->groupBy(...array_filter(['model_id', 'subModel_id', 'gwm_color', $showInterior ? 'interior_color' : null]))
                ->get()
                ->keyBy(function ($item) use ($showInterior) {
                    return $item->model_id . '_' .
                        $item->subModel_id . '_' .
                        $item->gwm_color .
                        ($showInterior ? '_' . $item->interior_color : '');
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

            $interior = $showInterior ? (optional($sale->interiorColor)->name ?? '-') : '-';

            if (in_array($brand, [2, 3, 4])) {

                $color = optional($sale->gwmColor)->name ?? '-';

                $key = $sale->model_id . '_' .
                    $sale->subModel_id . '_' .
                    $sale->gwm_color .
                    ($showInterior ? '_' . $sale->interior_color : '');
            } else {

                $color = $sale->Color ?? '-';

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
            'brand' => $brand,
            // คุมคอลัมน์ "สีภายใน" ฝั่ง JS (brand ไหนมีบ้าง ดู config/brand.php)
            'show_interior' => $showInterior
        ]);
    }
}
