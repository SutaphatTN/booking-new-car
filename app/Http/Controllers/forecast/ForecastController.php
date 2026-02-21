<?php

namespace App\Http\Controllers\forecast;

use App\Http\Controllers\Controller;
use App\Models\Salecar;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ForecastController extends Controller
{
    public function forecastForm()
    {
        return view('forecast.view');
    }

    public function forecastCalculate(Request $request)
    {
        $request->validate([
            'target' => 'required|numeric|min:1'
        ]);

        $target = $request->target;

        $startDate = Carbon::now()->subMonths(3)->startOfMonth();

        // ดึงข้อมูล 3 เดือนย้อนหลังจาก salecars
        $sales = Salecar::with('model', 'subModel', 'gwmColor', 'interiorColor')
            ->whereNotNull('DeliveryDate')
            ->where('DeliveryDate', '>=', $startDate)
            ->selectRaw('model_id, subModel_id, gwm_color, interior_color, COUNT(*) as total')
            ->groupBy('model_id', 'subModel_id', 'gwm_color', 'interior_color')
            ->get();

        // รวมยอดทั้งหมด
        $grandTotal = $sales->sum('total');

        if ($grandTotal == 0) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่มีข้อมูลยอดส่งมอบย้อนหลัง 3 เดือน'
            ]);
        }

        $result = [];

        foreach ($sales as $sale) {

            $mixPercent = $sale->total / $grandTotal;
            $forecastUnits = round($mixPercent * $target);

            $modelOrder = $sale->model ? $sale->model->Name_TH : '';
            $subModelOrder = $sale->subModel ? $sale->subModel->name : '';
            $subDetail = $sale->subModel ? $sale->subModel->detail : '';

            $car = "รุ่นหลัก : {$modelOrder}<br>รุ่นย่อย : {$subDetail} - {$subModelOrder}";

            $result[] = [
                'subModel' => $car,
                'color' => $sale->gwmColor->name,
                'interior_color' => $sale->interiorColor->name,
                'sold_last_3m' => $sale->total,
                'mix_percent' => round($mixPercent * 100, 2),
                'forecast_units' => $forecastUnits
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
}
