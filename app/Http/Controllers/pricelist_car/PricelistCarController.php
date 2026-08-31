<?php

namespace App\Http\Controllers\pricelist_car;

use App\Http\Controllers\Controller;
use App\Models\TbCarmodel;
use App\Models\TbPricelistCar;
use App\Models\TbSubcarmodel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PricelistCarController extends Controller
{
    public function index()
    {
        return view('car.pricelist-car.view');
    }

    public function listPricelist()
    {
        $prices = TbPricelistCar::all();
        $userBrand = Auth::user()->brand;

        $modelIds    = $prices->pluck('model_id')->unique()->filter();
        $subModelIds = $prices->pluck('subModel_id')->unique()->filter();

        $models    = TbCarmodel::whereIn('id', $modelIds)->pluck('Name_TH', 'id');
        $subModels = TbSubcarmodel::whereIn('id', $subModelIds)->pluck('name', 'id');
        $subModelsDetail = TbSubcarmodel::whereIn('id', $subModelIds)->pluck('detail', 'id');

        // manager ห้ามเห็นราคาทุนรถ — ไม่ส่งตัวเลขไปกับ JSON เลย (คอลัมน์ก็ถูกปิดที่ car.js อีกชั้น)
        $showCost = Auth::user()->canViewCarCost();

        $data = $prices->map(function ($p, $index) use ($models, $subModels, $subModelsDetail, $userBrand, $showCost) {
            $hide = in_array($userBrand, [2, 3, 4]);

            $model_id = $models[$p->model_id] ?? '-';
            $subModel_id = $subModels[$p->subModel_id] ?? '-';
            $subModelDetail = $subModelsDetail[$p->subModel_id] ?? null;

            if ($userBrand == 1 && $subModelDetail) {
                $subModel_id = "{$subModelDetail}<br>{$subModel_id}";
            }

            $car = "หลัก : {$model_id}<br>ย่อย : {$subModel_id}";

            return [
                'No'         => $index + 1,
                'car'   => $car,
                'option' => $hide ? '-' : ($p->option ?? '-'),
                'year'       => $p->year ?? '-',
                'color'  => $hide ? '-' : ($p->color ?? '-'),
                'dnp'        => $showCost && $p->dnp !== null ? number_format($p->dnp, 2) : '-',
                'msrp'       => $p->msrp !== null ? number_format($p->msrp, 2) : '-',
                'dm' => $hide ? '-' : ($p->dm !== null ? number_format($p->dm, 2) : '-'),
                'ri' => $hide ? '-' : ($p->ri !== null ? number_format($p->ri, 2) : '-'),
                'ws' => $hide ? '-' : ($p->ws !== null ? number_format($p->ws, 2) : '-'),
                'Action'     => view('car.pricelist-car.button', compact('p'))->render(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function create()
    {
        $models = TbCarmodel::all();
        $brand  = Auth::user()->brand ?? null;

        return view('car.pricelist-car.input', compact('models', 'brand'));
    }

    public function getSubModel($model_id)
    {
        $subModels = TbSubcarmodel::where('model_id', $model_id)
            ->select('id', 'name', 'detail')
            ->orderBy('name')
            ->get();

        return response()->json($subModels);
    }

    public function store(Request $request)
    {
        try {
            $user  = Auth::user();
            $brand = $user->brand ?? null;

            $dnp = $request->filled('dnp') ? (float) str_replace(',', '', $request->dnp) : null;

            $data = [
                'model_id'    => $request->model_id,
                'subModel_id' => $request->subModel_id,
                'year'        => $this->clean($request->year),
                'dnp'         => $dnp,
                'msrp'        => $request->filled('msrp') ? str_replace(',', '', $request->msrp) : null,
                'brand'       => $brand,
                'userZone'    => $user->userZone ?? null,
                'userInsert'  => $user->id ?? null,
                'branch'      => $user->branch ?? null,
            ];

            if ($brand == 1) {
                $data['option'] = $this->clean($request->option);
                $data['color']  = $this->clean($request->color);
                $data['dm']     = $request->filled('dm') ? str_replace(',', '', $request->dm) : null;
                $data['ri']     = $request->filled('ri') ? str_replace(',', '', $request->ri) : null;
                // WS: ใช้ค่าที่กรอก (JS คำนวณเติมให้อัตโนมัติ แต่แก้ไขได้) ถ้าเว้นว่างค่อยคำนวณจากราคาทุน (DNP)
                $data['ws']     = $request->filled('ws') ? (float) str_replace(',', '', $request->ws) : $this->calcWs($dnp);
            }

            // กันข้อมูลซ้ำ : รุ่นย่อย + ปี + ประเภทสี (ในแบรนด์เดียวกัน) ต้องมีได้แถวเดียว
            // ถ้าซ้ำจะยังไม่บันทึก แต่ส่งค่าเดิม-ค่าใหม่กลับไปให้ผู้ใช้ยืนยันว่าจะทับของเดิมหรือไม่
            $duplicate = $this->findDuplicate($data);

            if ($duplicate) {
                if (!$request->boolean('confirm_overwrite')) {
                    return response()->json($this->duplicatePayload($duplicate, $data, $brand), 409);
                }

                $duplicate->update($this->keepCost($data, $duplicate));

                return response()->json([
                    'success' => true,
                    'message' => 'ทับข้อมูลเดิมเรียบร้อยแล้ว',
                ]);
            }

            TbPricelistCar::create($data);

            return response()->json([
                'success' => true,
                'message' => 'เพิ่มข้อมูลเรียบร้อยแล้ว',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน',
            ], 500);
        }
    }

    public function edit($id)
    {
        $price    = TbPricelistCar::findOrFail($id);
        $models   = TbCarmodel::all();
        $subModels = TbSubcarmodel::where('model_id', $price->model_id)
            ->select('id', 'name', 'detail')
            ->orderBy('name')
            ->get();
        $brand = Auth::user()->brand ?? null;

        return view('car.pricelist-car.edit', compact('price', 'models', 'subModels', 'brand'));
    }

    public function update(Request $request, $id)
    {
        try {
            $price = TbPricelistCar::findOrFail($id);
            $brand = Auth::user()->brand ?? null;

            $dnp = $request->filled('dnp') ? (float) str_replace(',', '', $request->dnp) : null;

            $data = [
                'model_id'    => $request->model_id,
                'subModel_id' => $request->subModel_id,
                'year'        => $this->clean($request->year),
                'dnp'         => $dnp,
                'msrp'        => $request->filled('msrp') ? str_replace(',', '', $request->msrp) : null,
            ];

            if ($brand == 1) {
                $data['option'] = $this->clean($request->option);
                $data['color']  = $this->clean($request->color);
                $data['dm']     = $request->filled('dm') ? str_replace(',', '', $request->dm) : null;
                $data['ri']     = $request->filled('ri') ? str_replace(',', '', $request->ri) : null;
                // WS: ใช้ค่าที่กรอก (JS คำนวณเติมให้อัตโนมัติ แต่แก้ไขได้) ถ้าเว้นว่างค่อยคำนวณจากราคาทุน (DNP)
                $data['ws']     = $request->filled('ws') ? (float) str_replace(',', '', $request->ws) : $this->calcWs($dnp);
            }

            // แก้ไขแล้วไปชนกับแถวอื่นที่มีอยู่ — ไม่ให้บันทึก (ทับให้ไม่ได้ เพราะจะเหลือข้อมูลกำพร้าอีกแถว)
            if ($this->findDuplicate($data, $price->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'มีข้อมูลราคาของรุ่นย่อย / ปี / ประเภทสี นี้อยู่แล้วในอีกรายการหนึ่ง กรุณาไปแก้ไขที่รายการเดิม',
                ], 409);
            }

            $price->update($this->keepCost($data, $price));

            return response()->json([
                'success' => true,
                'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน',
            ], 500);
        }
    }

    /** ตัดช่องว่างหัวท้าย และมองค่าว่างเป็น null เพื่อให้เทียบซ้ำได้ตรงกัน */
    private function clean($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * หาแถวที่ซ้ำกับข้อมูลที่กำลังจะบันทึก
     * คีย์ซ้ำ = รุ่นย่อย + ปี + ประเภทสี (brand ถูกกรองโดย BrandScope อยู่แล้ว)
     * — ไม่นับ option เป็นคีย์ เพราะตอนดึงราคาไปใช้ในใบจอง/PO ค้นด้วยรุ่นย่อย+ปี+สี เท่านั้น
     */
    private function findDuplicate(array $data, $ignoreId = null): ?TbPricelistCar
    {
        $query = TbPricelistCar::where('subModel_id', $data['subModel_id'])
            ->where('year', $data['year']);

        $color = $data['color'] ?? null;

        if ($color === null) {
            $query->where(fn($q) => $q->whereNull('color')->orWhere('color', ''));
        } else {
            $query->where('color', $color);
        }

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->orderBy('id')->first();
    }

    /**
     * คงราคาทุนเดิมไว้เมื่อคนบันทึกเป็น role ที่ห้ามเห็นราคาทุน (manager)
     * ช่อง dnp ถูกซ่อนจากฟอร์ม ค่าจึงไม่ถูกส่งกลับมา — ถ้าไม่ดัก แค่กดบันทึกราคาทุนจะหาย
     * WS คำนวณจาก DNP จึงต้องคิดใหม่ตามค่าเดิมด้วย (เว้นแต่ผู้ใช้กรอก WS มาเอง)
     */
    private function keepCost(array $data, TbPricelistCar $existing): array
    {
        if (Auth::user()->canViewCarCost()) {
            return $data;
        }

        $data['dnp'] = $existing->dnp;

        if (array_key_exists('ws', $data) && !request()->filled('ws')) {
            $data['ws'] = $this->calcWs($existing->dnp);
        }

        return $data;
    }

    /** ข้อมูลเปรียบเทียบค่าเดิม–ค่าใหม่ ส่งให้หน้าเว็บถามยืนยันก่อนทับ */
    private function duplicatePayload(TbPricelistCar $existing, array $data, $brand): array
    {
        $fields = Auth::user()->canViewCarCost()
            ? ['dnp' => 'ราคาทุน (DNP)', 'msrp' => 'ราคาขาย (MSRP)']
            : ['msrp' => 'ราคาขาย (MSRP)'];

        if ($brand == 1) {
            $fields += ['dm' => 'DM', 'ri' => 'RI', 'ws' => 'WS'];
        }

        $money = fn($v) => $v === null || $v === '' ? '-' : number_format((float) $v, 2);

        $compare = [];
        foreach ($fields as $key => $label) {
            $compare[] = [
                'label'   => $label,
                'old'     => $money($existing->{$key}),
                'new'     => $money($data[$key] ?? null),
                'changed' => (float) $existing->{$key} !== (float) ($data[$key] ?? 0),
            ];
        }

        if ($brand == 1) {
            $compare[] = [
                'label'   => 'Option',
                'old'     => $existing->option ?: '-',
                'new'     => $data['option'] ?: '-',
                'changed' => $existing->option !== $data['option'],
            ];
        }

        $model    = TbCarmodel::find($data['model_id']);
        $subModel = TbSubcarmodel::find($data['subModel_id']);

        return [
            'success'     => false,
            'duplicate'   => true,
            'message'     => 'มีข้อมูลราคาของรุ่นย่อย / ปี / ประเภทสี นี้อยู่แล้ว',
            'existing_id' => $existing->id,
            'info'        => [
                'model'    => $model->Name_TH ?? '-',
                'subModel' => $subModel->name ?? '-',
                'year'     => $data['year'] ?? '-',
                'color'    => $data['color'] ?? null,
            ],
            'compare'     => $compare,
        ];
    }

    /**
     * คำนวณค่า WS (ดอกลอยสต๊อกต่อเดือน) จากราคาทุน (DNP)
     * - ราคาทุนถอด VAT = dnp - (dnp * 7/107)
     * - WS = (ราคาทุนถอด VAT * 9%) / 365 * จำนวนวันของเดือนปัจจุบัน
     * - ปัดเป็นเลขเต็มหลักร้อย เช่น 1548 → 1500, 1559 → 1600
     */
    private function calcWs($dnp): ?float
    {
        if (!$dnp) {
            return null;
        }

        $dnpExVat    = $dnp - ($dnp * 7 / 107);
        $daysInMonth = now()->daysInMonth;
        $ws          = ($dnpExVat * 0.09) / 365 * $daysInMonth;

        return round($ws / 100) * 100;
    }

    public function destroy($id)
    {
        try {
            $price = TbPricelistCar::findOrFail($id);
            $price->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบข้อมูลเรียบร้อยแล้ว',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน',
            ], 500);
        }
    }
}
