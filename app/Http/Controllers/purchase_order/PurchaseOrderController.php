<?php

namespace App\Http\Controllers\purchase_order;

use App\Exports\booking\BookingExport;
use App\Exports\commission\SaleCommissionExport;
use App\Exports\gp\GPExport;
use App\Exports\saleCar\SaleCarExport;
use App\Http\Controllers\Controller;
use App\Mail\SaleRequestMail;
use App\Models\TbCarmodel;
use App\Models\AccessoryPrice;
use App\Models\Campaign;
use App\Models\CarOrder;
use App\Models\CarOrderHistory;
use App\Models\Finance;
use App\Models\PaymentType;
use App\Models\Salecampaign;
use App\Models\Salecar;
use App\Models\SaleCarPayment;
use App\Models\TbConStatus;
use App\Models\TbInteriorColor;
use App\Models\TbProvinces;
use App\Models\TbSalecarType;
use App\Models\TbSalePurchaseType;
use App\Models\TbSubcarmodel;
use App\Models\TurnCar;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $saleCar = Salecar::all();
        $conStatus = TbConStatus::all();
        return view('purchase-order.view', compact('saleCar', 'conStatus'));
    }

    public function viewMore($id)
    {
        $saleCar = SaleCar::with([
            'customer.prefix',
            'model',
            'campaigns.campaign.campaignType',
            'accessories',
        ])->find($id);

        return view('purchase-order.view-more', compact('saleCar'));
    }

    public function create()
    {
        $model = TbCarmodel::all();
        $type = TbSalecarType::all();
        $typeSale = TbSalePurchaseType::all();
        $interiorColor = TbInteriorColor::all();
        return view('purchase-order.input', compact('model', 'type', 'typeSale', 'interiorColor'));
    }

    public function searchAccessory(Request $request)
    {
        $keyword = $request->get('keyword');
        $model_id = $request->get('model_id');
        $today = Carbon::today();

        $query = AccessoryPrice::query();

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('detail', 'LIKE', "%{$keyword}%")
                    ->orWhere('accessory_id', 'LIKE', "%{$keyword}%");;
            });
        }

        if ($model_id) {
            $query->where('model_id', $model_id);
        }

        $query->where('active', 'active');
        $query->where(function ($q) use ($today) {
            $q->where('startDate', '<=', $today)
                ->where(function ($q2) use ($today) {
                    $q2->whereNull('endDate')->orWhere('endDate', '>=', $today);
                });
        });

        if ($request->exclude_ids) {
            $exclude = is_array($request->exclude_ids) ? $request->exclude_ids : explode(',', $request->exclude_ids);
            $query->whereNotIn('id', $exclude);
        }

        $accessories = $query->latest('startDate')->get();

        $result = $accessories->map(function ($a) {
            return [
                'id' => $a->id,
                'AccessorySource' => $a->accessory_id,
                'AccessoryDetail' => $a->detail,
                'accessoryCost' => $a->cost ?? null,
                'AccessoryPromoPrice' => $a->promo ?? null,
                'AccessorySalePrice' => $a->sale ?? null,
                'AccessoryComSale' => $a->comSale ?? null,
            ];
        });

        return response()->json($result->values());
    }

    public function listPurchaseOrder(Request $request)
    {
        $statusFilter = $request->con_status;

        $query = Salecar::with('customer.prefix', 'conStatus')->whereNotIn('con_status', [5, 9]);

        if ($statusFilter) {
            $query->whereHas('conStatus', function ($q) use ($statusFilter) {
                $q->where('name', $statusFilter);
            });
        }

        $saleCar = $query->get();

        $data = $saleCar->map(function ($s, $index) {
            $c = $s->customer;
            $prefixText = $s->customer?->prefix?->Name_TH;
            $model = $s->model ? $s->model->Name_TH : '';
            $subModelSale = $s->subModel ? $s->subModel->name : '';
            $subDetail = $s->subModel ? $s->subModel->detail : '';
            $statusSale = $s->conStatus ? $s->conStatus->name : '';

            $subModelData = "{$subModelSale}<br>{$subDetail}";

            if (!empty($s->GMApprovalSignature)) {
                $approver = 'GM อนุมัติกรณีงบเกินแล้ว';
            } elseif (!empty($s->ApprovalSignature)) {
                $approver = 'ผู้จัดการ อนุมัติกรณีงบเกินแล้ว';
            } elseif (!empty($s->SMSignature)) {
                $approver = 'ผู้จัดการ อนุมัติแล้ว';
            } elseif (!empty($s->balanceCampaign)) {
                $approver = 'รออนุมัติ';
            } else {
                $approver = 'รอดำเนินการ';
            }

            //             if (!empty($s->GMApprovalSignature) && !empty($s->balanceCampaign)) {
            //     $approver = 'GM อนุมัติแล้ว';
            // } elseif (!empty($s->ApprovalSignature) && !empty($s->balanceCampaign)) {
            //     $approver = 'ผู้จัดการอนุมัติแล้ว';
            // } elseif (!empty($s->balanceCampaign)) {
            //     $approver = 'รออนุมัติ';
            // } else {
            //     $approver = 'รอดำเนินการ';
            // }

            return [
                'No' => $index + 1,
                'FullName' => implode(' ', array_filter([
                    $prefixText ?? null,
                    $c->FirstName ?? null,
                    $c->LastName ?? null,
                ])),
                'model' => $model,
                'subSale' => $subModelData,
                'order' => $s->carOrder?->order_code ?? 'ไม่มีข้อมูลการผูกรถ',
                'statusSale' => $statusSale,
                'approver' => $approver,
                'Action' => view('purchase-order.button', compact('s'))->render()
            ];
        });

        return response()->json(['data' => $data]);
    }

    function store(Request $request)
    {
        DB::beginTransaction();

        try {

            // $request->validate([
            //     'reservationCondition' => 'required',
            //     'hasTurnCar' => 'required',
            //     'reservation_cost' => 'required',
            //     'reservation_date' => 'required|date',
            //     'reservation_transfer_bank' => 'nullable|required_if:reservationCondition,transfer',
            //     'reservation_transfer_branch' => 'nullable|required_if:reservationCondition,transfer',
            //     'reservation_transfer_no' => 'nullable|required_if:reservationCondition,transfer',
            //     'reservation_check_bank' => 'nullable|required_if:reservationCondition,check',
            //     'reservation_check_branch' => 'nullable|required_if:reservationCondition,check',
            //     'reservation_check_no' => 'nullable|required_if:reservationCondition,check',
            //     'reservation_credit' => 'nullable|required_if:reservationCondition,credit',
            //     'reservation_tax_credit' => 'nullable|required_if:reservationCondition,credit',
            // ], [
            //     'hasTurnCar.required' => 'กรุณาเลือกประเภทรถเทิร์น',
            //     'reservationCondition.required' => 'กรุณาเลือกประเภทการจ่ายเงินจอง',
            //     'reservation_transfer_bank.required_if' => 'กรุณากรอกชื่อธนาคาร',
            //     'reservation_transfer_branch.required_if' => 'กรุณากรอกสาขาธนาคาร',
            //     'reservation_transfer_no.required_if' => 'กรุณากรอกเลขที่บัญชี',
            //     'reservation_check_bank.required_if' => 'กรุณากรอกชื่อธนาคาร',
            //     'reservation_check_branch.required_if' => 'กรุณากรอกสาขาธนาคาร',
            //     'reservation_check_no.required_if' => 'กรุณากรอกเลขที่เช็ค',
            //     'reservation_credit.required_if' => 'กรุณากรอกชื่อบัตรเครดิต',
            //     'reservation_tax_credit.required_if' => 'กรุณากรอกค่าธรรมเนียมบัตรเครดิต',
            // ]);

            $turnCarID = null;

            if ($request->hasTurnCar === 'yes') {
                $turnCar = TurnCar::create([
                    'brand_car' => $request->brand_car,
                    'model' => $request->model,
                    'machine' => $request->machine,
                    'year_turn' => $request->year_turn,
                    'color_turn' => $request->color_turn,
                    'license_plate' => $request->license_plate,
                    'cost_turn' => $request->filled('cost_turn')
                        ? str_replace(',', '', $request->cost_turn)
                        : null,
                    'com_turn' => $request->filled('com_turn')
                        ? str_replace(',', '', $request->com_turn)
                        : null,
                ]);

                $turnCarID = $turnCar->id;
            }

            $salecar = Salecar::create([
                'SaleID' => $request->SaleID,
                'type' => $request->type,
                'type_sale' => $request->type_sale,
                'model_id' => $request->model_id,
                'subModel_id' => $request->subModel_id,
                'price_sub' => $request->filled('price_sub')
                    ? str_replace(',', '', $request->price_sub)
                    : null,
                'CashDeposit' => $request->filled('CashDeposit')
                    ? str_replace(',', '', $request->CashDeposit)
                    : null,
                'Color' => $request->Color ?? null,
                'Year' => $request->Year,
                'option' => $request->option,
                'payment_mode' => $request->payment_mode,
                'CusID' => $request->CusID,
                'BookingDate' => $request->BookingDate,
                'TurnCarID' => $turnCarID,
                'con_status' => 1,
                'userZone' => Auth::user()->userZone ?? null,
                'brand' => Auth::user()->brand ?? null,
                'UserInsert' => Auth::id(),
            ]);

            if (Auth::user()->brand == 2) {
                $data['gwm_color'] = $request->gwm_color;
                $salecar['interior_color'] = $request->interior_color;
            }

            if ($request->filled('reservationCondition')) {
                $data = [
                    'saleCar_id' => $salecar->id,
                    'category' => 'reservation',
                    'type' => $request->reservationCondition,
                    'cost' => $request->filled('CashDeposit')
                        ? str_replace(',', '', $request->CashDeposit)
                        : null,
                    'date' => $request->reservation_date,
                    'userZone' => $request->userZone  ?? null,
                ];

                switch ($request->reservationCondition) {
                    case 'transfer':
                        $data['transfer_bank'] = $request->reservation_transfer_bank ?? null;
                        $data['transfer_branch'] = $request->reservation_transfer_branch ?? null;
                        $data['transfer_no'] = $request->reservation_transfer_no ?? null;

                        $data['check_bank'] = null;
                        $data['check_branch'] = null;
                        $data['check_no'] = null;
                        $data['credit'] = null;
                        $data['tax_credit'] = null;
                        break;

                    case 'check':
                        $data['check_bank'] = $request->reservation_check_bank ?? null;
                        $data['check_branch'] = $request->reservation_check_branch ?? null;
                        $data['check_no'] = $request->reservation_check_no ?? null;

                        $data['transfer_bank'] = null;
                        $data['transfer_branch'] = null;
                        $data['transfer_no'] = null;
                        $data['credit'] = null;
                        $data['tax_credit'] = null;
                        break;

                    case 'credit':
                        $data['credit'] = $request->reservation_credit ?? null;
                        $data['tax_credit'] = $request->reservation_tax_credit ? str_replace(',', '', $request->reservation_tax_credit) : null;

                        $data['transfer_bank'] = null;
                        $data['transfer_branch'] = null;
                        $data['transfer_no'] = null;
                        $data['check_bank'] = null;
                        $data['check_branch'] = null;
                        $data['check_no'] = null;
                        break;

                    case 'cash':
                    default:
                        $data['transfer_bank'] = null;
                        $data['transfer_branch'] = null;
                        $data['transfer_no'] = null;
                        $data['check_bank'] = null;
                        $data['check_branch'] = null;
                        $data['check_no'] = null;
                        $data['credit'] = null;
                        $data['tax_credit'] = null;
                        break;
                }

                PaymentType::create($data);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'เพิ่มข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // return response()->json([
            //     'success' => false,
            //     'message' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString(),
            // ], 500);
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
        }
    }

    public function getSubModelPurchase($model_id)
    {
        $subModels = TbSubcarmodel::where('model_id', $model_id)
            ->select('id', 'name', 'detail')
            ->orderBy('name')
            ->get();

        return response()->json($subModels);
    }

    //get color from sub model
    public function getColorBySubModel(Request $request)
    {
        $subModelId = $request->sub_model_id;

        $colors = TbSubcarmodel::with('colors')
            ->find($subModelId)
            ?->colors
            ->select('id', 'name');

        return response()->json($colors);
    }

    public function getCampaign(Request $request)
    {
        $subModel_id = $request->subModel_id;
        $year = (int) $request->year;
        $today = Carbon::today();

        if (!$subModel_id || !$year) {
            return response()->json([]);
        }

        $campaigns = Campaign::with('appellation', 'type')
            ->where('subModel_id', $request->subModel_id)
            ->where('startYear', '<=', $year)
            ->where('endYear', '>=', $year)
            ->whereDate('startDate', '<=', $today)
            ->whereDate('endDate', '>=', $today)
            ->get();

        return response()->json($campaigns);
    }

    public function edit($id)
    {
        $saleCar = Salecar::with(['customer.prefix', 'customer.currentAddress', 'customer.documentAddress', 'customerReferrer.prefix', 'turnCar', 'accessories', 'model', 'carOrder', 'conStatus', 'provinces', 'remainingPayment.financeInfo', 'campaigns.campaign.type', 'campaigns.campaign.appellation',])->findOrFail($id);
        $model = TbCarmodel::all();
        $finances = Finance::all();
        $subModels = TbSubcarmodel::where('model_id', $saleCar->model_id)->get();
        $conStatus = TbConStatus::all();
        $provinces = TbProvinces::all();
        $type = TbSalecarType::all();
        $typeSale = TbSalePurchaseType::all();
        $payments = SaleCarPayment::where('SaleID', $id)->get();
        $userRole = Auth::user()->role;
        $gwmColor = $saleCar->subModel
            ? $saleCar->subModel->colors
            : collect();
        $interiorColor = TbInteriorColor::all();

        //history
        $isHistory = $saleCar->con_status == 5;

        $subModel_id = $saleCar->subModel_id;

        $today = Carbon::today();

        $reservationPayment = PaymentType::where('saleCar_id', $id)
            ->where('category', 'reservation')
            ->first();

        $remainingPayment = PaymentType::where('saleCar_id', $id)
            ->where('category', 'remaining')
            ->first();

        $deliveryPayment = PaymentType::where('saleCar_id', $id)
            ->where('category', 'delivery')
            ->first();

        $campaigns = [];
        if ($subModel_id) {
            $campaigns = Campaign::with(['appellation', 'type'])
                ->where('subModel_id', $subModel_id)
                ->where('active', 'active')
                ->whereDate('startDate', '<=', $today)
                ->whereDate('endDate', '>=', $today)
                ->get();
        }

        $selected_campaigns = $saleCar->campaigns->pluck('CampaignID')->toArray();

        return view('purchase-order.edit', compact('saleCar', 'model', 'subModels', 'campaigns', 'selected_campaigns', 'reservationPayment', 'remainingPayment', 'deliveryPayment', 'finances', 'conStatus', 'provinces', 'type', 'typeSale', 'payments', 'userRole', 'isHistory', 'gwmColor', 'interiorColor'));
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            // $request->validate([
            //     //reservation
            //     'reservationCondition' => 'nullable|in:cash,transfer,check,credit,finance',
            //     'reservation_cost' => 'required',
            //     'reservation_date' => 'required|date',
            //     'reservation_transfer_bank' => 'nullable|required_if:reservationCondition,transfer',
            //     'reservation_transfer_branch' => 'nullable|required_if:reservationCondition,transfer',
            //     'reservation_transfer_no' => 'nullable|required_if:reservationCondition,transfer',
            //     'reservation_check_bank' => 'nullable|required_if:reservationCondition,check',
            //     'reservation_check_branch' => 'nullable|required_if:reservationCondition,check',
            //     'reservation_check_no' => 'nullable|required_if:reservationCondition,check',
            //     'reservation_credit' => 'nullable|required_if:reservationCondition,credit',
            //     'reservation_tax_credit' => 'nullable|required_if:reservationCondition,credit',

            //     //remaining
            //     'reservation_transfer_bank' => 'required_if:reservationCondition,transfer',
            //     'remaining_cost' => 'required',
            //     'remaining_date' => 'required|date',
            //     'remaining_transfer_bank' => 'nullable|required_if:remainingCondition,transfer',
            //     'remaining_transfer_branch' => 'nullable|required_if:remainingCondition,transfer',
            //     'remaining_transfer_no' => 'nullable|required_if:remainingCondition,transfer',
            //     'remaining_check_bank' => 'nullable|required_if:remainingCondition,check',
            //     'remaining_check_branch' => 'nullable|required_if:remainingCondition,check',
            //     'remaining_check_no' => 'nullable|required_if:remainingCondition,check',
            //     'remaining_credit' => 'nullable|required_if:remainingCondition,credit',
            //     'remaining_tax_credit' => 'nullable|required_if:remainingCondition,credit',

            //     //delivery
            //     'deliveryCondition' => 'nullable|in:cash,transfer,check,credit',
            //     'delivery_cost' => 'required',
            //     'delivery_date' => 'required|date',
            //     'delivery_transfer_bank' => 'sometimes|required_if:deliveryCondition,transfer',
            //     'delivery_transfer_branch' => 'sometimes|required_if:deliveryCondition,transfer',
            //     'delivery_transfer_no' => 'sometimes|required_if:deliveryCondition,transfer',
            //     'delivery_check_bank' => 'sometimes|required_if:deliveryCondition,check',
            //     'delivery_check_branch' => 'sometimes|required_if:deliveryCondition,check',
            //     'delivery_check_no' => 'sometimes|required_if:deliveryCondition,check',
            //     'delivery_credit' => 'sometimes|required_if:deliveryCondition,credit',
            //     'delivery_tax_credit' => 'sometimes|required_if:deliveryCondition,credit',
            // ], [
            //     //reservation
            //     'reservationCondition.required' => 'กรุณาเลือกประเภทการจ่ายเงินจอง',
            //     'reservation_transfer_bank.required_if' => 'กรุณากรอกชื่อธนาคาร',
            //     'reservation_transfer_branch.required_if' => 'กรุณากรอกสาขาธนาคาร',
            //     'reservation_transfer_no.required_if' => 'กรุณากรอกเลขที่บัญชี',
            //     'reservation_check_bank.required_if' => 'กรุณากรอกชื่อธนาคาร',
            //     'reservation_check_branch.required_if' => 'กรุณากรอกสาขาธนาคาร',
            //     'reservation_check_no.required_if' => 'กรุณากรอกเลขที่เช็ค',
            //     'reservation_credit.required_if' => 'กรุณากรอกชื่อบัตรเครดิต',
            //     'reservation_tax_credit.required_if' => 'กรุณากรอกค่าธรรมเนียมบัตรเครดิต',

            //     //remaining
            //     'remainingCondition.required' => 'กรุณาเลือกประเภทการจ่ายเงินจอง',
            //     'remaining_transfer_bank.required_if' => 'กรุณากรอกชื่อธนาคาร',
            //     'remaining_transfer_branch.required_if' => 'กรุณากรอกสาขาธนาคาร',
            //     'remaining_transfer_no.required_if' => 'กรุณากรอกเลขที่บัญชี',
            //     'remaining_check_bank.required_if' => 'กรุณากรอกชื่อธนาคาร',
            //     'remaining_check_branch.required_if' => 'กรุณากรอกสาขาธนาคาร',
            //     'remaining_check_no.required_if' => 'กรุณากรอกเลขที่เช็ค',
            //     'remaining_credit.required_if' => 'กรุณากรอกชื่อบัตรเครดิต',
            //     'remaining_tax_credit.required_if' => 'กรุณากรอกค่าธรรมเนียมบัตรเครดิต',

            //     //delivery
            //     'delivery_transfer_bank.required_if' => 'กรุณากรอกชื่อธนาคาร',
            //     'delivery_transfer_branch.required_if' => 'กรุณากรอกสาขาธนาคาร',
            //     'delivery_transfer_no.required_if' => 'กรุณากรอกเลขที่บัญชี',
            //     'delivery_check_bank.required_if' => 'กรุณากรอกชื่อธนาคาร',
            //     'delivery_check_branch.required_if' => 'กรุณากรอกสาขาธนาคาร',
            //     'delivery_check_no.required_if' => 'กรุณากรอกเลขที่เช็ค',
            //     'delivery_credit.required_if' => 'กรุณากรอกชื่อบัตรเครดิต',
            //     'delivery_tax_credit.required_if' => 'กรุณากรอกค่าธรรมเนียมบัตรเครดิต',
            // ]);


            $saleCar = Salecar::with('accessories')->findOrFail($id);

            $turnCarID = $saleCar->TurnCarID;

            if ($request->hasTurnCar === 'yes') {

                if (!$turnCarID) {
                    $turnCar = TurnCar::create([
                        'brand_car' => $request->brand_car,
                        'model' => $request->model,
                        'machine' => $request->machine,
                        'year_turn' => $request->year_turn,
                        'color_turn' => $request->color_turn,
                        'license_plate' => $request->license_plate,
                        'cost_turn' => $request->filled('cost_turn')
                            ? str_replace(',', '', $request->cost_turn)
                            : null,
                        'com_turn' => $request->filled('com_turn')
                            ? str_replace(',', '', $request->com_turn)
                            : null,
                    ]);

                    $turnCarID = $turnCar->id;
                } else {
                    $turnCar = TurnCar::findOrFail($turnCarID);
                    $turnCar->update([
                        'brand_car' => $request->brand_car,
                        'model' => $request->model,
                        'machine' => $request->machine,
                        'year_turn' => $request->year_turn,
                        'color_turn' => $request->color_turn,
                        'license_plate' => $request->license_plate,
                        'cost_turn' => $request->filled('cost_turn')
                            ? str_replace(',', '', $request->cost_turn)
                            : null,
                        'com_turn' => $request->filled('com_turn')
                            ? str_replace(',', '', $request->com_turn)
                            : null,
                    ]);
                }
            } else {
                $turnCarID = null;
            }


            $data = [
                'SaleID' => $request->SaleID,
                'type' => $request->type,
                'type_sale' => $request->type_sale,
                'model_id' => $request->model_id,
                'subModel_id' => $request->subModel_id,
                'price_sub' => $request->filled('price_sub')
                    ? str_replace(',', '', $request->price_sub)
                    : null,
                'Color' => $request->Color ?? null,
                'Year' => $request->Year,
                'CarOrderID' => $request->CarOrderID,
                'option' => $request->option,
                'payment_mode' => $request->payment_mode,
                'CusID' => $request->CusID,
                'FinanceID' => $request->FinanceID,
                'SaleConsultantID' => $request->SaleConsultantID,
                'CashDeposit' => $request->filled('CashDeposit')
                    ? str_replace(',', '', $request->CashDeposit)
                    : null,
                'TurnCarID' => $turnCarID,
                'BookingDate' => $request->BookingDate,
                'KeyInDate' => $request->KeyInDate,
                'DeliveryDate' => $request->DeliveryDate,
                'DeliveryInDMSDate' => $request->DeliveryInDMSDate,
                'DeliveryInCKDate' => $request->DeliveryInCKDate,
                'RegistrationProvince' => $request->RegistrationProvince,
                'RedPlateReceived' => $request->RedPlateReceived,
                'RedPlateAmount' => $request->RedPlateAmount,
                'CarSalePrice' => $request->filled('CarSalePrice')
                    ? str_replace(',', '', $request->CarSalePrice)
                    : null,
                'MarkupPrice' => $request->filled('MarkupPrice')
                    ? str_replace(',', '', $request->MarkupPrice)
                    : null,
                'Markup90' => $request->filled('Markup90')
                    ? str_replace(',', '', $request->Markup90)
                    : null,
                'CarSalePriceFinal' => $request->filled('CarSalePriceFinal')
                    ? str_replace(',', '', $request->CarSalePriceFinal)
                    : null,
                'discount' => $request->filled('discount')
                    ? str_replace(',', '', $request->discount)
                    : null,
                'DownPayment' => $request->filled('DownPayment')
                    ? str_replace(',', '', $request->DownPayment)
                    : null,
                'DownPaymentPercentage' => $request->filled('DownPaymentPercentage')
                    ? str_replace(',', '', $request->DownPaymentPercentage)
                    : null,
                'DownPaymentDiscount' => $request->filled('DownPaymentDiscount')
                    ? str_replace(',', '', $request->DownPaymentDiscount)
                    : null,
                'PaymentDiscount' => $request->filled('PaymentDiscount')
                    ? str_replace(',', '', $request->PaymentDiscount)
                    : null,
                'TradeinAddition' => $request->TradeinAddition,
                'AdditionFromCustomer' => $request->filled('AdditionFromCustomer')
                    ? str_replace(',', '', $request->AdditionFromCustomer)
                    : null,
                'TotalPaymentatDelivery' => $request->filled('TotalPaymentatDelivery')
                    ? str_replace(',', '', $request->TotalPaymentatDelivery)
                    : null,
                'ReferentPersonID' => $request->ReferentPersonID,
                'CashSupportFromMarkup' => $request->CashSupportFromMarkup,
                'TotalSaleCampaign' => $request->filled('TotalSaleCampaign')
                    ? str_replace(',', '', $request->TotalSaleCampaign)
                    : null,
                'balanceCampaign' => $request->filled('balanceCampaign')
                    ? str_replace(',', '', $request->balanceCampaign)
                    : null,
                'kickback' => $request->filled('kickback')
                    ? str_replace(',', '', $request->kickback)
                    : null,
                'other_cost' => $request->filled('other_cost')
                    ? str_replace(',', '', $request->other_cost)
                    : null,
                'other_cost_fi' => $request->filled('other_cost_fi')
                    ? str_replace(',', '', $request->other_cost_fi)
                    : null,
                'CashSupportInterestPlus' => $request->CashSupportInterestPlus,
                'TotalCashSupport' => $request->filled('TotalCashSupport')
                    ? str_replace(',', '', $request->TotalCashSupport)
                    : null,
                'TotalAccessoryGift' => $request->filled('TotalAccessoryGift')
                    ? str_replace(',', '', $request->TotalAccessoryGift)
                    : null,
                'AccessoryGiftCom' => $request->filled('AccessoryGiftCom')
                    ? str_replace(',', '', $request->AccessoryGiftCom)
                    : null,
                'TotalAccessoryExtra' => $request->filled('TotalAccessoryExtra')
                    ? str_replace(',', '', $request->TotalAccessoryExtra)
                    : null,
                'AccessoryExtraCom' => $request->filled('AccessoryExtraCom')
                    ? str_replace(',', '', $request->AccessoryExtraCom)
                    : null,
                'TotalCashSupportUsed' => $request->filled('TotalCashSupportUsed')
                    ? str_replace(',', '', $request->TotalCashSupportUsed)
                    : null,
                'RemainingCashSuuportShared' => $request->RemainingCashSuuportShared,
                'SCCommissionIntPlus' => $request->SCCommissionIntPlus,
                'TradeinComAmount' => $request->TradeinComAmount,
                'CommissionSale' => $request->filled('CommissionSale')
                    ? str_replace(',', '', $request->CommissionSale)
                    : null,
                'CommissionDeduct' => $request->filled('CommissionDeduct')
                    ? str_replace(',', '', $request->CommissionDeduct)
                    : null,
                'CommissionSpecial' => $request->filled('CommissionSpecial')
                    ? str_replace(',', '', $request->CommissionSpecial)
                    : null,
                'ApprovalSignature' => $request->ApprovalSignature,
                'ApprovalSignatureDate' => $request->ApprovalSignatureDate,
                'FinanceAmount' => $request->FinanceAmount,
                'InterestRate' => $request->InterestRate,
                'InterestCampaignID' => $request->InterestCampaignID,
                'InstallmentPeriod' => $request->InstallmentPeriod,
                'EXC_ALP' => $request->EXC_ALP,
                'INC_ALP' => $request->INC_ALP,
                'ALPAmount' => $request->ALPAmount,
                'SMSignature' => $request->SMSignature,
                'SMCheckedDate' => $request->SMCheckedDate,
                'AdminSignature' => $request->AdminSignature,
                'AdminCheckedDate' => $request->AdminCheckedDate,
                'CheckerID' => $request->CheckerID,
                'CheckerCheckedDate' => $request->CheckerCheckedDate,
                'GMApprovalSignature' => $request->GMApprovalSignature,
                'GMApprovalSignatureDate' => $request->GMApprovalSignatureDate,
                'DeliveryEstimateDate' => $request->DeliveryEstimateDate,
                'Note' => $request->Note,
                'ReferrerID' => $request->ReferrerID,
                'ReferrerAmount' => $request->filled('ReferrerAmount')
                    ? str_replace(',', '', $request->ReferrerAmount)
                    : null,
                'balance' => $request->filled('balance')
                    ? str_replace(',', '', $request->balance)
                    : null,
                'balanceFinance' => $request->filled('balanceFinance')
                    ? str_replace(',', '', $request->balanceFinance)
                    : null,
                'con_status' => $request->con_status,
            ];

            if (Auth::user()->brand == 2) {
                $data['gwm_color'] = $request->gwm_color;
                $data['interior_color'] = $request->interior_color;
            }

            $oldCarOrderID = $saleCar->CarOrderID;
            $newCarOrderID = $request->CarOrderID;

            $saleCar->update($data);

            if ($request->con_status == 9) {
                if ($saleCar->CarOrderID) {
                    CarOrder::where('id', $saleCar->CarOrderID)
                        ->update(['car_status' => 'Available']);
                }
            }

            if ($oldCarOrderID != $newCarOrderID && $newCarOrderID) {
                CarOrderHistory::create([
                    'SaleID' => $saleCar->id,
                    'CarOrderID' => $newCarOrderID,
                    'BookingDate' => $request->BookingDate,
                    'changed_at' => now(),
                    'userZone' => Auth::user()->userZone ?? null,
                    'brand' => Auth::user()->brand ?? null,
                ]);

                if ($oldCarOrderID) {
                    CarOrder::where('id', $oldCarOrderID)->update(['car_status' => 'Available']);
                }
                CarOrder::where('id', $newCarOrderID)->update(['car_status' => 'Booked']);
            }

            if ($request->con_status == 5) {

                if ($newCarOrderID) {
                    CarOrder::where('id', $newCarOrderID)->update([
                        'car_status' => 'Delivered'
                    ]);
                }
            }

            $saleCar->accessories()->detach();

            if ($request->has('accessories')) {
                $accessories = $request->input('accessories');
                if (is_string($accessories)) {
                    $accessories = json_decode($accessories, true);
                }

                if (is_array($accessories)) {
                    foreach ($accessories as $a) {
                        $price = isset($a['price']) ? floatval(str_replace(',', '', $a['price'])) : 0;
                        $commission = isset($a['commission']) ? floatval(str_replace(',', '', $a['commission'])) : 0;

                        $saleCar->accessories()->attach($a['id'], [
                            'price_type' => $a['price_type'],
                            'price' => $price,
                            'commission' => $commission,
                            'type' => $a['type'],
                        ]);
                    }
                }
            }

            Salecampaign::where('SaleID', $saleCar->id)->delete();

            // เพิ่มแคมเปญใหม่
            if ($request->has('CampaignID')) {
                foreach ($request->input('CampaignID') as $campId) {
                    $campaign = Campaign::find($campId);

                    Salecampaign::create([
                        'SaleID' => $saleCar->id,
                        'CampaignID' => $campId,
                        'CampaignName' => $campaign->camName_id ?? '',
                        'CampaignType' => $campaign->campaign_type ?? '',
                        'CashSupport' => $campaign->cashSupport ?? '',
                        'CashSupportDeduct' => $campaign->cashSupport_deduct ?? '',
                        'CashSupportFinal' => $campaign->cashSupport_final ?? '',
                    ]);
                }
            }

            if ($request->filled('reservationCondition')) {
                $data = [
                    'saleCar_id' => $saleCar->id,
                    'category' => 'reservation',
                    'type' => $request->reservationCondition,
                    'cost' => $request->filled('CashDeposit')
                        ? str_replace(',', '', $request->CashDeposit)
                        : null,
                    'date' => $request->reservation_date,
                    'userZone' => $request->userZone  ?? null,
                ];

                switch ($request->reservationCondition) {
                    case 'transfer':
                        $data['transfer_bank'] = $request->reservation_transfer_bank;
                        $data['transfer_branch'] = $request->reservation_transfer_branch;
                        $data['transfer_no'] = $request->reservation_transfer_no;

                        $data['check_bank'] = null;
                        $data['check_branch'] = null;
                        $data['check_no'] = null;
                        $data['credit'] = null;
                        $data['tax_credit'] = null;
                        break;

                    case 'check':
                        $data['check_bank'] = $request->reservation_check_bank;
                        $data['check_branch'] = $request->reservation_check_branch;
                        $data['check_no'] = $request->reservation_check_no;

                        $data['transfer_bank'] = null;
                        $data['transfer_branch'] = null;
                        $data['transfer_no'] = null;
                        $data['credit'] = null;
                        $data['tax_credit'] = null;
                        break;

                    case 'credit':
                        $data['credit'] = $request->reservation_credit;
                        $data['tax_credit'] = $request->reservation_tax_credit ? str_replace(',', '', $request->reservation_tax_credit) : null;

                        $data['transfer_bank'] = null;
                        $data['transfer_branch'] = null;
                        $data['transfer_no'] = null;
                        $data['check_bank'] = null;
                        $data['check_branch'] = null;
                        $data['check_no'] = null;
                        break;

                    case 'cash':
                    default:
                        $data['transfer_bank'] = null;
                        $data['transfer_branch'] = null;
                        $data['transfer_no'] = null;
                        $data['check_bank'] = null;
                        $data['check_branch'] = null;
                        $data['check_no'] = null;
                        $data['credit'] = null;
                        $data['tax_credit'] = null;
                        break;
                }

                PaymentType::updateOrCreate(
                    ['saleCar_id' => $saleCar->id, 'category' => 'reservation'],
                    $data
                );
            }

            if ($request->filled('payment_mode')) {

                if ($request->payment_mode === 'finance') {
                    $remainingType = 'finance';
                    $cost = $request->balanceFinance ?? null;
                } else {
                    $remainingType = $request->remainingCondition;
                    $cost = $request->balance ?? null;
                }

                $data = [
                    'saleCar_id' => $saleCar->id,
                    'payment_mode' => $request->payment_mode,
                    'category' => 'remaining',
                    'type' => $remainingType,
                    'cost' => $cost,
                    'date' => $request->remaining_date,
                    'userZone' => $request->userZone ?? null,
                ];

                $fieldsToClear = [
                    'transfer_bank',
                    'transfer_branch',
                    'transfer_no',
                    'check_bank',
                    'check_branch',
                    'check_no',
                    'credit',
                    'tax_credit',
                    'finance',
                    'interest',
                    'period',
                    'alp',
                    'including_alp',
                    'total_alp',
                    'type_com',
                    'total_com',
                    'po_number',
                    'po_date',
                    'contract_date'
                ];
                foreach ($fieldsToClear as $field) {
                    $data[$field] = null;
                }

                switch ($request->remainingCondition) {
                    case 'transfer':
                        $data['transfer_bank'] = $request->remaining_transfer_bank ?? null;
                        $data['transfer_branch'] = $request->remaining_transfer_branch ?? null;
                        $data['transfer_no'] = $request->remaining_transfer_no ?? null;
                        break;

                    case 'check':
                        $data['check_bank'] = $request->remaining_check_bank ?? null;
                        $data['check_branch'] = $request->remaining_check_branch ?? null;
                        $data['check_no'] = $request->remaining_check_no ?? null;
                        break;

                    case 'credit':
                        $data['credit'] = $request->remaining_credit ?? null;
                        $data['tax_credit'] = $request->remaining_tax_credit ? str_replace(',', '', $request->remaining_tax_credit) : null;
                        break;

                    case 'finance':
                        $data['finance'] = $request->remaining_finance ?? null;
                        $data['interest'] = $request->remaining_interest ?? null;
                        $data['period'] = $request->remaining_period ?? null;
                        $data['alp'] = $request->remaining_alp ? str_replace(',', '', $request->remaining_alp) : null;
                        $data['including_alp'] = $request->remaining_including_alp ? str_replace(',', '', $request->remaining_including_alp) : null;
                        $data['total_alp'] = $request->remaining_total_alp ? str_replace(',', '', $request->remaining_total_alp) : null;
                        $data['type_com'] = $request->remaining_type_com ?? null;
                        $data['total_com'] = $request->remaining_total_com ? str_replace(',', '', $request->remaining_total_com) : null;
                        $data['po_number'] = $request->remaining_po_number ?? null;
                        $data['po_date'] = $request->remaining_po_date ?? null;
                        $data['contract_date'] = $request->remaining_contract_date ?? null;
                        break;

                    case 'cash':
                    default:
                        break;
                }

                PaymentType::updateOrCreate(
                    ['saleCar_id' => $saleCar->id, 'category' => 'remaining'],
                    $data
                );
            }

            if ($request->filled('deliveryCondition')) {
                $data = [
                    'saleCar_id' => $saleCar->id,
                    'category' => 'delivery',
                    'type' => $request->deliveryCondition,
                    'cost' => $request->filled('delivery_cost')
                        ? str_replace(',', '', $request->delivery_cost)
                        : null,
                    'date' => $request->delivery_date,
                    'userZone' => $request->userZone  ?? null,
                ];

                switch ($request->deliveryCondition) {
                    case 'transfer':
                        $data['transfer_bank'] = $request->delivery_transfer_bank;
                        $data['transfer_branch'] = $request->delivery_transfer_branch;
                        $data['transfer_no'] = $request->delivery_transfer_no;

                        $data['check_bank'] = null;
                        $data['check_branch'] = null;
                        $data['check_no'] = null;
                        $data['credit'] = null;
                        $data['tax_credit'] = null;
                        break;

                    case 'check':
                        $data['check_bank'] = $request->delivery_check_bank;
                        $data['check_branch'] = $request->delivery_check_branch;
                        $data['check_no'] = $request->delivery_check_no;

                        $data['transfer_bank'] = null;
                        $data['transfer_branch'] = null;
                        $data['transfer_no'] = null;
                        $data['credit'] = null;
                        $data['tax_credit'] = null;
                        break;

                    case 'credit':
                        $data['credit'] = $request->delivery_credit;
                        $data['tax_credit'] = $request->delivery_tax_credit ? str_replace(',', '', $request->delivery_tax_credit) : null;

                        $data['transfer_bank'] = null;
                        $data['transfer_branch'] = null;
                        $data['transfer_no'] = null;
                        $data['check_bank'] = null;
                        $data['check_branch'] = null;
                        $data['check_no'] = null;
                        break;

                    case 'cash':
                    default:
                        $data['transfer_bank'] = null;
                        $data['transfer_branch'] = null;
                        $data['transfer_no'] = null;
                        $data['check_bank'] = null;
                        $data['check_branch'] = null;
                        $data['check_no'] = null;
                        $data['credit'] = null;
                        $data['tax_credit'] = null;
                        break;
                }

                PaymentType::updateOrCreate(
                    ['saleCar_id' => $saleCar->id, 'category' => 'delivery'],
                    $data
                );
            }

            // ลบรายการที่ user กดลบจริง
            if ($request->deletedPayments) {
                $deleteIds = explode(',', rtrim($request->deletedPayments, ','));
                SaleCarPayment::whereIn('id', $deleteIds)->delete();
            }


            if ($request->filled('payment_type')) {
                $ids = $request->payment_id ?? [];

                SaleCarPayment::where('SaleID', $saleCar->id)
                    ->whereNotIn('id', array_filter($ids))
                    ->delete();

                $types   = $request->payment_type;
                $costs = $request->payment_cost;
                $dates   = $request->payment_date;

                foreach ($types as $index => $type) {

                    if (!$type && !$costs[$index] && !$dates[$index]) {
                        continue;
                    }

                    $paymentId = $ids[$index] ?? null;

                    if ($paymentId) {
                        // UPDATE
                        SaleCarPayment::where('id', $paymentId)->update([
                            'type' => $type,
                            'cost' => $costs[$index] ? str_replace(',', '', $costs[$index]) : null,
                            'date' => $dates[$index] ?? null,
                        ]);
                    } else {
                        // CREATE
                        SaleCarPayment::create([
                            'SaleID' => $saleCar->id,
                            'type'   => $type,
                            'cost'   => $costs[$index] ? str_replace(',', '', $costs[$index]) : null,
                            'date'   => $dates[$index] ?? null,
                        ]);
                    }
                }
            }

            $action = $request->action_type;
            // Log::info('ACTION TYPE = ' . $request->action_type);

            if ($action === 'request_normal') {
                // Log::info('SENDING NORMAL MAIL');
                $saleCar->update([
                    'approval_type' => 'normal',
                    'approval_requested_at' => now(),
                ]);
                // ส่งเมลแบบยอดปกติ
                Mail::to('mitsuchookiat.programmer@gmail.com')
                    ->send(new SaleRequestMail($saleCar, 'normal'));
            }

            if ($action === 'request_over') {

                $saleCar->update([
                    'approval_type' => 'overbudget',
                    'approval_requested_at' => now(),
                    'reason_campaign' => $request->reason_campaign,
                ]);

                // ผู้จัดการ
                Mail::to('mitsuchookiat.programmer@gmail.com')
                    ->send(new SaleRequestMail($saleCar, 'manager'));
            }

            if ($action === 'request_gm') {

                $saleCar->update([
                    'approval_type' => 'overbudget',
                    'approval_requested_at' => now(),
                    'reason_campaign' => $request->reason_campaign,
                ]);

                // GM
                Mail::to('sutaphat.thongnui@gmail.com')
                    ->cc('mitsuchookiat.programmer@gmail.com')
                    ->send(new SaleRequestMail($saleCar, 'gm'));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
            // return response()->json([
            //     'success' => false,
            //     'message' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString(),
            // ], 500);
        }
    }

    //ยกเลิกการผูกรถ
    public function cancelCarOrder(Request $request, $id)
    {
        DB::transaction(function () use ($id, $request) {

            $sale = Salecar::findOrFail($id);

            // ป้องกันกรณีไม่มี car order
            if (!$sale->CarOrderID) {
                throw new \Exception('ไม่พบข้อมูลการผูกรถ');
            }

            $carOrder = CarOrder::findOrFail($sale->CarOrderID);

            $sale->CarOrderID = null;
            $sale->save();

            $carOrder->car_status = 'Available';
            $carOrder->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'ยกเลิกการผูกรถเรียบร้อยแล้ว'
        ]);
    }

    function destroy($id)
    {
        try {
            $saleCar = Salecar::findOrFail($id);

            if ($saleCar->CarOrderID) {
                CarOrder::where('id', $saleCar->CarOrderID)->update(['car_status' => 'Available']);
            }

            $saleCar->delete();

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

    public function summaryPurchase($id)
    {
        $saleCar = Salecar::with(['customer.prefix', 'model', 'carOrder', 'campaigns.campaign.type', 'campaigns.campaign.appellation', 'reservationPayment', 'remainingPayment.financeInfo', 'deliveryPayment', 'turnCar', 'provinces'])->findOrFail($id);
        $model = TbCarmodel::all();

        $pdf = Pdf::loadView('purchase-order.report.summary', compact('saleCar', 'model'))
            ->setPaper('A4', 'portrait');

        $filename = 'purchase-order_' . $saleCar->id . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->stream($filename);
    }

    public function preview($id)
    {
        $saleCar = Salecar::with(['customer.prefix', 'turnCar', 'accessories', 'model', 'carOrder', 'campaigns', 'remainingPayment.financeInfo'])->findOrFail($id);
        $model = TbCarmodel::all();
        $finances = Finance::all();
        $subModels = TbSubcarmodel::where('model_id', $saleCar->model_id)->get();

        $subModel_id = $saleCar->subModel_id;

        $today = Carbon::today();

        $reservationPayment = PaymentType::where('saleCar_id', $id)
            ->where('category', 'reservation')
            ->first();

        $remainingPayment = PaymentType::where('saleCar_id', $id)
            ->where('category', 'remaining')
            ->first();

        $deliveryPayment = PaymentType::where('saleCar_id', $id)
            ->where('category', 'delivery')
            ->first();

        return view('purchase-order.preview.preview', compact('saleCar', 'model', 'subModels', 'reservationPayment', 'remainingPayment', 'deliveryPayment', 'finances'));
    }

    public function viewPO()
    {
        $saleCar = Salecar::all();
        return view('purchase-order.po.view', compact('saleCar'));
    }

    public function listPO()
    {
        $saleCar = Salecar::with([
            'customer.prefix',
            'model',
            'subModel',
            'remainingPayment'
        ])
            ->where('payment_mode', 'finance')
            ->get();

        $data = $saleCar->map(function ($s, $index) {
            $c = $s->customer;
            $model = $s->model?->Name_TH ?? '-';
            $subModel = $s->subModel?->name ?? '-';
            $number = $s->remainingPayment?->po_number ?? '-';

            $daysRemaining = '-';
            if ($s->BookingDate) {
                $bookingDate = Carbon::parse($s->BookingDate);
                $overdueDays = (int) Carbon::now()->diffInDays($bookingDate->copy()->addDays(5), false);

                if ($overdueDays < 0) {
                    $daysRemaining = 'เกินกำหนด ' . abs($overdueDays) . ' วัน';
                } else {
                    $daysRemaining = $overdueDays . ' วัน';
                }
            }

            return [
                'No' => $index + 1,
                'FullName' => $c->prefix->Name_TH ?? '' . ' ' . $c->FirstName ?? '' . ' ' . $c->LastName ?? '',
                'model' => $model,
                'subModel' => $subModel,
                'po' => $number,
                'date' => $daysRemaining,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function viewBooking()
    {
        $saleCar = Salecar::all();
        $models = TbCarmodel::orderBy('Name_TH')->get();
        $statuses = TbConStatus::all();
        return view('purchase-order.booking-list.view', compact('saleCar', 'models', 'statuses'));
    }

    public function listBooking(Request $request)
    {
        $query = Salecar::with(['customer.prefix', 'model', 'subModel', 'carOrder', 'carOrderHistories'])
            ->when($request->model_id, fn($q) => $q->where('model_id', $request->model_id))
            ->when($request->sub_model_id, fn($q) => $q->where('subModel_id', $request->sub_model_id))
            ->whereNotIn('con_status', [5, 9]);

        if ($request->status_id) {
            $query->where('con_status', $request->status_id);
        }

        if ($request->booking_start) {
            $query->whereDate('BookingDate', '>=', $request->booking_start);
        }
        if ($request->booking_end) {
            $query->whereDate('BookingDate', '<=', $request->booking_end);
        }

        $saleCar = $query->orderBy('model_id')
            ->orderBy('subModel_id')
            ->orderBy('option')
            ->orderBy('BookingDate')
            ->get();

        $data = $saleCar->map(function ($s, $index) {
            $c = $s->customer;

            $changedAt = $s->carOrderHistories?->changed_at;
            $days = $changedAt
                ? Carbon::parse($changedAt)->startOfDay()->diffInDays(now()->startOfDay()) . ' วัน'
                : '-';


            return [
                'No' => $index + 1,
                'model' => $s->model?->Name_TH ?? '-',
                'subModel' => $s->subModel?->name ?? '-',
                'option' => $s->option,
                'order' => $s->carOrder?->order_code ?? 'ไม่มีข้อมูลการผูกรถ',
                'FullName' => $c->prefix->Name_TH ?? '' . ' ' . $c->FirstName ?? '' . ' ' . $c->LastName ?? '',
                'sale' => $s->saleUser->name ?? '-',
                'date' => $s->BookingDate,
                'status' => $s->conStatus?->name ?? '',
                'daysBind' => $days,
            ];
        });

        return response()->json(['data' => $data]);
    }

    // history
    public function history()
    {
        $saleCar = Salecar::all();
        return view('purchase-order.history.view', compact('saleCar'));
    }

    public function listHistory(Request $request)
    {
        $saleCar = Salecar::with([
            'customer.prefix',
            'carOrder'
        ])
            ->where('con_status', '5')
            ->get();

        $data = $saleCar->map(function ($s, $index) {
            $c = $s->customer;
            $prefixText = $s->customer?->prefix?->Name_TH;

            return [
                'No' => $index + 1,
                'FullName' => implode(' ', array_filter([
                    $prefixText ?? null,
                    $c->FirstName ?? null,
                    $c->LastName ?? null,
                ])),
                'code' => $s->carOrder->order_code ?? '-',
                'Action' => view('purchase-order.history.button', compact('s'))->render()
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function viewMoreHistory($id)
    {
        $saleCar = Salecar::with(['customer.prefix', 'customer.currentAddress', 'customer.documentAddress', 'customerReferrer.prefix', 'turnCar', 'accessories', 'model', 'carOrder', 'conStatus', 'provinces', 'remainingPayment.financeInfo', 'campaigns.campaign.type', 'campaigns.campaign.appellation', 'reservationPayment', 'remainingPayment', 'deliveryPayment'])->findOrFail($id);
        $campaignText = $saleCar->campaigns
            ->map(function ($saleCampaign) {
                return $saleCampaign->campaign?->appellation?->name;
            })
            ->filter() // ป้องกัน null
            ->join(' + ');

        return view('purchase-order.history.view-more-history', compact('saleCar', 'campaignText'));
    }

    public function exportBooking(Request $request)
    {
        return Excel::download(new BookingExport($request), 'booking.xlsx');
    }

    //search puschase
    public function search(Request $request)
    {
        $keyword = $request->input('keyword');

        $saleCars = Salecar::with([
            'customer.prefix',
            'model',
            'subModel'
        ])
            ->whereNull('CarOrderID')
            ->whereHas('customer', function ($q) use ($keyword) {
                $q->where('FirstName', 'like', "%{$keyword}%")
                    ->orWhere('LastName', 'like', "%{$keyword}%");
            })
            ->limit(10)
            ->get();

        return response()->json($saleCars);
    }

    //commission
    public function viewCommission()
    {
        return view('purchase-order.commission.view');
    }

    public function listCommission()
    {
        $month = $request->month ?? Carbon::now()->month;
        $year  = $request->year  ?? Carbon::now()->year;

        $saleCar = Salecar::with('saleUser.branchInfo')
            ->selectRaw('
            SaleID,
            COUNT(CarOrderID) as total_cars,
            SUM(CommissionSale) as total_commission
        ')
            ->whereNotNull('DeliveryInCKDate')
            ->whereNotNull('CarOrderID')
            ->whereMonth('DeliveryInCKDate', $month)
            ->whereYear('DeliveryInCKDate', $year)
            ->groupBy('SaleID')
            ->get();

        $data = $saleCar->map(function ($s, $index) {
            $nameSale = $s->saleUser->name;
            $branchSale = $s->saleUser->branchInfo->name;
            $sale = "{$nameSale}<br>(สาขา : {$branchSale})";

            return [
                'No' => $index + 1,
                'name' => $sale,
                'total_car' => $s->total_cars . ' คัน',
                'com' => number_format($s->total_commission ?? 0, 2),
            ];
        });

        return response()->json(['data' => $data]);
    }

    // report view com
    public function viewExportCommission()
    {
        return view('purchase-order.report.commission.view');
    }

    public function exportSaleCom(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->format('Y-m-d');
        $toDate   = $request->to_date   ?? now()->format('Y-m-d');

        return Excel::download(new SaleCommissionExport(Auth::user(), $fromDate, $toDate), 'sale-commission.xlsx');
    }

    // report gp
    public function viewExportGP()
    {
        return view('purchase-order.report.gp.view');
    }

    public function exportGP(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->format('Y-m-d');
        $toDate   = $request->to_date   ?? now()->format('Y-m-d');

        return Excel::download(new GPExport($fromDate, $toDate), 'gp-report.xlsx');
    }

    // report saleCar
    public function viewExportSaleCar()
    {
        return view('purchase-order.report.saleCar.view');
    }

    public function exportSaleCar(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->format('Y-m');
        $toDate   = $request->to_date   ?? now()->format('Y-m');

        return Excel::download(new SaleCarExport($fromDate, $toDate), 'ข้อมูลการจอง.xlsx');
    }
}
