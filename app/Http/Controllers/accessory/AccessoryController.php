<?php

namespace App\Http\Controllers\accessory;

use App\Http\Controllers\Controller;
use App\Models\AccessoryPartner;
use App\Models\AccessoryPrice;
use App\Models\AccessoryType;
use App\Models\Saleaccessory;
use App\Models\TbCarmodel;
use App\Models\TbSubcarmodel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Traits\ConvertsThaiDate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AccessoryController extends Controller
{
    use ConvertsThaiDate;
    public function index()
    {
        $acc = AccessoryPrice::all();
        return view('accessory.view', compact('acc'));
    }

    public function listAccessory(Request $request)
    {
        $draw   = (int) ($request->draw ?? 1);
        $start  = (int) ($request->start ?? 0);
        $length = (int) ($request->length ?? 10);
        $search = trim($request->input('search.value', ''));

        $base = AccessoryPrice::query();

        $recordsTotal = (clone $base)->count();

        if ($search) {
            $base->where(function ($q) use ($search) {
                $q->whereHas('partner', fn($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('model', fn($q) => $q->where('Name_TH', 'like', "%{$search}%"))
                    ->orWhere('accessory_id', 'like', "%{$search}%")
                    ->orWhere('detail', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $base)->count();

        $acc = $base
            ->with('partner', 'model')
            ->orderBy('id')
            ->skip($start)
            ->take($length)
            ->get();

        $rowNum = $start + 1;
        $data = $acc->map(function ($a) use (&$rowNum) {
            $index = $rowNum++ - 1;
            $partnerA = $a->partner ? $a->partner->name : '';
            $modelC = $a->model ? $a->model->Name_TH : '';

            $statusSwitch = '
            <div class="d-flex justify-content-center align-items-center">
                <div class="form-check form-switch m-0">
                    <input class="form-check-input status-acc" 
                        type="checkbox"
                        id="status_acc_' . $a->id . '"
                        name="status_acc_' . $a->id . '"
                        data-id="' . $a->id . '"
                        ' . ($a->active === 'active' ? 'checked' : '') . '>
                </div>
            </div>
            ';

            $saleText = '-';
            if ($a->sale !== null) {
                $saleText = number_format($a->sale, 2);
                if ($a->comSale !== null && $a->comSale > 0) {
                    $saleText .= ' (' . number_format($a->comSale, 2) . ')';
                }
            }

            $row = fn($icon, $class, $tip, $text) =>
                "<div class=\"text-start\"><i class=\"bx {$icon} {$class} me-1\" data-bs-toggle=\"tooltip\" title=\"{$tip}\"></i>:&nbsp;{$text}</div>";

            $fullName = $row('bx-barcode', 'text-dark', 'รหัส', $a->accessory_id)
                      . $row('bx-label',   'text-primary',   'ชื่อ', $a->detail);

            $costSpare = $a->cost_spare !== null ? number_format($a->cost_spare, 2) : '-';
            $cost = $a->cost !== null ? number_format($a->cost, 2) : '-';
            $sale = $a->sale !== null ? number_format($a->sale, 2) : '-';
            $promo = $a->promo !== null ? number_format($a->promo, 2) : '-';

            $fullCost = $row('bx-box',          'text-warning', 'ราคาทุนอะไหล่', $costSpare)
                      . $row('bx-dollar',       'text-danger',  'ราคาทุน',       $cost)
                      . $row('bx-purchase-tag', 'text-success', 'ราคาขาย',       $sale)
                      . $row('bx-gift',         'text-info',    'ราคาพิเศษ',     $promo);

            return [
                'No' => $index + 1,
                'accessoryPartner_id' => $partnerA,
                'name' => $fullName,
                'model' => $modelC,
                'cost' => $fullCost,
                'active' => $statusSwitch,
                'Action' => view('accessory.button', compact('a'))->render()
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data->values(),
        ]);
    }

    public function statusAcc(Request $request)
    {
        $id = $request->id;
        $status = $request->status;

        $acc = AccessoryPrice::find($id);
        if (!$acc) {
            return response()->json(['success' => false, 'message' => 'ไม่พบข้อมูล'], 404);
        }

        $acc->active = $status;
        $acc->save();

        return response()->json(['success' => true, 'message' => 'อัปเดตสถานะเรียบร้อยแล้ว']);
    }

    public function create()
    {
        $acc = AccessoryPrice::all();
        $model = TbCarmodel::all();
        $partner = AccessoryPartner::all();
        $type = AccessoryType::all();
        return view('accessory.input', compact('acc', 'model', 'partner', 'type'));
    }

    public function getSubModelAcc($model_id)
    {
        $subModels = TbSubcarmodel::where('model_id', $model_id)
            ->select('id', 'name', 'detail')
            ->orderBy('name')
            ->get();

        return response()->json($subModels);
    }

    /**
     * ราคาทุนอะไหล่ (cost_spare) ใช้เป็นยอด "ของแถม" ตอนขออนุมัติเกินงบ
     * ถ้าเป็น 0 ยอดที่เหลือในอีเมลจะสูงกว่าความจริง → ห้ามกรอก 0 ผ่านฟอร์ม
     *
     * $allowZero = true เฉพาะตอนแก้ไขแถวที่ค่าเดิมใน DB เป็น 0 อยู่แล้ว (ตั้งไว้เองจากฐานข้อมูล)
     * เพื่อให้ยังแก้ชื่อ/วันที่ของแถวนั้นได้โดยไม่ถูกบังคับทับ 0 ทิ้ง
     *
     * ต้องเรียก "นอก" try/catch เพราะ ValidationException เป็นลูกของ \Exception
     */
    private function validateCostSpare(Request $request, bool $allowZero = false): void
    {
        $request->merge([
            'cost_spare' => $request->filled('cost_spare')
                ? str_replace(',', '', (string) $request->cost_spare)
                : null,
        ]);

        $request->validate([
            'cost_spare' => $allowZero
                ? 'required|numeric|min:0'
                : 'required|numeric|gt:0',
        ], [
            'cost_spare.required' => 'กรุณากรอกราคาทุนอะไหล่',
            'cost_spare.numeric'  => 'ราคาทุนอะไหล่ต้องเป็นตัวเลข',
            'cost_spare.gt'       => 'ราคาทุนอะไหล่ต้องมากกว่า 0',
            'cost_spare.min'      => 'ราคาทุนอะไหล่ต้องไม่ติดลบ',
        ]);
    }

    function store(Request $request)
    {
        $this->validateCostSpare($request);

        try {
            $active = 'active';

            $data = [
                'model_id' => $request->model_id,
                'accessory_id' => $request->accessory_id,
                'detail' => $request->detail,
                'accessoryType_id' => $request->accessoryType_id,
                'is_standard' => $request->boolean('is_standard'),
                'is_registration' => $request->boolean('is_registration'),
                'accessoryPartner_id' => $request->accessoryPartner_id,
                'cost_spare' => $request->cost_spare,
                'cost' => $request->filled('cost')
                    ? str_replace(',', '', $request->cost)
                    : null,
                'sale' => $request->filled('sale')
                    ? str_replace(',', '', $request->sale)
                    : null,
                'comSale' => $request->filled('comSale')
                    ? str_replace(',', '', $request->comSale)
                    : null,
                'promo' => $request->filled('promo')
                    ? str_replace(',', '', $request->promo)
                    : null,
                'userZone' => Auth::user()->userZone ?? null,
                'brand' => Auth::user()->brand ?? null,
                'branch' => Auth::user()->branch ?? null,
                'startDate' => $this->toGregorian($request->startDate),
                'endDate' => $this->toGregorian($request->endDate),
                'active' => $active,
            ];

            AccessoryPrice::create($data);

            return response()->json([
                'success' => true,
                'message' => 'เพิ่มข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
        }
    }

    public function viewMore($id)
    {
        $acc = AccessoryPrice::with([
            'model',
            'subModel',
            'type',
            'partner',
        ])->find($id);

        return view('accessory.view-more', compact('acc'));
    }

    public function edit($id)
    {
        $acc = AccessoryPrice::findOrFail($id);
        $model = TbCarmodel::all();
        $subModels = TbSubcarmodel::where('model_id', $acc->model_id)->get();
        $partner = AccessoryPartner::all();
        $type = AccessoryType::all();
        return view('accessory.edit', compact('acc', 'model', 'subModels', 'partner', 'type'));
    }

    public function update(Request $request, $id)
    {
        $acc = AccessoryPrice::findOrFail($id);

        // ค่าเดิมเป็น 0 (ตั้งจาก DB) → ยอมให้บันทึก 0 ซ้ำได้ | NULL หรือ > 0 → บังคับ > 0
        $this->validateCostSpare($request, $acc->cost_spare !== null && (float) $acc->cost_spare === 0.0);

        try {
            $data = $request->except(['_token', '_method']);

            $data['cost_spare'] = $request->cost_spare;

            $data['cost'] = $request->cost
                ? str_replace(',', '', $request->cost)
                : null;

            $data['sale'] = $request->sale
                ? str_replace(',', '', $request->sale)
                : null;

            $data['comSale'] = $request->comSale
                ? str_replace(',', '', $request->comSale)
                : null;

            $data['promo'] = $request->promo
                ? str_replace(',', '', $request->promo)
                : null;

            $acc->update($data);

            return response()->json([
                'success' => true,
                'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
        }
    }

    function destroy($id)
    {
        try {
            $acc = AccessoryPrice::findOrFail($id);
            $acc->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
        }
    }

    //partner
    public function viewPartner()
    {
        $partner = AccessoryPartner::all();
        return view('accessory.partner.view', compact('partner'));
    }

    public function listPartner()
    {
        $partner = AccessoryPartner::all();

        $data = $partner->map(function ($p, $index) {

            return [
                'No' => $index + 1,
                'name' => $p->name,
                'Action' => view('accessory.partner.button', compact('p'))->render()
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function createPartner()
    {
        $partner = AccessoryPartner::all();
        return view('accessory.partner.input', compact('partner'));
    }

    function storePartner(Request $request)
    {
        try {

            $data = [
                'name' => $request->name,
                'userZone' => Auth::user()->userZone ?? null,
                'brand' => Auth::user()->brand ?? null,
                'branch' => Auth::user()->branch ?? null,
            ];

            AccessoryPartner::create($data);

            return response()->json([
                'success' => true,
                'message' => 'เพิ่มข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
        }
    }

    public function editPartner($id)
    {
        $partner = AccessoryPartner::findOrFail($id);
        return view('accessory.partner.edit', compact('partner'));
    }

    public function updatePartner(Request $request, $id)
    {
        try {
            $partner = AccessoryPartner::findOrFail($id);
            $data = $request->except(['_token', '_method']);
            $partner->update($data);

            return response()->json([
                'success' => true,
                'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
        }
    }

    function destroyPartner($id)
    {
        try {
            $partner = AccessoryPartner::findOrFail($id);
            $partner->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
        }
    }

    public function viewExportAccessory()
    {
        return view('accessory.report.view');
    }

    public function exportAccessoryPartner(Request $request)
    {
        $from = $request->from_date;
        $to   = $request->to_date;

        $rows = Saleaccessory::with(['saleCar.carOrder', 'accessory.partner'])
            ->whereHas('saleCar', function ($q) use ($from, $to) {
                $q->whereBetween('DeliveryDate', [$from, $to]);
            })
            ->get();

        $data = $rows->map(function ($r) {
            $customerName = trim(
                ($r->saleCar?->customer?->prefix?->Name_TH ?? '') . ' ' .
                    ($r->saleCar?->customer?->FirstName ?? '') . ' ' .
                    ($r->saleCar?->customer?->LastName ?? '')
            );

            $accName = $r->accessory?->detail ?? '-';
            $accID = $r->accessory?->accessory_id ?? '-';
            $accessoryNID = "{$accID}<br>{$accName}";

            return [
                'partner_id'   => $r->accessory?->accessoryPartner_id,
                'partner_name' => $r->accessory?->partner?->name ?? 0,

                'delivery_date' => $r->saleCar?->format_delivery_date ?? '-',
                'customer' => $customerName,
                'vin' => $r->saleCar?->carOrder?->vin_number ?? '-',
                'accessory_name' => $accessoryNID,
                'cost' => $r->accessory?->cost_spare ?? 0,
            ];
        });

        $fromFormatted = Carbon::parse($from)->format('d/m/Y');
        $toFormatted   = Carbon::parse($to)->format('d/m/Y');

        $grouped = $data->groupBy('partner_id');

        $pdf = Pdf::loadView('accessory.report.pdf', [
            'groups' => $grouped,
            'from' => $fromFormatted,
            'to'   => $toFormatted
        ]);

        return $pdf->stream('accessory-report.pdf');
    }
}
