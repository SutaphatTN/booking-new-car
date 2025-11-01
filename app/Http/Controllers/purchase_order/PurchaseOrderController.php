<?php

namespace App\Http\Controllers\purchase_order;

use App\Http\Controllers\Controller;
use App\Models\TbCarmodel;
use App\Models\Accessory;
use App\Models\Accessorycost;
use App\Models\Accessorypromoprice;
use App\Models\Accessorysaleprice;
use App\Models\Accessorybuymore;
use App\Models\Campaigncar;
use App\Models\Customer;
use App\Models\Salecampaign;
use App\Models\Salecar;
use App\Models\TbPrefixname;
use App\Models\TurnCar;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $saleCar = Salecar::all();
        return view('purchase-order.view', compact('saleCar'));
    }

    public function viewMore($id)
    {
        $saleCar = SaleCar::with([
            'customer.prefix',
            'carModel',
            'campaigns.campaign.campaignType',
            'accessories',
        ])->find($id);

        return view('purchase-order.view-more', compact('saleCar'));
    }

    public function create()
    {
        $carModel = TbCarmodel::all();
        return view('purchase-order.input', compact('carModel'));
    }

    public function show()
    {
        $carModel = TbCarmodel::all();

        return view('purchase-order.edit', compact('carModel'));
    }

    public function searchAccessory(Request $request)
    {
        $keyword = $request->get('keyword');
        $CarModelID = $request->get('CarModelID');
        $today = Carbon::today(); // วันที่ปัจจุบัน

        $query = Accessory::query();

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('AccessorySource', 'LIKE', "%{$keyword}%")
                    ->orWhere('AccessoryDetail', 'LIKE', "%{$keyword}%");
            });
        }

        if ($CarModelID) {
            $query->where('Car_ID', $CarModelID);
        }

        if ($request->exclude_ids) {
            $exclude = is_array($request->exclude_ids) ? $request->exclude_ids : explode(',', $request->exclude_ids);
            $query->whereNotIn('id', $exclude);
        }

        $accessories = $query->get();

        $result = $accessories->map(function ($a) use ($today) {

            // เช็คราคาทุน
            $cost = Accessorycost::where('accessoryID', $a->id)
                ->where(function ($q) use ($today) {
                    $q->where('StartDate', '<=', $today)
                        ->where(function ($q2) use ($today) {
                            $q2->whereNull('EndDate')->orWhere('EndDate', '>=', $today);
                        });
                })
                ->latest('StartDate')
                ->first();

            // เช็คราคาโปร
            $promo = Accessorypromoprice::where('accessoryID', $a->id)
                ->where(function ($q) use ($today) {
                    $q->where('StartDate', '<=', $today)
                        ->where(function ($q2) use ($today) {
                            $q2->whereNull('EndDate')->orWhere('EndDate', '>=', $today);
                        });
                })
                ->latest('StartDate')
                ->first();

            // เช็คราคาขาย
            $sale = Accessorysaleprice::where('accessoryID', $a->id)
                ->where(function ($q) use ($today) {
                    $q->where('StartDate', '<=', $today)
                        ->where(function ($q2) use ($today) {
                            $q2->whereNull('EndDate')->orWhere('EndDate', '>=', $today);
                        });
                })
                ->latest('StartDate')
                ->first();

            // ถ้าทั้ง 3 ตัวนี้ไม่มีราคาในช่วงวันที่ปัจจุบันเลย ก็ไม่ต้องแสดง
            if (!$cost && !$promo && !$sale) {
                return null;
            }

            return [
                'id' => $a->id,
                'AccessorySource' => $a->AccessorySource,
                'AccessoryDetail' => $a->AccessoryDetail,
                'accessoryCost' => $cost->accessoryCost ?? null,
                'AccessoryComCost' => $cost->AccessoryCom ?? 0,
                'AccessoryPromoPrice' => $promo->AccessoryPromoPrice ?? null,
                'AccessoryComPromo' => $promo->AccessoryCom ?? 0,
                'AccessorySalePrice' => $sale->AccessorySalePrice ?? null,
                'AccessoryComSale' => $sale->AccessoryCom ?? 0,
            ];
        })->filter(); // ตัด null ออก (accessory ที่ยังไม่เริ่มขายหรือหมดเขต)

        return response()->json($result->values());
    }

    public function listPurchaseOrder()
    {
        $saleCar = Salecar::with('customer.prefix')->get();

        $data = $saleCar->map(function ($s, $index) {
            $c = $s->customer;

            return [
                'No' => $index + 1,
                'FullName' => $c->prefix->Name_TH . ' ' . $c->FirstName . ' ' . $c->LastName,
                'IDNumber' => $c->formatted_id_number,
                'Mobilephone' => $c->formatted_mobile,
                'Action' => view('purchase-order.button', compact('s'))->render()
            ];
        });

        return response()->json(['data' => $data]);
    }

    function store(Request $request)
    {
        try {
            $turnCarID = null;

            if ($request->hasTurnCar === 'yes') {
                $turnCar = new TurnCar();
                $turnCar->brand = $request->brand;
                $turnCar->model = $request->model;
                $turnCar->machine = $request->machine;
                $turnCar->year = $request->year;
                $turnCar->color = $request->color;
                $turnCar->license_plate = $request->license_plate;
                $turnCar->save();

                $turnCarID = $turnCar->id;
            }

            $data = [
                'SaleID' => $request->SaleID,
                'CarModelID' => $request->CarModelID,
                'Color' => $request->Color,
                'CusID' => $request->CusID,
                'FinanceID' => $request->FinanceID,
                'SaleConsultantID' => $request->SaleConsultantID,
                'CashDeposit' => str_replace(',', '', $request->CashDeposit ?: 0),
                'TurnCarID' => $turnCarID,
            ];

            Salecar::create($data);

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

    public function getCampaign(Request $request)
    {
        $carModelID = $request->CarModelID;
        $today = Carbon::today();

        $campaigns = CampaignCar::where('CarID', $carModelID)
            ->whereDate('StartDate', '<=', $today)
            ->whereDate('EndDate', '>=', $today)
            ->get();

        return response()->json($campaigns);
    }

    public function edit($id)
    {
        $saleCar = Salecar::with(['customer.prefix', 'turnCar', 'accessories', 'carModel'])->findOrFail($id);
        $carModel = TbCarmodel::all();

        $carModelID = $saleCar->CarModelID;

        $today = Carbon::today();

        $campaigns = [];
        if ($carModelID) {
            $campaigns = CampaignCar::with('campaignType')
                ->where('CarID', $carModelID)
                ->whereDate('StartDate', '<=', $today)
                ->whereDate('EndDate', '>=', $today)
                ->get();
        }

        $selected_campaigns = $saleCar->campaigns->pluck('CampaignID')->toArray();

        return view('purchase-order.edit', compact('saleCar', 'carModel', 'campaigns', 'selected_campaigns'));
    }

    public function update(Request $request, $id)
    {
        try {
            $saleCar = Salecar::with('accessories')->findOrFail($id);

            // 🔹 อัปเดตข้อมูลรถเทิร์น (TurnCar)
            $turnCarID = $saleCar->TurnCarID;

            if ($request->hasTurnCar === 'yes') {

                $turnCar = TurnCar::find($turnCarID);

                $turnCar->update([
                    'brand' => $request->brand,
                    'model' => $request->model,
                    'machine' => $request->machine,
                    'year' => $request->year,
                    'color' => $request->color,
                    'license_plate' => $request->license_plate,
                ]);
                $turnCarID = $turnCar->id;
            }

            $data = [
                'SaleID' => $request->SaleID,
                'CarModelID' => $request->CarModelID,
                'Color' => $request->Color,
                'CusID' => $request->CusID,
                'FinanceID' => $request->FinanceID,
                'SaleConsultantID' => $request->SaleConsultantID,
                'CashDeposit' => str_replace(',', '', $request->CashDeposit ?: 0),
                'TurnCarID' => $turnCarID,
                'BookingDate' => $request->BookingDate,
                'DeliveryDate' => $request->DeliveryDate,
                'DeliveryInDMSDate' => $request->DeliveryInDMSDate,
                'DeliveryInCKDate' => $request->DeliveryInCKDate,
                'RegistrationProvince' => $request->RegistrationProvince,
                'RedPlateReceived' => $request->RedPlateReceived,
                'RedPlateAmount' => $request->RedPlateAmount,
                'CarSalePrice' => str_replace(',', '', $request->CarSalePrice ?: 0),
                'MarkupPrice' => str_replace(',', '', $request->MarkupPrice ?: 0),
                'Markup90' => str_replace(',', '', $request->Markup90 ?: 0),
                'CarSalePriceFinal' => str_replace(',', '', $request->CarSalePriceFinal ?: 0),
                'DownPayment' => str_replace(',', '', $request->DownPayment ?: 0),
                'DownPaymentPercentage' => $request->DownPaymentPercentage,
                'DownPaymentDiscount' => str_replace(',', '', $request->DownPaymentDiscount ?: 0),
                'CashDeposit' => $request->CashDeposit,
                'TradeinAddition' => $request->TradeinAddition,
                'AdditionFromCustomer' => str_replace(',', '', $request->AdditionFromCustomer ?: 0),
                'TotalPaymentatDelivery' => str_replace(',', '', $request->TotalPaymentatDelivery ?: 0),
                'ReferentPersonID' => $request->ReferentPersonID,
                'CashSupportFromMarkup' => $request->CashSupportFromMarkup,
                'TotalSaleCampaign' => str_replace(',', '', $request->TotalSaleCampaign ?: 0),
                'CashSupportInterestPlus' => $request->CashSupportInterestPlus,
                'TotalCashSupport' => str_replace(',', '', $request->TotalCashSupport ?: 0),
                'TotalAccessoryGift' => str_replace(',', '', $request->TotalAccessoryGift ?: 0),
                'TotalAccessoryExtra' => str_replace(',', '', $request->TotalAccessoryExtra ?: 0),
                'TotalCashSupportUsed' => str_replace(',', '', $request->TotalCashSupportUsed ?: 0),
                'RemainingCashSuuportShared' => $request->RemainingCashSuuportShared,
                'SCCommissionIntPlus' => $request->SCCommissionIntPlus,
                'AccessoryComAmount' => $request->AccessoryComAmount,
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
                'Note' => $request->Note,
            ];

            $saleCar->update($data);

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

            Salecampaign::where('SaleID', $saleCar->SaleID)->delete();

            // เพิ่มแคมเปญใหม่
            if ($request->has('CampaignID')) {
                foreach ($request->input('CampaignID') as $campId) {
                    $campaign = CampaignCar::find($campId);

                    Salecampaign::create([
                        'SaleID' => $saleCar->SaleID,
                        'CampaignID' => $campId,
                        'CampaignType' => $campaign->SubCampaignType ?? '',
                        'CashSupport' => $campaign->CashSupport ?? 0,
                        'CashSupportDeduct' => $campaign->CashSupportDeduct ?? 0,
                        'CashSupportFinal' => $campaign->CashSupportFinal ?? 0,
                    ]);
                }
            }

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
            $saleCar = Salecar::findOrFail($id);
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
        $saleCar = Salecar::with(['customer.prefix', 'carModel', 'campaigns'])->findOrFail($id);
        $carModel = TbCarmodel::all();

        $pdf = Pdf::loadView('purchase-order.report.summary', compact('saleCar', 'carModel'))
            ->setPaper('A4', 'portrait');

        $filename = 'purchase-order_' . $saleCar->id . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->stream($filename);
    }
}
