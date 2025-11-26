<?php

namespace App\Http\Controllers\purchase_order;

use App\Http\Controllers\Controller;
use App\Models\TbCarmodel;
use App\Models\Accessory;
use App\Models\Accessorycost;
use App\Models\Accessorypromoprice;
use App\Models\Accessorysaleprice;
use App\Models\Accessorybuymore;
use App\Models\AccessoryPrice;
use App\Models\Campaign;
use App\Models\Campaigncar;
use App\Models\CarOrder;
use App\Models\CarOrderHistory;
use App\Models\Customer;
use App\Models\Finance;
use App\Models\PaymentType;
use App\Models\Salecampaign;
use App\Models\Salecar;
use App\Models\TbConStatus;
use App\Models\TbPrefixname;
use App\Models\TbProvinces;
use App\Models\TbSubcarmodel;
use App\Models\TurnCar;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        return view('purchase-order.input', compact('model'));
    }

    public function searchAccessory(Request $request)
    {
        $keyword = $request->get('keyword');
        $subModel_id = $request->get('subModel_id');
        $today = Carbon::today();

        $query = AccessoryPrice::query();

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('detail', 'LIKE', "%{$keyword}%")
                    ->orWhere('accessory_id', 'LIKE', "%{$keyword}%");;
            });
        }

        if ($subModel_id) {
            $query->where('subModel_id', $subModel_id);
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

        $query = Salecar::with('customer.prefix', 'conStatus');

        if ($statusFilter) {
            $query->whereHas('conStatus', function ($q) use ($statusFilter) {
                $q->where('name', $statusFilter);
            });
        }

        $saleCar = $query->get();

        $data = $saleCar->map(function ($s, $index) {
            $c = $s->customer;
            $subModelSale = $s->subModel ? $s->subModel->name : '';
            $statusSale = $s->conStatus ? $s->conStatus->name : '';

            return [
                'No' => $index + 1,
                'FullName' => $c->prefix->Name_TH . ' ' . $c->FirstName . ' ' . $c->LastName,
                'IDNumber' => $c->formatted_id_number,
                'Mobilephone' => $c->formatted_mobile,
                'subSale' => $subModelSale,
                'statusSale' => $statusSale,
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
                    'brand' => $request->brand,
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
                'model_id' => $request->model_id,
                'subModel_id' => $request->subModel_id,
                'CashDeposit' => $request->filled('CashDeposit')
                    ? str_replace(',', '', $request->CashDeposit)
                    : null,
                'Color' => $request->Color,
                'Year' => $request->Year,
                'option' => $request->option,
                'payment_mode' => $request->payment_mode,
                'CusID' => $request->CusID,
                'BookingDate' => $request->BookingDate,
                'TurnCarID' => $turnCarID,
                'con_status' => 1,
            ]);

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

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
            // return response()->json([
            //     'success' => false,
            //     'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            // ], 500);
        }
    }

    public function getSubModelPurchase($model_id)
    {
        $subModels = TbSubcarmodel::where('model_id', $model_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($subModels);
    }

    public function getCampaign(Request $request)
    {
        $subModel_id = $request->subModel_id;
        $today = Carbon::today();

        $campaigns = Campaign::where('subModel_id', $subModel_id)
            ->whereDate('startDate', '<=', $today)
            ->whereDate('endDate', '>=', $today)
            ->get();

        return response()->json($campaigns);
    }

    public function edit($id)
    {
        $saleCar = Salecar::with(['customer.prefix', 'customer.currentAddress', 'customer.documentAddress', 'customerReferrer.prefix', 'turnCar', 'accessories', 'model', 'carOrder', 'conStatus', 'provinces', 'remainingPayment.financeInfo', 'campaigns.campaign.type'])->findOrFail($id);
        $model = TbCarmodel::all();
        $finances = Finance::all();
        $subModels = TbSubcarmodel::where('model_id', $saleCar->model_id)->get();
        $conStatus = TbConStatus::all();
        $provinces = TbProvinces::all();

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
            $campaigns = Campaign::where('subModel_id', $subModel_id)
                ->where('active', 'active')
                ->whereDate('startDate', '<=', $today)
                ->whereDate('endDate', '>=', $today)
                ->get();
        }

        $selected_campaigns = $saleCar->campaigns->pluck('CampaignID')->toArray();

        return view('purchase-order.edit', compact('saleCar', 'model', 'subModels', 'campaigns', 'selected_campaigns', 'reservationPayment', 'remainingPayment', 'deliveryPayment', 'finances', 'conStatus', 'provinces'));
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

                $turnCar = TurnCar::find($turnCarID);

                $turnCar->update([
                    'brand' => $request->brand,
                    'model' => $request->model,
                    'machine' => $request->machine,
                    'year_turn' => $request->year_turn,
                    'color_turn' => $request->color_turn,
                    'license_plate' => $request->license_plate,
                    'cost_turn' => $request->filled('cost_turn')
                        ? str_replace(',', '', $request->cost_turn)
                        : null,
                    'com_turn'  => $request->filled('com_turn')
                        ? str_replace(',', '', $request->com_turn)
                        : null,
                ]);
                $turnCarID = $turnCar->id;
            }

            $data = [
                'SaleID' => $request->SaleID,
                'model_id' => $request->model_id,
                'subModel_id' => $request->subModel_id,
                'Color' => $request->Color,
                'CarOrderID' => $request->CarOrderID,
                'option' => $request->option,
                'CusID' => $request->CusID,
                'FinanceID' => $request->FinanceID,
                'SaleConsultantID' => $request->SaleConsultantID,
                'CashDeposit' => $request->filled('CashDeposit')
                    ? str_replace(',', '', $request->CashDeposit)
                    : null,
                'TurnCarID' => $turnCarID,
                'BookingDate' => $request->BookingDate,
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
                'CommissionDeduct' => $request->CommissionDeduct,
                'ApprovalSignature' => $request->ApprovalSignature,
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

            $oldCarOrderID = $saleCar->CarOrderID;
            $newCarOrderID = $request->CarOrderID;

            $saleCar->update($data);

            if ($request->con_status == 9) {
                if ($saleCar->CarOrderID) {
                    CarOrder::where('id', $saleCar->CarOrderID)
                        ->update(['car_status' => 'Null']);
                }
            }

            if ($oldCarOrderID != $newCarOrderID && $newCarOrderID) {
                CarOrderHistory::create([
                    'SaleID' => $saleCar->id,
                    'CarOrderID' => $newCarOrderID,
                    'BookingDate' => $request->BookingDate,
                ]);

                if ($oldCarOrderID) {
                    CarOrder::where('id', $oldCarOrderID)->update(['car_status' => 'Null']);
                }
                CarOrder::where('id', $newCarOrderID)->update(['car_status' => 'Book']);
            }

            if ($request->con_status == 5) {

                if ($newCarOrderID) {
                    CarOrder::where('id', $newCarOrderID)->update([
                        'car_status' => 'Send'
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
                        'CampaignName' => $campaign->name ?? '',
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
                        $data['tax_credit'] = $request->remaining_tax_credit ?? null;
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

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // return response()->json([
            //     'success' => false,
            //     'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            // ], 500);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    function destroy($id)
    {
        try {
            $saleCar = Salecar::findOrFail($id);

            if ($saleCar->CarOrderID) {
                CarOrder::where('id', $saleCar->CarOrderID)->update(['car_status' => 'Null']);
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
        $saleCar = Salecar::with(['customer.prefix', 'model', 'campaigns.campaign.type', 'reservationPayment', 'remainingPayment.financeInfo', 'deliveryPayment', 'turnCar', 'provinces'])->findOrFail($id);
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
                'FullName' => $c->prefix->Name_TH . ' ' . $c->FirstName . ' ' . $c->LastName,
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
        return view('purchase-order.booking-list.view', compact('saleCar'));
    }

    public function listBooking()
    {
        $saleCar = Salecar::with([
            'customer.prefix',
            'model',
            'subModel',
            'carOrder'
        ])
            ->orderBy('model_id')
            ->orderBy('subModel_id')
            ->orderBy('option')
            ->get();

        $data = $saleCar->map(function ($s, $index) {
            $c = $s->customer;
            $model = $s->model?->Name_TH ?? '-';
            $subModel = $s->subModel?->name ?? '-';
            $orderCode = $s->carOrder?->order_code ?? 'ไม่มีข้อมูลการผูกรถ';

            return [
                'No' => $index + 1,
                'model' => $model,
                'subModel' => $subModel,
                'option' => $s->option,
                'order' => $orderCode,
                'FullName' => $c->prefix->Name_TH . ' ' . $c->FirstName . ' ' . $c->LastName,
                'date' => $s->BookingDate,
            ];
        });

        return response()->json(['data' => $data]);
    }
}
