<?php

namespace App\Http\Controllers\stock_film;

use App\Http\Controllers\Controller;
use App\Models\CarOrder;
use App\Models\FilmBrand;
use App\Models\FilmPriceList;
use App\Models\FilmStock;
use App\Models\FilmUsage;
use App\Models\FilmUsageItem;
use App\Models\Salecar;
use App\Models\TbCarmodel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FilmUsageController extends Controller
{
    public function index()
    {
        return view('stock-film.usage.view');
    }

    public function list()
    {
        $records = FilmUsage::with(['model', 'filmBrand', 'items'])->get();

        $data = $records->map(function ($r, $index) {
            $typeBadge = $r->type === 'bp'
                ? '<span class="badge bg-warning text-dark">BP</span>'
                : '<span class="badge bg-info">ทั่วไป</span>';

            $totalSqft  = $r->items->sum('sqft_used');
            $totalPrice = $r->items->sum('price');

            return [
                'No'          => $index + 1,
                'type'        => $typeBadge,
                'order_date'  => $r->order_date?->format('d/m/Y') ?? '-',
                'vin'         => $r->vin ?? '-',
                'customer'    => $r->customer_name ?? '-',
                'sale_person' => $r->sale_person ?? '-',
                'model'       => $r->model?->Name_TH ?? '-',
                'film_brand'  => $r->filmBrand?->name ?? '-',
                'total_sqft'  => $totalSqft > 0 ? number_format($totalSqft, 2) : '-',
                'total_price' => $totalPrice > 0 ? number_format($totalPrice, 2) : '-',
                'Action'      => view('stock-film.usage.button', compact('r'))->render(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function create()
    {
        $user         = Auth::user();
        $brandForSale = $user->brand == 3 ? 1 : $user->brand;
        $models       = TbCarmodel::orderBy('Name_TH')->get();
        $filmBrands   = FilmBrand::orderBy('id')->get();
        $saleUsers    = User::whereIn('role', ['sale', 'lead_sale'])
            ->where('brand', $brandForSale)
            ->orderBy('name')
            ->get(['id', 'name']);
        return view('stock-film.usage.create', compact('models', 'filmBrands', 'saleUsers'));
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            DB::beginTransaction();

            $usage = FilmUsage::create([
                'type'         => $request->type,
                'order_date'   => $request->order_date,
                'vin'          => $request->vin ?: null,
                'car_order_id' => $request->car_order_id ?: null,
                'salecar_id'   => $request->salecar_id ?: null,
                'customer_name'=> $request->customer_name,
                'sale_person'  => $request->sale_person,
                'model_id'     => $request->model_id ?: null,
                'film_brand_id'=> $request->film_brand_id ?: null,
                'brand'        => $user->brand ?? null,
                'branch'       => $user->branch ?? null,
                'userZone'     => $user->userZone ?? null,
                'userInsert'   => $user->id ?? null,
            ]);

            $items = $request->input('items', []);
            foreach ($items as $item) {
                if (empty($item['position'])) continue;

                $sqftUsed = !empty($item['sqft_used']) ? (float) $item['sqft_used'] : null;

                FilmUsageItem::create([
                    'film_usage_id' => $usage->id,
                    'position'      => $item['position'],
                    'shade'         => $item['shade'] ?: null,
                    'film_stock_id' => $item['film_stock_id'] ?: null,
                    'stock_no'      => $item['stock_no'] ?: null,
                    'sqft_used'     => $sqftUsed,
                    'price'         => !empty($item['price']) ? str_replace(',', '', $item['price']) : null,
                    'commission'    => !empty($item['commission']) ? str_replace(',', '', $item['commission']) : null,
                ]);

                // ตัดสต็อก
                if (!empty($item['film_stock_id']) && $sqftUsed > 0) {
                    FilmStock::where('id', $item['film_stock_id'])
                        ->increment('used_qty', $sqftUsed);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว', 'id' => $usage->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $usage = FilmUsage::with('items')->findOrFail($id);

            DB::beginTransaction();

            // คืนสต็อกก่อนลบ
            foreach ($usage->items as $item) {
                if ($item->film_stock_id && $item->sqft_used > 0) {
                    FilmStock::where('id', $item->film_stock_id)
                        ->decrement('used_qty', (float) $item->sqft_used);
                }
            }

            $usage->items()->delete();
            $usage->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'ลบข้อมูลเรียบร้อยแล้ว']);
        } catch (\Exception) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    // ── AJAX: ค้นหา VIN ────────────────────────────────────────
    public function vinSearch(Request $request)
    {
        $vin = trim($request->vin);
        if (!$vin) {
            return response()->json(['found' => false, 'message' => 'กรุณาระบุเลข VIN']);
        }

        $carOrder = CarOrder::where('vin_number', $vin)
            ->where('car_status', '!=', 'Available')
            ->first();
        if (!$carOrder) {
            return response()->json(['found' => false, 'message' => 'ไม่พบข้อมูลเลข VIN นี้ในระบบ']);
        }

        $salecar = Salecar::with(['customer', 'model'])
            ->where('CarOrderID', $carOrder->id)
            ->latest()
            ->first();

        if (!$salecar) {
            return response()->json(['found' => false, 'message' => 'ไม่พบข้อมูลการขายสำหรับ VIN นี้']);
        }

        $customer     = $salecar->customer;
        $customerName = trim(($customer->FirstName ?? '') . ' ' . ($customer->LastName ?? ''));
        $salePerson   = User::withTrashed()->find($salecar->SaleID)?->name ?? '';
        $model        = $salecar->model;

        return response()->json([
            'found'         => true,
            'car_order_id'  => $carOrder->id,
            'salecar_id'    => $salecar->id,
            'customer_name' => $customerName,
            'sale_person'   => $salePerson,
            'model_id'      => $model?->id,
            'model_name'    => $model?->Name_TH ?? '-',
        ]);
    }

    // ── AJAX: ค้นหา VIN แบบบางส่วน ────────────────────────────
    public function vinSuggest(Request $request)
    {
        $q = strtoupper(trim($request->get('q', '')));
        if (strlen($q) < 3) {
            return response()->json([]);
        }

        $carOrders = CarOrder::where('vin_number', 'LIKE', "%{$q}%")
            ->where('car_status', '!=', 'Available')
            ->limit(8)
            ->get(['id', 'vin_number']);

        $results = [];
        foreach ($carOrders as $carOrder) {
            $salecar = Salecar::with(['customer', 'model'])
                ->where('CarOrderID', $carOrder->id)
                ->latest()
                ->first();

            $customerName = '-';
            $salePerson   = '';
            $modelId      = null;
            $modelName    = '-';

            if ($salecar) {
                $customer     = $salecar->customer;
                $customerName = trim(($customer->FirstName ?? '') . ' ' . ($customer->LastName ?? '')) ?: '-';
                $salePerson   = User::withTrashed()->find($salecar->SaleID)?->name ?? '';
                $model        = $salecar->model;
                $modelId      = $model?->id;
                $modelName    = $model?->Name_TH ?? '-';
            }

            $results[] = [
                'vin'           => $carOrder->vin_number,
                'car_order_id'  => $carOrder->id,
                'salecar_id'    => $salecar?->id,
                'customer_name' => $customerName,
                'sale_person'   => $salePerson,
                'model_id'      => $modelId,
                'model_name'    => $modelName,
            ];
        }

        return response()->json($results);
    }

    // ── AJAX: ดึงราคาจาก PriceList ─────────────────────────────
    public function priceListLookup(Request $request)
    {
        $pl = FilmPriceList::where('model_id', $request->model_id)
            ->where('film_brand_id', $request->film_brand_id)
            ->first();

        if (!$pl) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found'              => true,
            'sqft'               => $pl->sqft,
            'price'              => $pl->price,
            'commission'         => $pl->commission,
            'has_sunroof'        => $pl->has_sunroof,
            'sqft_sunroof'       => $pl->sqft_sunroof,
            'price_sunroof'      => $pl->price_sunroof,
            'commission_sunroof' => $pl->commission_sunroof,
        ]);
    }

    // ── AJAX: ค้นหา Stock ──────────────────────────────────────
    public function stockSearch(Request $request)
    {
        $brand = FilmBrand::find($request->film_brand_id);

        $query = FilmStock::where('film_brand_id', $request->film_brand_id)
            ->where('shade', $request->shade)
            ->whereRaw('(initial_qty - used_qty) > 0');

        if ($brand?->code) {
            $query->whereRaw('SUBSTRING(stock_no, 2, 2) = ?', [$brand->code]);
        }

        return response()->json(
            $query->get(['id', 'stock_no', 'initial_qty', 'used_qty'])
        );
    }
}
