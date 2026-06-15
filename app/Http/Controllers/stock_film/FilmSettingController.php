<?php

namespace App\Http\Controllers\stock_film;

use App\Http\Controllers\Controller;
use App\Models\FilmBrand;
use App\Models\FilmCostSetting;
use App\Models\FilmGlobalSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FilmSettingController extends Controller
{
    public function index()
    {
        $global     = FilmGlobalSetting::current();
        $filmBrands = FilmBrand::orderBy('id')->get();
        $costSettings = FilmCostSetting::with('filmBrand')->get()->keyBy('film_brand_id');

        return view('stock-film.settings.view', compact('global', 'filmBrands', 'costSettings'));
    }

    public function modal()
    {
        $global       = FilmGlobalSetting::current();
        $filmBrands   = FilmBrand::orderBy('id')->get();
        $costSettings = FilmCostSetting::with('filmBrand')->get()->keyBy('film_brand_id');

        return view('stock-film.settings.modal', compact('global', 'filmBrands', 'costSettings'));
    }

    public function updateGlobal(Request $request)
    {
        try {
            $user   = Auth::user();
            $global = FilmGlobalSetting::first();

            $data = [
                'roll_size'      => $request->roll_size,
                'waste_pct'      => $request->waste_pct,
                'gp_pct'         => $request->gp_pct,
                'commission_pct' => $request->commission_pct,
                'brand'          => $user->brand ?? null,
                'branch'         => $user->branch ?? null,
                'userZone'       => $user->userZone ?? null,
                'userInsert'     => $user->id ?? null,
            ];

            if ($global) {
                $global->update($data);
            } else {
                FilmGlobalSetting::create($data);
            }

            return response()->json(['success' => true, 'message' => 'บันทึก Global Settings เรียบร้อยแล้ว']);
        } catch (\Exception) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    public function updateCost(Request $request)
    {
        try {
            $user = Auth::user();

            foreach ($request->costs as $filmBrandId => $values) {
                FilmCostSetting::updateOrCreate(
                    ['film_brand_id' => $filmBrandId, 'brand' => $user->brand ?? null],
                    [
                        'roll_price'  => $values['roll_price'] ?? 0,
                        'discount'    => $values['discount'] ?? 0,
                        'branch'      => $user->branch ?? null,
                        'userZone'    => $user->userZone ?? null,
                        'userInsert'  => $user->id ?? null,
                    ]
                );
            }

            return response()->json(['success' => true, 'message' => 'บันทึกข้อมูลต้นทุนฟิล์มเรียบร้อยแล้ว']);
        } catch (\Exception) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }
}
