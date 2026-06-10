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
        $data = FilmPriceList::with('model')->get()
            ->groupBy('model_id')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'model_id' => $first->model_id,
                    'model'    => $first->model?->Name_TH ?? '-',
                    'sqft'     => number_format($first->sqft, 2),
                ];
            })
            ->values()
            ->map(function ($item, $index) {
                $editBtn = '<button class="btn btn-icon btn-warning text-white btnEditFilmPrice" data-model-id="' . $item['model_id'] . '">'
                    . '<i class="bx bx-edit"></i></button>';
                return array_merge($item, ['No' => $index + 1, 'Action' => $editBtn]);
            });

        return response()->json(['data' => $data]);
    }

    public function editModel(int $modelId)
    {
        $model       = TbCarmodel::findOrFail($modelId);
        $records     = FilmPriceList::where('model_id', $modelId)->with('filmBrand')->get();
        $filmBrands  = FilmBrand::orderBy('id')->get();
        $hasSunroof  = $records->where('has_sunroof', true)->isNotEmpty();
        $sqft        = $records->first()?->sqft;
        $sqftSunroof = $records->first()?->sqft_sunroof;

        return view('stock-film.price-list.edit-model', compact('model', 'records', 'filmBrands', 'hasSunroof', 'sqft', 'sqftSunroof'));
    }

    public function updateModel(Request $request, int $modelId)
    {
        try {
            $user       = Auth::user();
            $hasSunroof = $request->boolean('has_sunroof');
            $brands     = $request->input('brands', []);

            if (empty($brands)) {
                return response()->json(['success' => false, 'message' => 'กรุณาเพิ่มยี่ห้อฟิล์มอย่างน้อย 1 รายการ']);
            }

            $submittedBrandIds = collect($brands)->pluck('film_brand_id')->filter()->values()->toArray();

            FilmPriceList::where('model_id', $modelId)
                ->whereNotIn('film_brand_id', $submittedBrandIds)
                ->delete();

            $count = 0;
            foreach ($brands as $b) {
                if (empty($b['film_brand_id'])) continue;

                FilmPriceList::updateOrCreate(
                    ['model_id' => $modelId, 'film_brand_id' => $b['film_brand_id'], 'brand' => $user->brand ?? null],
                    [
                        'sqft'               => $request->sqft,
                        'price'              => !empty($b['price']) ? str_replace(',', '', $b['price']) : null,
                        'commission'         => !empty($b['commission']) ? str_replace(',', '', $b['commission']) : null,
                        'has_sunroof'        => $hasSunroof,
                        'sqft_sunroof'       => $hasSunroof ? $request->sqft_sunroof : null,
                        'price_sunroof'      => $hasSunroof && !empty($b['price_sunroof']) ? str_replace(',', '', $b['price_sunroof']) : null,
                        'commission_sunroof' => $hasSunroof && !empty($b['commission_sunroof']) ? str_replace(',', '', $b['commission_sunroof']) : null,
                        'branch'             => $user->branch ?? null,
                        'userZone'           => $user->userZone ?? null,
                        'userInsert'         => $user->id ?? null,
                    ]
                );
                $count++;
            }

            return response()->json(['success' => true, 'message' => "แก้ไขข้อมูล {$count} รายการเรียบร้อยแล้ว"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
        }
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
            $user       = Auth::user();
            $hasSunroof = $request->boolean('has_sunroof');
            $brands     = $request->input('brands', []);

            if (empty($brands)) {
                return response()->json(['success' => false, 'message' => 'กรุณาเพิ่มยี่ห้อฟิล์มอย่างน้อย 1 รายการ']);
            }

            $count = 0;
            foreach ($brands as $b) {
                if (empty($b['film_brand_id'])) continue;

                FilmPriceList::updateOrCreate(
                    [
                        'model_id'      => $request->model_id,
                        'film_brand_id' => $b['film_brand_id'],
                        'brand'         => $user->brand ?? null,
                    ],
                    [
                        'sqft'               => $request->sqft,
                        'price'              => !empty($b['price']) ? str_replace(',', '', $b['price']) : null,
                        'commission'         => !empty($b['commission']) ? str_replace(',', '', $b['commission']) : null,
                        'has_sunroof'        => $hasSunroof,
                        'sqft_sunroof'       => $hasSunroof ? $request->sqft_sunroof : null,
                        'price_sunroof'      => $hasSunroof && !empty($b['price_sunroof']) ? str_replace(',', '', $b['price_sunroof']) : null,
                        'commission_sunroof' => $hasSunroof && !empty($b['commission_sunroof']) ? str_replace(',', '', $b['commission_sunroof']) : null,
                        'branch'             => $user->branch ?? null,
                        'userZone'           => $user->userZone ?? null,
                        'userInsert'         => $user->id ?? null,
                    ]
                );
                $count++;
            }

            return response()->json(['success' => true, 'message' => "บันทึกข้อมูล {$count} รายการเรียบร้อยแล้ว"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
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

            $hasSunroof = $request->boolean('has_sunroof');

            $price->update([
                'model_id'           => $request->model_id,
                'film_brand_id'      => $request->film_brand_id,
                'sqft'               => $request->sqft,
                'price'              => $request->filled('price') ? str_replace(',', '', $request->price) : null,
                'commission'         => $request->filled('commission') ? str_replace(',', '', $request->commission) : null,
                'has_sunroof'        => $hasSunroof,
                'sqft_sunroof'       => $hasSunroof ? $request->sqft_sunroof : null,
                'price_sunroof'      => $hasSunroof && $request->filled('price_sunroof') ? str_replace(',', '', $request->price_sunroof) : null,
                'commission_sunroof' => $hasSunroof && $request->filled('commission_sunroof') ? str_replace(',', '', $request->commission_sunroof) : null,
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
