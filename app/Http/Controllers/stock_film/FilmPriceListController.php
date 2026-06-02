<?php

namespace App\Http\Controllers\stock_film;

use App\Http\Controllers\Controller;
use App\Models\FilmBrand;
use App\Models\FilmCostSetting;
use App\Models\FilmGlobalSetting;
use App\Models\FilmPriceList;
use App\Models\TbCarmodel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FilmPriceListController extends Controller
{
    public function index()
    {
        return view('stock-film.price-list.view');
    }

    public function list()
    {
        $prices = FilmPriceList::with(['model', 'filmBrand'])->get();

        $data = $prices->map(function ($p, $index) {
            return [
                'No'         => $index + 1,
                'model'      => $p->model?->Name_TH ?? '-',
                'film_brand' => $p->filmBrand?->name ?? '-',
                'position'   => $p->position === 'sunroof' ? 'Sunroof' : 'รอบคัน',
                'shade'      => $p->shade_display,
                'sqft'       => number_format($p->sqft, 2),
                'price'      => $p->price !== null ? number_format($p->price, 2) : '-',
                'commission' => $p->commission !== null ? number_format($p->commission, 2) : '-',
                'Action'     => view('stock-film.price-list.button', compact('p'))->render(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function create()
    {
        $models     = TbCarmodel::orderBy('Name_TH')->get();
        $filmBrands = FilmBrand::orderBy('id')->get();
        return view('stock-film.price-list.input', compact('models', 'filmBrands'));
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            FilmPriceList::create([
                'model_id'      => $request->model_id,
                'film_brand_id' => $request->film_brand_id,
                'position'      => $request->position,
                'front_shade'   => $request->position === 'รอบคัน' ? ($request->front_shade ?: null) : null,
                'body_shade'    => $request->position === 'รอบคัน' ? $request->body_shade : null,
                'sunroof_shade' => $request->position === 'sunroof' ? $request->sunroof_shade : null,
                'sqft'          => $request->sqft,
                'price'         => $request->filled('price') ? str_replace(',', '', $request->price) : null,
                'commission'    => $request->filled('commission') ? str_replace(',', '', $request->commission) : null,
                'brand'         => $user->brand ?? null,
                'branch'        => $user->branch ?? null,
                'userZone'      => $user->userZone ?? null,
                'userInsert'    => $user->id ?? null,
            ]);

            return response()->json(['success' => true, 'message' => 'เพิ่มข้อมูลเรียบร้อยแล้ว']);
        } catch (\Exception) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    public function edit(int $id)
    {
        $price      = FilmPriceList::with(['model', 'filmBrand'])->findOrFail($id);
        $models     = TbCarmodel::orderBy('Name_TH')->get();
        $filmBrands = FilmBrand::orderBy('id')->get();
        return view('stock-film.price-list.edit', compact('price', 'models', 'filmBrands'));
    }

    public function update(Request $request, int $id)
    {
        try {
            $price = FilmPriceList::findOrFail($id);

            $price->update([
                'model_id'      => $request->model_id,
                'film_brand_id' => $request->film_brand_id,
                'position'      => $request->position,
                'front_shade'   => $request->position === 'รอบคัน' ? ($request->front_shade ?: null) : null,
                'body_shade'    => $request->position === 'รอบคัน' ? $request->body_shade : null,
                'sunroof_shade' => $request->position === 'sunroof' ? $request->sunroof_shade : null,
                'sqft'          => $request->sqft,
                'price'         => $request->filled('price') ? str_replace(',', '', $request->price) : null,
                'commission'    => $request->filled('commission') ? str_replace(',', '', $request->commission) : null,
            ]);

            return response()->json(['success' => true, 'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว']);
        } catch (\Exception) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    public function calculate(Request $request)
    {
        $filmBrandId = $request->film_brand_id;
        $sqft        = (float) $request->sqft;

        if (!$filmBrandId || $sqft <= 0) {
            return response()->json(['success' => false, 'message' => 'กรุณาระบุยี่ห้อฟิล์มและจำนวน ตร.ฟุต']);
        }

        $global = FilmGlobalSetting::current();
        $cost   = FilmCostSetting::where('film_brand_id', $filmBrandId)->first();

        if (!$cost) {
            return response()->json(['success' => false, 'message' => 'ไม่พบข้อมูลต้นทุนฟิล์มนี้ กรุณาตั้งค่าก่อน']);
        }

        $costPerSqft = $cost->final_cost / (float) $global->roll_size;
        $result      = $global->calcPrice($sqft, $costPerSqft);

        return response()->json([
            'success'        => true,
            'price'          => $result['price'],
            'commission'     => $result['commission'],
            'price_fmt'      => number_format($result['price'], 2),
            'commission_fmt' => number_format($result['commission'], 2),
            'detail' => [
                'cost_per_sqft'  => round($costPerSqft, 2),
                'film_cost'      => round($sqft * (1 + $global->waste_pct / 100) * $costPerSqft, 2),
                'waste_pct'      => $global->waste_pct,
                'gp_pct'         => $global->gp_pct,
                'commission_pct' => $global->commission_pct,
            ],
        ]);
    }

    public function destroy(int $id)
    {
        try {
            FilmPriceList::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'ลบข้อมูลเรียบร้อยแล้ว']);
        } catch (\Exception) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }
}
