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

    public function forecastCalculate(Request $request)
    {
        $request->validate([
            'target' => 'required|numeric|min:1'
        ]);

        $target = $request->target;
        $brand = Auth::user()->brand;

        $startDate = Carbon::now()->subMonths(3)->startOfMonth();

        $query = Salecar::with(['model', 'subModel'])
            ->whereNotNull('DeliveryDate')
            ->where('DeliveryDate', '>=', $startDate);

        $stockQuery = CarOrder::where('car_status', 'Available')
            ->whereIn('status', ['finished', 'approved']);

        if ($brand == 2) {

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
        } else {

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
            } else {

                $color = $sale->Color ?? '-';
                $interior = '-';

                $key = $sale->model_id . '_' .
                    $sale->subModel_id . '_' .
                    $sale->Color;
            }

            $currentStock = $stocks[$key]->stock_total ?? 0;
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
