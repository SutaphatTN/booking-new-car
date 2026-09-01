<?php

namespace App\Http\Controllers\purchase_order;

use App\Traits\ConvertsThaiDate;
use App\Exports\booking\BookingExport;
use App\Exports\commission\SaleCommissionExport;
use App\Exports\gp\GPExport;
use App\Exports\insurance\InsuranceExport;
use App\Exports\lead_online\LeadOnlineAllocationExport;
use App\Exports\over_budget\OverBudgetExport;
use App\Exports\gwm\GwmExport;
use App\Exports\saleCar\estimated\EstimatedExport;
use App\Exports\saleCar\estimated\SaleCarEstimatedExport;
use App\Exports\saleCar\SaleCarBookingExport;
use App\Exports\saleCar\MonthlyDeliveryExport;
use App\Http\Controllers\Controller;
use App\Mail\SaleRequestMail;
use App\Models\Address;
use App\Models\TbCarmodel;
use App\Models\AccessoryPrice;
use App\Models\Saleaccessory;
use App\Models\Campaign;
use App\Models\CarOrder;
use App\Models\CarOrderHistory;
use App\Models\Customer;
use App\Models\Finance;
use App\Models\FinancesConfirm;
use App\Models\Insurance;
use App\Models\LicensePlateHistory;
use App\Models\PaymentType;
use App\Models\Salecampaign;
use App\Models\Salecar;
use App\Models\SaleCarPayment;
use App\Models\SaleCommissionMonthly;
use App\Models\MonthlySaleTarget;
use App\Models\CustomerTracking;
use App\Models\TbConStatus;
use App\Models\TbInteriorColor;
use App\Models\TbLicensePlate;
use App\Models\TbPrefixname;
use App\Models\TbProvinces;
use App\Models\TbSalecarType;
use App\Models\TbSalePurchaseType;
use App\Models\TbPricelistCar;
use App\Models\TbSubcarmodel;
use App\Models\TurnCar;
use App\Models\User;
use App\Models\TbBranch;
use App\Services\GPQuery;
use App\Services\SaleCommissionQuery;
use App\Services\SsiCommissionQuery;
use App\Services\CarCommissionQuery;
use App\Services\HeldCommissionQuery;
use App\Services\ExtraBudgetLedger;
use App\Services\BudgetWallet;
use App\Services\OneDriveService;
use App\Services\ApprovalSummary;
use App\Support\ScopeBypass;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Mail\Mailables\Attachment;
use App\Mail\SaleApprovedMail;
use App\Mail\CarDeliveredMail;
use App\Mail\ApprovalReturnMail;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Support\ExportFilename;
use App\Support\BrandFeature;

class PurchaseOrderController extends Controller
{
    use ConvertsThaiDate;

    /**
     * ประเภทราคาประดับยนต์แบบ "ระบุเอง" — ใช้ตอนของชิ้นเดียวกันราคาไม่เท่ากันในแต่ละดีล
     * (เช่น แถมน้ำมัน) จะได้ไม่ต้องสร้างแถวใหม่ใน master ทุกครั้ง
     * ค่าที่เก็บใน saleaccessory.price_type เป็นข้อความไทยมาตั้งแต่ต้น จึงคงรูปแบบเดิมไว้
     */
    public const ACC_PRICE_TYPE_CUSTOM = 'ระบุเอง';

    /**
     * สวิตช์ปิดฟีเจอร์ "ระบุราคาประดับยนต์เอง" ทั้งระบบ (ปิดเมื่อ 2026-08-29)
     *
     * เหตุผล: เจ้าของกิจการกังวลว่าการให้กรอกราคาเองตอนทำใบจองเปิดช่องทุจริต
     * จึงกลับไปใช้วิธีเดิม คือสร้างรายการใหม่ใน master เมื่อราคาต่างกัน (เพิ่มรหัส/ชื่อซ้ำได้แล้ว)
     *
     * ปิดแทนการลบโค้ด เพราะยังมีใบจองเก่าที่บันทึกด้วยวิธีนี้ไว้ (43 รายการ ณ วันที่ปิด) และอาจกลับมาเปิดใช้อีก
     * ใบเก่ายังเปิด/เซฟต่อได้ตราบใดที่ไม่แก้ตัวเลข (เงื่อนไข $unchanged ตอนบันทึกประดับยนต์)
     *
     * คอลัมน์ "ระบุเอง" ในโมดัลเลือกประดับยนต์หายไปเองตามค่านี้
     * (gift.blade.php + purchase-order.js อ่านผ่าน canSetCustomAccessoryPrice ทั้งหัวตารางและช่องกรอก)
     *
     * เปิดคืน: เปลี่ยนเป็น true + เอา @if(false) ออกที่
     *   - resources/views/accessory/input.blade.php  (สวิตช์ "ราคาไม่คงที่")
     *   - resources/views/accessory/edit.blade.php   (สวิตช์ "ราคาไม่คงที่")
     *   - app/Http/Controllers/accessory/AccessoryController.php (badge ในหน้ารายการ — เป็นคอมเมนต์ //)
     */
    public const ACC_CUSTOM_PRICE_ENABLED = false;

    /**
     * ระบุราคาประดับยนต์เองได้ไหม — sale/lead_sale ห้าม
     * เพราะ cost_spare คือยอด "ของแถม" ที่ใช้คำนวณงบขออนุมัติและ GP
     * ตอนนี้ปิดทั้งระบบด้วย ACC_CUSTOM_PRICE_ENABLED (ดูเหตุผลด้านบน)
     */
    public static function canSetCustomAccessoryPrice(): bool
    {
        if (!self::ACC_CUSTOM_PRICE_ENABLED) {
            return false;
        }

        $role = Auth::user()->role ?? null;

        return $role !== null && !in_array($role, ['sale', 'lead_sale'], true);
    }

    /**
     * ถอนจอง (ปุ่มลบในหน้ารายการจอง) ได้ไหม — sale/lead_sale ห้าม
     * ถอนจองคือการปิดใบจอง (con_status = 9) + ปลดรถกลับเป็น Available
     * ฝ่ายขายกดเองไม่ได้ ต้องให้ระดับที่ดูแลใบจองเป็นคนตัดสิน
     */
    public static function canWithdrawBooking(): bool
    {
        $role = Auth::user()->role ?? null;

        return $role !== null && !in_array($role, ['sale', 'lead_sale'], true);
    }

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

    public function create(Request $request)
    {
        $authUser = Auth::user();

        $model = TbCarmodel::all();
        $type = TbSalecarType::all();
        $saleBrands = config("brand.sale_pool.{$authUser->brand}", [$authUser->brand]);
        $extraSaleIds = User::extraSaleUserIdsForBrand((int) $authUser->brand);
        $saleUser = User::whereIn('role', ['sale', 'lead_sale'])
            ->where(function ($q) use ($saleBrands, $extraSaleIds) {
                $q->whereIn('brand', $saleBrands)
                    ->orWhereIn('id', $extraSaleIds);
            })
            ->get();
        $typeSale = TbSalePurchaseType::all();
        $interiorColor = TbInteriorColor::all();

        $prefill = null;
        if ($request->filled('from_tracking')) {
            $tracking = CustomerTracking::with([
                'customer.prefix',
                'sale',
                'subModel',
            ])->find($request->from_tracking);

            // lead จากเพจที่เซลล์ยังไม่ได้บันทึกตอบกลับ ห้ามข้ามไปทำใบจอง
            // (กันเปิด URL ตรง ๆ — หน้ารายละเอียดการติดตามปิดปุ่มไว้อยู่แล้ว)
            // ยกเว้นขออนุมัติล่วงหน้า ซึ่งยังไม่ใช่การจอง
            if ($tracking && !$request->boolean('pre_approval') && $tracking->awaitingSaleReply()) {
                return redirect()
                    ->route('customer-tracking.show', $tracking->id)
                    ->with('error', 'ยังสร้างการจองไม่ได้ — เซลล์ยังไม่ได้บันทึกตอบกลับลูกค้ารายนี้ กรุณาเพิ่มบันทึกเซลล์ที่แท็บ "ประวัติการติดตาม" ก่อน');
            }

            if ($tracking && $tracking->customer) {
                $c = $tracking->customer;
                $prefill = [
                    'tracking_id'        => $tracking->id,
                    'customer_id'        => $c->id,
                    'customer_name'      => trim(($c->prefix->Name_TH ?? '') . ' ' . $c->FirstName . ' ' . $c->LastName),
                    'customer_id_number' => $c->formatted_id_number ?? $c->IDNumber,
                    'customer_phone'     => $c->formatted_mobile ?? $c->Mobilephone1,
                    'sale_id'            => $tracking->sale_id,
                    'source_id'          => $tracking->source_id,
                    'model_id'           => $tracking->model_id,
                    'sub_model_id'       => $tracking->sub_model_id,
                    'year'               => $tracking->year,
                    'pricelist_color'    => $tracking->pricelist_color,
                    'option'             => $tracking->option,
                    'color_id'           => $tracking->color_id,
                    'interior_color_id'  => $tracking->interior_color_id,
                    'color_text'         => $tracking->color_text,
                ];
            }
        }

        $prefixes = TbPrefixname::all();

        // สร้างจากโมดูล "ขออนุมัติเกินงบล่วงหน้า" (?pre_approval=1) → ยังไม่เป็นการจอง
        $isPreApproval = $request->boolean('pre_approval');

        return view('purchase-order.input', compact('model', 'type', 'typeSale', 'interiorColor', 'saleUser', 'prefill', 'prefixes', 'isPreApproval'));
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
                'is_standard' => (bool) $a->is_standard,
                // เปิดช่อง "ระบุเอง" ให้เฉพาะรายการที่ราคาไม่คงที่ — ตอนนี้ปิดทั้งระบบ (ดู ACC_CUSTOM_PRICE_ENABLED)
                'allow_custom_price' => self::ACC_CUSTOM_PRICE_ENABLED && (bool) $a->allow_custom_price,
                'is_zero_cost' => (bool) $a->is_zero_cost, // ยืนยันแล้วว่าทุนอะไหล่ = 0 จริง → เลือกได้ทั้งที่ cost_spare เป็น 0
                'cost_spare' => $a->cost_spare ?? null, // ราคาทุนอะไหล่ — ต้องมีถึงเลือกได้ (ใช้ตอนขออนุมัติ)
                'require_note' => $a->requiresNote(), // ฟิล์ม — ต้องระบุรายละเอียด (ความเข้ม/ตำแหน่งที่ติด)
            ];
        });

        return response()->json($result->values());
    }

    // ประกอบข้อมูลสำหรับใบขออนุมัติ (เมล + เก็บยอดที่เหลือ)
    // สูตรอยู่ที่ App\Services\ApprovalSummary — รายงานเกินงบใช้ชุดเดียวกัน
    private function buildApprovalData(Salecar $saleCar)
    {
        // รีเฟรช relations ที่เพิ่งถูก sync ในรีเควสต์เดียวกัน (accessories/campaigns) กัน cache เก่า
        $saleCar->load(ApprovalSummary::RELATIONS);

        return ApprovalSummary::build($saleCar);
    }

    /**
     * "กระเป๋าของเซลล์" — งบที่ได้ทั้งก้อน เทียบกับทุกอย่างที่ถูกใช้ไป
     *
     * ⚠ ยังไม่ได้ใช้งาน — การแนบไฟล์นี้ใน buildApprovalAttachments() ถูก comment ไว้
     *   เก็บเมธอดกับ view (purchase-order/report/sale-pocket) ไว้รอเปิดใช้ทีหลัง
     *
     *  งบที่ได้      = ยอดแคมเปญทั้งหมด (TotalSaleCampaign) + บวกหัว 90% + Kick Back
     *  รายการที่ใช้ไป = ส่วนลดราคารถ + ส่วนลดเงินดาวน์ + เงินจอง + ของแถม(ราคาทุนอะไหล่) + Vat ของแถม
     *                  (ค่างวดล่วงหน้าไม่นับ — ไม่ได้ออกจากกระเป๋าเซลล์)
     *  คงเหลือ       = งบที่ได้ − รายการที่ใช้ไป
     *
     * หมายเหตุ: ของแถมใช้ "ราคาทุนอะไหล่" (cost_spare) ชุดเดียวกับที่ลิสต์ในเมล ซึ่งเป็นคนละฐานกับ
     * salecars.TotalAccessoryGift (ราคาที่ใช้ = pivot.price) ที่หน้าใบจองใช้คิด balanceCampaign
     * → ยอดคงเหลือหน้านี้จึงไม่เท่ากับ "เหลืองบ/เกินงบ" ในไฟล์สรุปการขาย เป็นคนละมุมโดยตั้งใจ
     */
    private function buildSalePocketData(Salecar $saleCar): array
    {
        $saleCar->loadMissing(['accessories']);

        $isFinance = $saleCar->payment_mode === 'finance';

        // ── งบที่ได้ ──
        $campaignTotal = (float) ($saleCar->TotalSaleCampaign ?? 0);
        $markup90      = $isFinance ? (float) ($saleCar->Markup90 ?? 0) : 0.0;
        $kickback      = (float) ($saleCar->kickback ?? 0);
        $budgetTotal   = $campaignTotal + $markup90 + $kickback;

        // ── รายการที่ใช้ไป ──
        // ส่วนลดราคารถ : ไฟแนนซ์ใช้ discount / เงินสดใช้ PaymentDiscount (เหมือน buildApprovalData)
        $carDiscount = $isFinance
            ? (float) ($saleCar->discount ?? 0)
            : (float) ($saleCar->PaymentDiscount ?? 0);

        // ส่วนลดเงินดาวน์ / Vat ของแถม : มีเฉพาะเคสจัดไฟแนนซ์
        $downPaymentDiscount = $isFinance ? (float) ($saleCar->DownPaymentDiscount ?? 0) : 0.0;
        $accessoryGiftVat    = $isFinance ? (float) ($saleCar->AccessoryGiftVat ?? 0) : 0.0;

        // เงินจอง — ใช้ salecars.CashDeposit (ช่องที่กรอกในใบจอง) ครบกว่า payment_type ประเภท reservation
        $cashDeposit = (float) ($saleCar->CashDeposit ?? 0);

        // ของแถม = ราคาทุนอะไหล่ (snapshot ในใบขาย) + รายละเอียดรายชิ้น เหมือนที่แสดงในเมล
        $giftAccessories = $saleCar->accessories->where('pivot.type', 'gift');
        $giftTotal   = (float) $giftAccessories->sum(fn($a) => $a->usedCostSpare());
        $giftDetails = $giftAccessories->map(fn($a) => [
            'detail' => $a->detail,
            'note'   => $a->pivot->note, // ฟิล์ม: ความเข้ม/ตำแหน่งที่ติด
            'amount' => $a->usedCostSpare(),
        ])->values();

        $usedTotal = $carDiscount + $downPaymentDiscount + $cashDeposit + $giftTotal + $accessoryGiftVat;

        return [
            'is_finance'            => $isFinance,
            'campaign_total'        => $campaignTotal,
            'markup90'              => $markup90,
            'kickback'              => $kickback,
            'budget_total'          => $budgetTotal,
            'car_discount'          => $carDiscount,
            'down_payment_discount' => $downPaymentDiscount,
            'cash_deposit'          => $cashDeposit,
            'gift_total'            => $giftTotal,
            'gift_details'          => $giftDetails,
            'accessory_gift_vat'    => $accessoryGiftVat,
            'used_total'            => $usedTotal,
            'remaining'             => $budgetTotal - $usedTotal,
        ];
    }

    // คำนวณ com finance ตามสูตรหน้า FN (finance.js: calculateComFin)
    private function calcComFinance(Salecar $saleCar)
    {
        return ApprovalSummary::comFinance($saleCar);
    }

    // เคสอนุมัติ (brand-aware) — ทุกแบรนด์เริ่มที่ "ผู้จัดการ" เหมือนกันหมด:
    //  normal     = งบปกติ → manager (จบ)
    //  b1_manager = brand1/3 เกิน ≤ over_budget → manager (จบ)
    //  b1_md      = brand1/3 เกิน > over_budget → manager กรอกยอดที่ต้องหัก → GM อนุมัติจบ (CC ให้ md)
    //  b2_gm      = brand2/4 เกินงบ (ไม่มีเพดาน) → manager กรอกยอดที่ต้องหัก → GM อนุมัติจบ (CC ให้ md)
    //  ยอดที่กรอก ระบบเติมค่าตั้งต้นให้ = เกินงบยอดเต็ม × 10% (Salecar::suggestedCommissionDeduct) แก้เพิ่มได้
    //               brand 2 เลือก "ไม่หักเงิน VIP" ได้ → ส่ง MD อนุมัติจบแทน (CC ให้ gm)
    //  brand 3 ใช้ logic เดียวกับ brand 1 (ไม่มี over_budget → เกินงบทุกกรณีจะได้ b1_md เสมอ)
    private function approvalCase(Salecar $saleCar): string
    {
        // ตรรกะเคสรวมไว้ที่ Salecar::approvalCase() (ใช้ร่วมกับการคิดค่าคอมสด)
        return $saleCar->approvalCase();
    }

    // signature ที่ถือว่า "อนุมัติครบ" ตามเคส (ใช้ gate การผูกรถ + ปลดล็อก save)
    private function isApproved(Salecar $saleCar): bool
    {
        return match ($this->approvalCase($saleCar)) {
            'normal'     => (bool) $saleCar->SMSignature,
            'b1_manager' => (bool) $saleCar->ApprovalSignature,
            'b1_md', 'b2_gm' => (bool) $saleCar->GMApprovalSignature,
            default      => false,
        };
    }

    // role ผู้อนุมัติด่านแรก — ทุกเคส/ทุกแบรนด์เริ่มที่ผู้จัดการเสมอ
    // (เดิม b2_gm ยิงเข้า GM ตรง ๆ — ย้ายมาให้ผู้จัดการกรอกยอดก่อนแล้ว)
    private function firstApproverRole(string $case): string
    {
        return 'manager';
    }

    // ป้ายชื่อผู้อนุมัติขั้นสุดท้ายของเคสเกินเพดาน — VIP (brand 2) → MD, นอกนั้น → GM
    private function finalApproverLabel(Salecar $saleCar): string
    {
        return strtoupper($saleCar->finalApproverRole());
    }

    // หาอีเมลผู้อนุมัติตามขั้น (manager/gm/md)
    //  - brand 3 ใช้ของ brand 1 (alias ใน config/approval.php)
    //  - manager: ดึงจาก DB ตาม brand+branch (รองรับสาขาใหม่อัตโนมัติ) ถ้าไม่เจอ fallback config
    //  - gm/md: อ่านจาก config ระดับ brand
    private function approverEmails($brand, $branch, string $stage): array
    {
        $alias = config("approval.$brand");
        $resolvedBrand = is_int($alias) ? $alias : (int) $brand;
        $cfg = config("approval.$resolvedBrand", []);

        if ($stage === 'manager') {
            $emails = User::where('role', 'manager')
                ->where('brand', $resolvedBrand)
                ->where('branch', $branch)
                ->whereNotNull('email')
                ->pluck('email')->unique()->values()->all();
            if (!empty($emails)) {
                return $emails;
            }
        }

        return array_values((array) ($cfg[$stage] ?? []));
    }

    // สร้างไฟล์แนบสำหรับเมลขออนุมัติ: (1) PDF สรุปการขาย (ดึงจาก salecars, ซ่อนวันส่งมอบ) (2) ไฟล์ผู้ขอที่เก็บไว้
    // ใช้ได้ทั้งเมลผู้จัดการและ GM (ดึงไฟล์ผู้ขอจาก salecars.approval_files ที่เก็บถาวร)
    private function buildApprovalAttachments(Salecar $saleCar): array
    {
        $files = [];

        // รีเฟรช accessories/campaigns ที่เพิ่ง sync กัน PDF ได้ข้อมูลเก่า
        $saleCar->load(['accessories', 'campaigns.campaign.type', 'campaigns.campaign.appellation']);

        // (1) ไฟล์สรุปการขาย — ใช้ view เดิม ซ่อน section วันส่งมอบ
        $pdf = Pdf::loadView('purchase-order.report.summary', [
            'saleCar'      => $saleCar,
            'model'        => collect(),
            'hideDelivery' => true,
        ])->setPaper('A4', 'portrait');

        $files[] = Attachment::fromData(fn() => $pdf->output(), 'summary-' . $saleCar->id . '.pdf')
            ->withMime('application/pdf');

        // (2) ไฟล์กระเป๋าของเซลล์ — งบที่ได้ทั้งก้อน vs ทุกอย่างที่ใช้ไป
        //     ปิดไว้ก่อน (ยังไม่ใช้) — uncomment block นี้เพื่อเปิดแนบกลับทุกเคสที่ขออนุมัติ
        //     โค้ดที่เกี่ยวข้องยังอยู่ครบ: buildSalePocketData() + view purchase-order/report/sale-pocket
        // $pocketPdf = Pdf::loadView('purchase-order.report.sale-pocket', [
        //     'saleCar' => $saleCar,
        //     'pocket'  => $this->buildSalePocketData($saleCar),
        // ])->setPaper('A4', 'portrait');
        //
        // $files[] = Attachment::fromData(fn() => $pocketPdf->output(), 'sale-pocket-' . $saleCar->id . '.pdf')
        //     ->withMime('application/pdf');

        // (3) ไฟล์แนบจากผู้ขอ (เก็บไว้ใน storage — ส่งต่อ GM ได้)
        foreach (($saleCar->approval_files ?? []) as $f) {
            if (!empty($f['path']) && \Illuminate\Support\Facades\Storage::exists($f['path'])) {
                $files[] = Attachment::fromPath(\Illuminate\Support\Facades\Storage::path($f['path']))
                    ->as($f['name'] ?? basename($f['path']))
                    ->withMime($f['mime'] ?? 'application/octet-stream');
            }
        }

        return $files;
    }

    // อีเมลที่ CC เพิ่มในสายอนุมัติเกินงบขั้นสุดท้าย
    //  - brand 2 : ผู้บริหาร (ketsudap + danut) ตลอดสาย (ทั้งขั้น gm และ VIP ที่ส่ง md)
    //  - brand 1/3/4 : md — ได้ลิงก์อนุมัติเหมือนผู้อนุมัติหลัก (ดู emailFinalApprover)
    //  - กันซ้ำกับ To ที่ส่ง (เช่น danut เป็น md อยู่แล้วในเมลส่งต่อ → เหลือ CC แค่ ketsudap)
    private function overBudgetCc(Salecar $saleCar, array $to = []): array
    {
        if ((int) $saleCar->brand === 2) {
            $cc = ['ketsudap@chookiat.org', 'danut@chookiat.org'];
        } else {
            $cc = $this->approverEmails($saleCar->brand, $saleCar->branch, 'md');
            if (empty($cc)) {
                $cc = ['ketsudap@chookiat.org'];
            }
        }
        return array_values(array_diff($cc, $to));
    }

    // อีเมลที่ CC ทุกเมลในสายอนุมัติของแบรนด์นั้น (คำขอทุกเคส รวมงบปกติ + เมลตีกลับ)
    // ตั้งค่าที่ config/approval.php คีย์ 'request_cc'
    //  - brand 1/3/4 : CC ให้ daw รับทราบ (brand 3 ใช้ config ของ brand 1 ผ่าน alias)
    //  - กันซ้ำกับ To/CC ที่มีอยู่แล้ว
    private function requestCc($brand, array $exclude = []): array
    {
        $alias         = config("approval.$brand");
        $resolvedBrand = is_int($alias) ? $alias : (int) $brand;

        $cc = array_values(array_filter((array) config("approval.$resolvedBrand.request_cc", [])));

        return array_values(array_diff($cc, $exclude));
    }

    // อีเมลขั้นถัดไป (ผู้อนุมัติขั้นสุดท้าย) พร้อมข้อมูล+ไฟล์ทั้งสอง
    //  - b1_md (brand 1/3)       : ส่งต่อ GM (MD ได้ลิงก์อนุมัติด้วย)
    //  - b2_gm ปกติ (brand 2/4)  : ส่งต่อ GM (MD ได้ลิงก์อนุมัติด้วย)
    //  - b2_gm VIP (brand 2)     : ส่งต่อ MD (GM ได้ลิงก์อนุมัติด้วย)
    //  ทั้งคู่ใช้ approval_final_token ตัวเดียวกัน → ใครกดอนุมัติก่อนถือว่าจบ
    //  ส่วน audit ประจำแบรนด์ได้แค่สำเนา (approval_token) เปิดดู/ตีกลับได้ แต่กดอนุมัติไม่ได้
    private function emailFinalApprover(Salecar $saleCar, ?float $deduct): void
    {
        $case      = $this->approvalCase($saleCar);
        $finalRole = $case === 'b2_gm' ? $saleCar->finalApproverRole() : 'gm';

        $mailTo = $this->approverEmails($saleCar->brand, $saleCar->branch, $finalRole);
        if (empty($mailTo)) {
            $mailTo = $finalRole === 'gm'
                ? ['JirapornK@Chookiat.org']
                : ($saleCar->brand == 2 ? ['danut@chookiat.org'] : ['ketsudap@chookiat.org']);
        }

        $data = $this->buildApprovalData($saleCar);
        if ($deduct !== null) {
            $data['commission_deduct'] = $deduct;
            // เก็บงบเพิ่มเติม = ค่าที่ผู้จัดการกรอกเอง (เฉพาะแบรนด์ที่ใช้ช่องนี้ — brand 2/4 ไม่ใช้)
            $data['extra_budget'] = $saleCar->usesExtraBudget() && $saleCar->approval_extra_budget !== null
                ? (float) $saleCar->approval_extra_budget
                : null;
        }
        $files = $this->buildApprovalAttachments($saleCar);

        // ── แยกผู้รับ 2 กลุ่ม ──
        // (1) ผู้บริหารอีกฝั่ง (เคสปกติ = MD | เคส VIP = GM) — ตกลงกันว่าให้ "กดอนุมัติจบได้" เหมือนกัน
        //     ใครกดก่อนถือว่าจบ (ใช้ approval_final_token ตัวเดียวกันกับผู้อนุมัติหลัก)
        $ccApprovers = $this->overBudgetCc($saleCar, (array) $mailTo);
        if ($finalRole === 'md') {
            $gm = $this->approverEmails($saleCar->brand, $saleCar->branch, 'gm') ?: ['JirapornK@Chookiat.org'];
            $ccApprovers = array_merge($ccApprovers, array_diff($gm, (array) $mailTo, $ccApprovers));
        }
        $ccApprovers = array_values(array_unique($ccApprovers));

        // (2) สำเนารับทราบประจำแบรนด์ (audit) — เปิดดู/ตีกลับได้ แต่กดอนุมัติไม่ได้
        $ccInfo = $this->requestCc($saleCar->brand, array_merge((array) $mailTo, $ccApprovers));

        // ── ลิงก์อนุมัติ "ขั้นสุดท้าย" ใช้ token คนละตัวกับลิงก์ทั่วไป ──
        // ออก token ใหม่ทุกครั้งที่ส่ง แล้วใส่ให้เฉพาะกลุ่มที่มีสิทธิ์อนุมัติ (To + ผู้บริหารอีกฝั่ง)
        // ส่วนสำเนา audit ยังได้ approval_token ปกติ ซึ่ง holdsFinalToken() จะกันไม่ให้กดอนุมัติ
        $finalToken = null;
        if (Salecar::hasFinalTokenColumn()) {
            $finalToken = Str::random(48);
            $saleCar->update(['approval_final_token' => $finalToken]);
        }

        $mailModel = $saleCar->fresh(['model', 'saleUser', 'customer.prefix']);
        $mailType  = $finalRole === 'gm' ? 'gm_final' : 'md_final';

        // ผู้อนุมัติหลัก
        Mail::to($mailTo)->send(new SaleRequestMail($mailModel, $mailType, $data, $files, $finalToken));

        // ผู้บริหารอีกฝั่ง — ลิงก์กดอนุมัติได้เหมือนกัน
        if ($ccApprovers) {
            Mail::to($ccApprovers)->send(new SaleRequestMail($mailModel, $mailType, $data, $files, $finalToken));
        }

        // สำเนารับทราบ — token ปกติ กดอนุมัติไม่ได้
        if ($ccInfo) {
            Mail::to($ccInfo)->send(new SaleRequestMail($mailModel, $mailType, $data, $files, null, true));
        }
    }

    // แจ้งเมื่ออนุมัติเสร็จสมบูรณ์ → ส่งหา เซลล์ (saleUser) + audit (ตาม brand จาก config)
    //  $includeManager = true → แจ้งผู้จัดการด้วย (ใช้กรณี MD กรอกยอดหักใหม่เองแล้วอนุมัติ)
    private function notifyApproved(Salecar $saleCar, bool $includeManager = false): void
    {
        $to = [];
        if ($saleCar->saleUser?->email) {
            $to[] = $saleCar->saleUser->email;
        }
        $to = array_merge($to, $this->approverEmails($saleCar->brand, $saleCar->branch, 'audit'));
        if ($includeManager) {
            $to = array_merge($to, $this->approverEmails($saleCar->brand, $saleCar->branch, 'manager'));
        }
        $to = array_values(array_unique(array_filter($to)));

        if (empty($to)) {
            return;
        }

        Mail::to($to)->send(new SaleApprovedMail(
            $saleCar->fresh(['model', 'subModel', 'saleUser', 'customer.prefix', 'gwmColor', 'interiorColor'])
        ));
    }

    // เปิดลิงก์อนุมัติจากเมล (ไม่ต้อง login — ใช้ token) — แสดงหน้าตามเคส/ขั้นปัจจุบัน
    /**
     * token ที่ยื่นมาเป็นของ "ผู้อนุมัติขั้นสุดท้าย" ตัวจริงไหม
     * ยังไม่ได้รัน ALTER (ไม่มีคอลัมน์) หรือใบนี้ยังไม่เคยออก token จบ → ถือว่าใช่ (พฤติกรรมเดิม)
     */
    private function holdsFinalToken(Salecar $saleCar, string $token): bool
    {
        if (!Salecar::hasFinalTokenColumn() || empty($saleCar->approval_final_token)) {
            return true;
        }

        return hash_equals((string) $saleCar->approval_final_token, $token);
    }

    public function emailApprove($token)
    {
        // เปิดผ่านลิงก์ในเมล — ผู้กดอาจล็อกอินคนละ brand → ปิด BrandScope ทั้ง request
        ScopeBypass::$brand = true;

        $saleCar = Salecar::withoutGlobalScopes()
            ->with(['model', 'saleUser', 'customer'])
            ->where(fn($q) => $q->where('approval_token', $token)
                ->when(Salecar::hasFinalTokenColumn(), fn($w) => $w->orWhere('approval_final_token', $token)))
            ->first();

        if (!$saleCar) {
            return response('ลิงก์ไม่ถูกต้องหรือหมดอายุ', 404);
        }

        // เปิดมาด้วยลิงก์ของผู้อนุมัติตัวจริงไหม — คนที่ถูก CC จะได้ token ปกติ กดอนุมัติจบไม่ได้
        $canApproveFinal = $this->holdsFinalToken($saleCar, $token);

        if ($this->isApproved($saleCar)) {
            return view('purchase-order.approval-result', [
                'saleCar' => $saleCar,
                'msg'     => 'รายการนี้อนุมัติเรียบร้อยแล้ว',
            ]);
        }

        $case = $this->approvalCase($saleCar);

        switch ($case) {
            case 'normal':
            case 'b1_manager':
                // ผู้จัดการกดยืนยัน (ไม่ต้องกรอกหัก)
                return view('purchase-order.approval-manager', ['saleCar' => $saleCar, 'token' => $token, 'showDeduct' => false]);

            case 'b1_md':
            case 'b2_gm':
                // ผู้จัดการกรอกยอด (b2_gm ของ brand 2 เลือก "ไม่หักเงิน VIP" ได้) → ส่งต่อขั้นสุดท้าย
                if (!$saleCar->ApprovalSignature) {
                    return view('purchase-order.approval-manager', ['saleCar' => $saleCar, 'token' => $token, 'showDeduct' => true]);
                }
                // ขั้นสุดท้าย (GM หรือ MD ถ้า VIP): อนุมัติ (แก้ยอดได้) หรือ ตีกลับให้ผู้จัดการ
                return view('purchase-order.approval-confirm', [
                    'saleCar'         => $saleCar,
                    'token'           => $token,
                    'allowRevise'     => true,
                    'approverLabel'   => $this->finalApproverLabel($saleCar),
                    'canApproveFinal' => $canApproveFinal,
                ]);

            default:
                // fallback — ขั้นสุดท้าย (md) กดยืนยัน
                return view('purchase-order.approval-confirm', ['saleCar' => $saleCar, 'token' => $token, 'allowRevise' => false, 'approverLabel' => 'MD', 'canApproveFinal' => $canApproveFinal]);
        }
    }

    // ผู้จัดการกดอนุมัติ — normal/b1_manager: กดยืนยัน | b1_md/b2_gm: กรอกยอด → ส่งต่อขั้นสุดท้าย
    public function managerApprove(Request $request, $token)
    {
        ScopeBypass::$brand = true; // ผู้อนุมัติอาจล็อกอินคนละ brand → ปิด BrandScope ทั้ง request

        $saleCar = Salecar::withoutGlobalScopes()->where('approval_token', $token)->firstOrFail();
        $case = $this->approvalCase($saleCar);
        $today = now();

        // ครอบ transaction: ถ้าส่งอีเมลขั้นถัดไปพัง → rollback การเซ็นอนุมัติ (กันค้างสถานะ "อนุมัติแล้วแต่เมลไม่ออก")
        $msg = DB::transaction(function () use ($request, $saleCar, $case, $today) {
            if ($case === 'normal') {
                $saleCar->update(['SMSignature' => 1, 'SMCheckedDate' => $today]);
                $this->notifyApproved($saleCar);
                return 'อนุมัติเรียบร้อย (ผู้จัดการ – อนุมัติการขาย)';
            } elseif ($case === 'b1_manager') {
                $saleCar->update(['ApprovalSignature' => 1, 'ApprovalSignatureDate' => $today]);
                $this->notifyApproved($saleCar);
                return 'อนุมัติเรียบร้อย (ผู้จัดการ – เกินงบ ไม่เกินเพดาน)';
            } elseif ($case === 'b1_md' || $case === 'b2_gm') {
                // brand 2 เท่านั้นที่เลือก "ไม่หักเงิน VIP" ได้ → ข้ามการกรอกยอด แล้วส่งให้ MD อนุมัติจบ
                $isVip = $saleCar->allowsVipChoice() && $request->input('decision') === 'vip';

                if ($isVip) {
                    // VIP = ไม่หักเงิน → บันทึกเป็น 0 (ไม่ใช่ null) กันไม่ให้ตกไปเข้าสูตรหักอัตโนมัติ
                    $deduct = 0.0;
                } else {
                    $request->merge([
                        'commission_deduct' => str_replace(',', '', (string) $request->commission_deduct),
                        'extra_budget'      => $request->filled('extra_budget') ? str_replace(',', '', (string) $request->extra_budget) : null,
                    ]);
                    $request->validate([
                        'commission_deduct' => 'required|numeric|min:0',
                        'extra_budget'      => 'nullable|numeric|min:0',
                    ], [
                        'commission_deduct.required' => 'กรุณากรอกยอดที่ต้องหัก',
                    ]);
                    $deduct = (float) $request->commission_deduct;
                }

                // "เก็บงบเพิ่มเติม" ใช้เฉพาะ brand 1/3 (ดู Salecar::usesExtraBudget)
                $extraBudget = ($saleCar->usesExtraBudget() && !$isVip && $request->filled('extra_budget'))
                    ? (float) $request->extra_budget
                    : null;

                $saleCar->update([
                    'approval_commission_deduct' => $deduct,
                    'approval_extra_budget'      => $extraBudget,
                    'approval_is_vip'            => $isVip,
                    // กติกาใหม่ : ยอดที่กรอกคือ "ยอดที่ต้องหัก" ทุกแบรนด์ (ใบเก่าไม่ตั้งธง จึงคงความหมายเดิม)
                    'approval_is_deduct'         => true,
                    'ApprovalSignature' => 1,
                    'ApprovalSignatureDate' => $today,
                    'approval_md_note' => null, // เคลียร์โน้ตผู้อนุมัติรอบก่อน (ถ้าเคยถูกตีกลับ)
                ]);

                $this->emailFinalApprover($saleCar, $deduct);
                $label = $this->finalApproverLabel($saleCar);
                return $isVip
                    ? "ผู้จัดการเลือกไม่หักเงิน (VIP) — ส่งต่อให้ {$label} อนุมัติ (ส่งอีเมลพร้อมไฟล์แนบแล้ว)"
                    : "ผู้จัดการอนุมัติแล้ว — ส่งต่อให้ {$label} อนุมัติ (ส่งอีเมลพร้อมไฟล์แนบแล้ว)";
            }
            abort(400);
        });

        return view('purchase-order.approval-result', compact('saleCar', 'msg'));
    }

    // ขั้นสุดท้าย (GM — หรือ MD ถ้าผู้จัดการเลือก VIP) — อนุมัติ (ใช้ยอดเดิม/กรอกใหม่) หรือ ตีกลับให้ผู้จัดการกรอกใหม่
    public function finalApprove(Request $request, $token)
    {
        ScopeBypass::$brand = true; // ผู้อนุมัติอาจล็อกอินคนละ brand → ปิด BrandScope ทั้ง request

        $saleCar = Salecar::withoutGlobalScopes()
            ->where(fn($q) => $q->where('approval_token', $token)
                ->when(Salecar::hasFinalTokenColumn(), fn($w) => $w->orWhere('approval_final_token', $token)))
            ->firstOrFail();

        // อนุมัติจบแล้ว → แสดงผลเดิม
        if ($saleCar->GMApprovalSignature) {
            return view('purchase-order.approval-result', [
                'saleCar' => $saleCar,
                'msg'     => 'รายการนี้อนุมัติเรียบร้อยแล้ว',
            ]);
        }

        $case = $this->approvalCase($saleCar);
        // ทุกเคสเกินเพดานผ่านมือผู้จัดการมาก่อนแล้ว → ผู้อนุมัติขั้นสุดท้ายแก้ยอด/ส่งกลับได้เหมือนกันหมด
        $canRevise = in_array($case, ['b1_md', 'b2_gm'], true);
        $approverLabel = $this->finalApproverLabel($saleCar); // GM (ปกติ) | MD (VIP)

        // ── ผู้อนุมัติขั้นสุดท้ายตีกลับให้ผู้จัดการกรอกยอดใหม่ ──
        if ($canRevise && $request->input('decision') === 'return') {
            $request->validate([
                'md_note' => 'nullable|string|max:1000',
            ]);

            $saleCar->update([
                'ApprovalSignature'     => 0,      // รีเซ็ต → ผู้จัดการเปิดลิงก์เดิมจะกลับไปหน้ากรอกยอดหัก
                'ApprovalSignatureDate' => null,
                'approval_md_note'      => $request->md_note,
            ]);

            $this->emailReturnToManager($saleCar, $request->md_note);

            return view('purchase-order.approval-result', [
                'saleCar' => $saleCar,
                'msg'     => 'ส่งกลับให้ผู้จัดการกรอก' . $saleCar->approvalDeductLabel() . 'ใหม่แล้ว (แจ้งอีเมลผู้จัดการเรียบร้อย)',
            ]);
        }

        // ── ผู้อนุมัติขั้นสุดท้ายอนุมัติ (ถ้ากรอกยอดใหม่มา → override) ──
        // ต้องมาด้วยลิงก์ของผู้อนุมัติตัวจริงเท่านั้น — คนที่ถูก CC ได้ token ปกติ กดอนุมัติไม่ได้
        // (แต่ยัง "ตีกลับ" ได้ ซึ่งเป็นเจตนาเดิมของการ CC)
        if (!$this->holdsFinalToken($saleCar, $token)) {
            return view('purchase-order.approval-result', [
                'saleCar' => $saleCar,
                'msg'     => "ลิงก์นี้เป็นสำเนาสำหรับรับทราบ — การอนุมัติขั้นสุดท้ายต้องกดจากอีเมลของ {$approverLabel} เท่านั้น (ตีกลับยังทำได้จากลิงก์นี้)",
            ]);
        }

        $mdEdited = false;
        if ($canRevise && $request->filled('commission_deduct')) {
            $request->merge(['commission_deduct' => str_replace(',', '', (string) $request->commission_deduct)]);
            $request->validate([
                'commission_deduct' => 'numeric|min:0',
            ]);
            $newDeduct = (float) $request->commission_deduct;
            $mdEdited = $newDeduct != (float) ($saleCar->approval_commission_deduct ?? 0);
            $saleCar->approval_commission_deduct = $newDeduct;
        }

        // เก็บงบเพิ่มเติม — ผู้อนุมัติแก้ได้ก่อนอนุมัติ (เฉพาะแบรนด์ที่ใช้ช่องนี้ = brand 1/3)
        if ($canRevise && $saleCar->usesExtraBudget() && $request->has('extra_budget')) {
            $request->merge([
                'extra_budget' => $request->filled('extra_budget') ? str_replace(',', '', (string) $request->extra_budget) : null,
            ]);
            $request->validate(['extra_budget' => 'nullable|numeric|min:0']);
            $saleCar->approval_extra_budget = $request->filled('extra_budget') ? (float) $request->extra_budget : null;
        }

        $saleCar->update([
            'approval_commission_deduct' => $saleCar->approval_commission_deduct,
            'approval_extra_budget'      => $saleCar->approval_extra_budget,
            'GMApprovalSignature'        => 1,
            'GMApprovalSignatureDate'    => now(),
            'approval_md_note'           => null, // เคลียร์โน้ตเมื่ออนุมัติจบ
        ]);

        // แจ้งผู้จัดการด้วยเมื่อ MD แก้ยอด (ตามที่ตกลง) — sale+audit แจ้งเสมอ
        $this->notifyApproved($saleCar, $mdEdited);

        return view('purchase-order.approval-result', [
            'saleCar' => $saleCar,
            'msg'     => $mdEdited
                ? "อนุมัติเรียบร้อย ({$approverLabel} — แก้{$saleCar->approvalDeductLabel()} แจ้งผู้จัดการแล้ว)"
                : "อนุมัติเรียบร้อย ({$approverLabel})",
        ]);
    }

    /**
     * ตีกลับใบจอง (ทุกขั้น) — ใช้เมื่อผู้อนุมัติเห็นว่ายอด/ข้อมูลผิด
     *  ปลายทาง: audit (config) + ฝ่ายขาย ทุกเคส [จบรอบ ต้องขออนุมัติใหม่]
     *  (คนละอย่างกับปุ่ม "ส่งกลับให้ผู้จัดการแก้" ใน finalApprove ที่แก้แค่ยอด แล้วอยู่ในรอบเดิม)
     *  รีเซ็ตลายเซ็นทั้งหมดเสมอ
     */
    public function returnApproval(Request $request, $token)
    {
        ScopeBypass::$brand = true;

        $saleCar = Salecar::withoutGlobalScopes()
            ->with(['model', 'subModel', 'saleUser', 'customer.prefix'])
            ->where('approval_token', $token)
            ->firstOrFail();

        $request->validate(['return_reason' => 'nullable|string|max:1000']);

        $case = $this->approvalCase($saleCar);

        // ขั้นปัจจุบัน — ดูจากลายเซ็น "ก่อน" รีเซ็ต (เกินเพดานทุกเคส: ผู้จัดการ → ขั้นสุดท้าย)
        $stage = (in_array($case, ['b1_md', 'b2_gm'], true) && $saleCar->ApprovalSignature)
            ? 'final'
            : 'manager';

        // ปลายทาง: แจ้ง audit + ฝ่ายขาย ให้แก้ใบจอง แล้วจบรอบ (ต้องยื่นคำขอใหม่)
        $mailTo = $this->approverEmails($saleCar->brand, $saleCar->branch, 'audit');
        if ($saleCar->saleUser?->email) {
            $mailTo[] = $saleCar->saleUser->email;
        }
        $returnedBy = $stage === 'final' ? $this->finalApproverLabel($saleCar) : 'ผู้จัดการ';
        $endRound   = true;

        // รีเซ็ตลายเซ็นทั้งหมด + บันทึกเหตุผล
        $update = [
            'SMSignature'             => 0,
            'SMCheckedDate'           => null,
            'ApprovalSignature'       => 0,
            'ApprovalSignatureDate'   => null,
            'GMApprovalSignature'     => 0,
            'GMApprovalSignatureDate' => null,
            'approval_return_note'    => $request->return_reason,
            'approval_returned_at'    => now(),
            'approval_is_vip'         => 0,   // ตีกลับ = เริ่มรอบใหม่ ผู้จัดการเลือก VIP ใหม่ได้
            'approval_is_deduct'      => 0,
        ];

        // จบรอบ → เคลียร์คำขอ + ล้าง token (ลิงก์เมลเดิมใช้ไม่ได้) ต้องส่งขออนุมัติใหม่
        if ($endRound) {
            $update['approval_requested_at'] = null;
            $update['approval_token']        = null;
            if (Salecar::hasFinalTokenColumn()) {
                $update['approval_final_token'] = null;
            }
        }

        $saleCar->update($update);

        $actionUrl = $endRound ? null : route('purchase-order.emailApprove', ['token' => $token]);

        $mailTo = array_values(array_unique(array_filter($mailTo)));
        // CC ประจำแบรนด์ (brand 1/3/4 → daw) — รับทราบการตีกลับด้วย
        $mailCc = $this->requestCc($saleCar->brand, $mailTo);

        try {
            Mail::to($mailTo)->cc($mailCc)
                ->send(new ApprovalReturnMail($saleCar->fresh(['model', 'subModel', 'saleUser', 'customer.prefix']), $request->return_reason, $returnedBy, $actionUrl));
        } catch (\Throwable $e) {
            report($e); // ส่งเมลล้มเหลวไม่ควรทำให้การตีกลับล้มเหลว
        }

        return view('purchase-order.approval-result', [
            'saleCar' => $saleCar,
            'msg'     => 'ตีกลับใบจองเรียบร้อยแล้ว — แจ้งอีเมลผู้เกี่ยวข้องแล้ว',
        ]);
    }

    // ผู้อนุมัติขั้นสุดท้ายตีกลับ → แจ้งผู้จัดการให้ทบทวนยอดที่ต้องหักใหม่ (ลิงก์เดิมจะกลับไปหน้ากรอก)
    private function emailReturnToManager(Salecar $saleCar, ?string $note = null): void
    {
        $mailTo = $this->approverEmails($saleCar->brand, $saleCar->branch, 'manager');
        if (empty($mailTo)) {
            // fallback ต้องเป็น "ผู้จัดการ" ของแบรนด์ (เดิม brand 2 ใส่อีเมล GM ไว้ผิดขั้น)
            $mailTo = $saleCar->brand == 2
                ? ['SasithornK@chookiat.org']
                : ['Phung.mitsuchookiatkrabi@gmail.com'];
        }

        $data  = $this->buildApprovalData($saleCar);
        $files = $this->buildApprovalAttachments($saleCar);

        Mail::to($mailTo)->cc($this->requestCc($saleCar->brand, (array) $mailTo))->send(new SaleRequestMail(
            $saleCar->fresh(['model', 'saleUser', 'customer.prefix']),
            'manager_revise',
            $data,
            $files
        ));
    }

    // resource route สร้าง GET purchase-order/{id} → show() แต่ไม่มี method นี้ → redirect ไปหน้า edit แทน
    public function show($id)
    {
        return redirect()->route('purchase-order.edit', $id);
    }

    // ชื่อเต็มลูกค้าปัจจุบัน + ชื่อผู้จองเดิม (ถ้ามีการเปลี่ยนผู้ซื้อ) — คืน HTML สำหรับตาราง
    // ต้อง eager load 'originalCustomer.prefix' มาก่อน
    private function customerNameWithOriginal(Salecar $s): string
    {
        // คอลัมน์นี้ถูก render เป็น HTML ใน DataTables → ต้อง escape ชื่อที่มาจากข้อมูลผู้ใช้
        $c = $s->customer;
        $name = e(implode(' ', array_filter([
            $c?->prefix?->Name_TH,
            $c?->FirstName,
            $c?->LastName,
        ])));

        if ($s->original_customer_id && $s->originalCustomer) {
            $o = $s->originalCustomer;
            $origName = trim(implode(' ', array_filter([
                $o->prefix->Name_TH ?? null,
                $o->FirstName ?? null,
                $o->LastName ?? null,
            ])));
            if ($origName !== '') {
                $name .= '<br><small style="color:#6c757d;">ผู้จอง : ' . e($origName) . '</small>';
            }
        }

        return $name;
    }

    public function listPurchaseOrder(Request $request)
    {
        $draw         = (int) ($request->draw ?? 1);
        $start        = (int) ($request->start ?? 0);
        $length       = (int) ($request->length ?? 10);
        $search       = trim($request->input('search.value', ''));
        $statusFilter = $request->con_status;
        $saleFilter   = $request->sale_filter ? json_decode($request->sale_filter, true) : null;
        $user         = Auth::user();

        $base = Salecar::query();

        if (in_array($user->role, ['sale', 'lead_sale'])) {
            $visibleSaleIds = [$user->id];
            if ($user->role === 'lead_sale') {
                $visibleSaleIds = array_merge($visibleSaleIds, [9, 10, 11]);
            }
            $base->whereIn('SaleID', $visibleSaleIds);
        }

        if ($statusFilter) {
            $base->where('con_status', $statusFilter);
        } else {
            $base->whereIn('con_status', [1, 2, 3, 4, 6]);
        }

        if ($saleFilter && count($saleFilter) > 0) {
            $saleIds = User::whereIn('name', $saleFilter)->pluck('id');
            $base->whereIn('SaleID', $saleIds);
        }

        $recordsTotal = (clone $base)->count();

        if ($search) {
            $base->where(function ($q) use ($search) {
                $q->whereHas('customer', fn($q) => $q->searchFullName($search))
                // ค้นชื่อ "ผู้จองเดิม" ด้วย (กรณีเปลี่ยนผู้ซื้อ) — ตารางแสดง 2 ชื่อ ต้องค้นเจอทั้งคู่
                ->orWhereHas('originalCustomer', fn($q) => $q->searchFullName($search))
                ->orWhereHas('saleUser', fn($q) =>
                    $q->where('name', 'like', "%{$search}%")
                )
                ->orWhereHas('carOrder', fn($q) =>
                    $q->where('order_code', 'like', "%{$search}%")
                )
                // รุ่นรถ — คอลัมน์ "รุ่นรถ" ในตารางแสดง รุ่นหลัก/รุ่นย่อย/รายละเอียด จึงต้องค้นได้ทั้งสามค่า
                // ครอบ closure ซ้อนไว้ ไม่งั้น orWhere จะหลุดออกนอกเงื่อนไข join ของ whereHas
                ->orWhereHas('model', fn($q) => $q->where(fn($w) =>
                    $w->where('Name_TH', 'like', "%{$search}%")
                      ->orWhere('Name_EN', 'like', "%{$search}%")
                ))
                ->orWhereHas('subModel', fn($q) => $q->where(fn($w) =>
                    $w->where('name', 'like', "%{$search}%")
                      ->orWhere('detail', 'like', "%{$search}%")
                ));
            });
        }

        $recordsFiltered = (clone $base)->count();

        $saleCars = $base
            ->with('customer.prefix', 'originalCustomer.prefix', 'conStatus', 'saleUser', 'model', 'subModel', 'carOrder', 'remainingPayment')
            ->orderBy('BookingDate', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        $rowNum = $start + 1;
        $canWithdraw = self::canWithdrawBooking(); // sale/lead_sale ไม่เห็นปุ่มถอนจอง

        $data = $saleCars->map(function ($s) use (&$rowNum, $canWithdraw) {
            $model        = $s->model ? $s->model->Name_TH : '';
            $subModelSale = $s->subModel ? $s->subModel->name : '';
            $subDetail    = $s->subModel ? $s->subModel->detail : '';
            $statusSale   = $s->conStatus ? $s->conStatus->name : '';

            $row = fn($icon, $class, $tip, $text) =>
                "<div class=\"text-start\"><i class=\"bx {$icon} {$class} me-1\" data-bs-toggle=\"tooltip\" title=\"{$tip}\"></i>:&nbsp;{$text}</div>";

            if (in_array($s->brand, [2, 3, 4])) {
                $car = $row('bxs-car',       'text-primary', 'รุ่นหลัก', $model)
                     . $row('bx-git-branch', 'text-info',    'รุ่นย่อย', $subModelSale);
            } else {
                $car = $row('bxs-car',       'text-primary', 'รุ่นหลัก', $model)
                     . $row('bx-git-branch', 'text-info',    'รุ่นย่อย', $subModelSale)
                     . ($subDetail ? $row('bx-info-circle', 'text-warning', 'รายละเอียด', $subDetail) : '');
            }

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

            $status = $row('bx-receipt',      'text-success', 'ใบจอง',        $statusSale)
                    . $row('bx-check-shield', 'text-warning', 'การตรวจสอบ', $approver);

            $salecarId    = $s->id;
            $editUrl      = route('purchase-order.edit', $salecarId);
            $summaryUrl   = route('purchase-order.summary', $salecarId);
            $bookingUrl   = route('purchase-order.booking-pdf', $salecarId);
            $hasRemaining = !empty($s->remainingPayment);

            $summaryBtn = $hasRemaining
                ? "<a href=\"{$summaryUrl}\" target=\"_blank\" class=\"btn btn-icon btn-primary text-white\" title=\"สรุปค่าใช้จ่าย\"><i class=\"bx bx-printer\"></i></a>"
                : "<a href=\"javascript:void(0)\" class=\"btn btn-icon btn-primary text-white\" style=\"opacity:.45;pointer-events:none;cursor:not-allowed;\" title=\"ยังไม่มีข้อมูลค่างวด\"><i class=\"bx bx-printer\"></i></a>";

            $deleteBtn = $canWithdraw
                ? "<button class=\"btn btn-icon btn-danger text-white btnDeleteSale\" data-id=\"{$salecarId}\" title=\"ลบ\"><i class=\"bx bx-trash\"></i></button>"
                : '';

            $action = "<div class=\"d-flex justify-content-center gap-1\">"
                . "<a href=\"{$editUrl}\" class=\"btn btn-icon btn-warning text-white\" title=\"แก้ไข\"><i class=\"bx bx-edit\"></i></a>"
                . "<a href=\"{$bookingUrl}\" target=\"_blank\" class=\"btn btn-icon btn-success text-white\" title=\"ใบจอง\"><i class=\"bx bx-receipt\"></i></a>"
                . $summaryBtn
                . $deleteBtn
                . "</div>";

            return [
                'No'         => $rowNum++,
                'FullName'   => $this->customerNameWithOriginal($s),
                'model'  => $car,
                'order'  => $s->carOrder?->order_code ?? 'ไม่มีข้อมูลการผูกรถ',
                'dates'  => (function () use ($s, $row) {
                    $booking  = $s->format_booking_date ?? '-';
                    $contractRaw = $s->remainingPayment?->contract_date;
                    $contract = '-';
                    if ($contractRaw) {
                        $days = (int) Carbon::parse($contractRaw)->diffInDays(now());
                        $contract = Carbon::parse($contractRaw)->format('d-m-Y') . " ({$days} วัน)";
                    }
                    $po = $s->remainingPayment?->po_date
                        ? Carbon::parse($s->remainingPayment->po_date)->format('d-m-Y')
                        : '-';
                    return $row('bx-calendar',   'text-primary', 'วันที่จอง',        $booking)
                         . $row('bx-pen',        'text-success', 'วันที่เซ็นสัญญา', $contract)
                         . $row('bx-file-blank', 'text-warning', 'วันที่ PO',        $po);
                })(),
                // ใบขาย Dealer ไม่ผูกฝ่ายขาย (SaleID = NULL)
                'sale'   => $s->saleUser?->name ?? '-',
                'statusSale'    => $status,
                'Action'        => $action,
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data->values(),
        ]);
    }

    public function saleOptions(Request $request)
    {
        $user         = Auth::user();
        $statusFilter = $request->con_status;

        $query = Salecar::join('users', 'salecars.SaleID', '=', 'users.id')
            ->select('users.name')
            ->distinct();

        if (in_array($user->role, ['sale', 'lead_sale'])) {
            $visibleSaleIds = [$user->id];
            if ($user->role === 'lead_sale') {
                $visibleSaleIds = array_merge($visibleSaleIds, [9, 10, 11]);
            }
            $query->whereIn('salecars.SaleID', $visibleSaleIds);
        }

        if ($statusFilter) {
            $query->where('salecars.con_status', $statusFilter);
        } else {
            $query->whereIn('salecars.con_status', [1, 2, 3, 4, 6]);
        }

        return response()->json(
            $query->orderBy('users.name')->pluck('users.name')->filter()->values()
        );
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

            $request->validate([
                'CusID' => 'required|exists:customers,id'
            ], [
                'CusID.required' => 'กรุณาค้นหาและเลือกลูกค้า'
            ]);

            // กันข้ามฝั่ง server: ลูกค้าต้องมีเลขบัตร/เบอร์โทร/ที่อยู่ปัจจุบันครบก่อนทำการจอง
            $missingProfile = $this->customerProfileMissing(Customer::find($request->CusID));
            if (!empty($missingProfile)) {
                DB::rollBack();
                return response()->json([
                    'success'      => false,
                    'need_profile' => true,
                    'customer_id'  => (int) $request->CusID,
                    'missing'      => $missingProfile,
                    'message'      => 'ข้อมูลลูกค้ายังไม่ครบ (' . implode(', ', $missingProfile) . ') กรุณากรอกให้ครบก่อนทำการจอง',
                ], 422);
            }

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

            // ผูกกับใบติดตามที่ผู้ใช้กดเข้ามาจริง (from_tracking) — ลูกค้าคนเดียวเปิดการติดตามค้างไว้ได้หลายใบ
            // ถ้าไม่ได้มาจากหน้าติดตาม หรือใบที่ส่งมาไม่ใช่ของลูกค้ารายนี้ ค่อยหาใบล่าสุดที่ยังเปิดอยู่แทน
            $trackingQuery = fn() => CustomerTracking::where('customer_id', $request->CusID)
                ->where('brand', Auth::user()->brand)
                ->whereNull('cancelled_at');

            $trackingId = $request->filled('from_tracking')
                ? $trackingQuery()->whereKey($request->from_tracking)->value('id')
                : null;

            $trackingId ??= $trackingQuery()->orderByDesc('created_at')->value('id');

            // สร้างจากโมดูล "ขออนุมัติเกินงบล่วงหน้า" → ยังไม่เป็นการจอง (global scope ซ่อนไว้)
            $isPreApproval = $request->boolean('is_pre_approval');

            // ขาย Dealer ไม่นับยอด/ไม่คิดคอม (ดู Salecar::scopeSalesQualifying) จึงไม่ผูกฝ่ายขาย
            // ฟอร์มล็อกช่องไว้แล้ว บังคับซ้ำตรงนี้กันยิง request ตรง — คนคีย์เก็บที่ UserInsert อยู่แล้ว
            $isDealerSale = (int) $request->type_sale === Salecar::TYPE_SALE_DEALER;

            $salecar = Salecar::create([
                'is_pre_approval' => $isPreApproval,
                'pre_approval_at' => $isPreApproval ? now() : null,
                'SaleID' => $isDealerSale ? null : $request->SaleID,
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
                'option' => $request->option ?? null,
                'type_color' => $request->type_color ?? null,
                'payment_mode' => $request->payment_mode,
                'CusID' => $request->CusID,
                'BookingDate' => $this->toGregorian($request->BookingDate),
                'TurnCarID' => $turnCarID,
                'con_status' => 1,
                'userZone' => Auth::user()->userZone ?? null,
                'brand' => Auth::user()->brand ?? null,
                'UserInsert' => Auth::id(),
                'branch' => Auth::user()->branch ?? null,
                'gwm_color' => in_array(Auth::user()->brand, [2, 3, 4]) ? $request->gwm_color : null,
                'interior_color' => BrandFeature::hasInteriorColor() ? $request->interior_color : null,
                'tracking_id' => $trackingId,
            ]);

            // เก็บว่าใครกดสร้างการจองจาก tracking นี้
            // คำขออนุมัติล่วงหน้ายังไม่ถือว่าจอง → ยังไม่ประทับ ไปประทับตอน PreApprovalController::convert()
            if ($trackingId && !$isPreApproval) {
                CustomerTracking::whereKey($trackingId)->update([
                    'BookedBy'  => Auth::id(),
                    'booked_at' => now(),
                ]);
            }

            if ($request->hasFile('attachments')) {
                $customer = Customer::find($request->CusID);
                $customerFolder = $customer->id . '-' . ($customer->FirstName ?? 'unknown');
                $brandName = Auth::user()->brandInfo->name ?? 'Other';
                $folder = "New Car/{$brandName}/หลักฐานการจอง/{$customerFolder}";

                $oneDrive = new OneDriveService();
                $urls = [];

                foreach ($request->file('attachments') as $index => $file) {
                    $fileName = 'booking_' . $salecar->id . '_' . ($index + 1) . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $urls[] = [
                        'url'  => $oneDrive->upload($file->getRealPath(), $fileName, $folder),
                        'name' => $file->getClientOriginalName(),
                    ];
                }

                $salecar->update(['attachment_url' => $urls]);
            }

            if ($request->filled('reservationCondition')) {
                $data = [
                    'saleCar_id' => $salecar->id,
                    'category' => 'reservation',
                    'type' => $request->reservationCondition,
                    'cost' => $request->filled('CashDeposit')
                        ? str_replace(',', '', $request->CashDeposit)
                        : null,
                    'date' => $this->toGregorian($request->reservation_date),
                    'userZone' => $request->userZone  ?? null,
                    'brand' => Auth::user()->brand ?? null,
                    'branch' => Auth::user()->branch ?? null,
                ];

                $isBrand2 = Auth::user()->brand == 2;

                switch ($request->reservationCondition) {
                    case 'transfer':
                        $data['transfer_bank'] = $request->reservation_transfer_bank ?? null;
                        $data['transfer_branch'] = $request->reservation_transfer_branch ?? null;
                        $data['transfer_no'] = $request->reservation_transfer_no ?? null;
                        $data['danu_date'] = $isBrand2 ? $this->toGregorian($request->danu_date) : null;

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
                        $data['danu_date'] = $isBrand2 ? $this->toGregorian($request->danu_date) : null;

                        $data['transfer_bank'] = null;
                        $data['transfer_branch'] = null;
                        $data['transfer_no'] = null;
                        $data['credit'] = null;
                        $data['tax_credit'] = null;
                        break;

                    case 'credit':
                        $data['credit'] = $request->reservation_credit ?? null;
                        $data['tax_credit'] = $request->reservation_tax_credit ? str_replace(',', '', $request->reservation_tax_credit) : null;
                        $data['danu_date'] = null;

                        $data['transfer_bank'] = null;
                        $data['transfer_branch'] = null;
                        $data['transfer_no'] = null;
                        $data['check_bank'] = null;
                        $data['check_branch'] = null;
                        $data['check_no'] = null;
                        break;

                    case 'cash':
                    default:
                        $data['danu_date'] = $isBrand2 ? $this->toGregorian($request->danu_date) : null;

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

            // คำขออนุมัติล่วงหน้า → กลับหน้าโมดูลของมัน ไม่ใช่รายการจอง (record ยังไม่เป็นการจอง)
            return response()->json([
                'success'  => true,
                'message'  => $isPreApproval ? 'บันทึกคำขออนุมัติเรียบร้อยแล้ว' : 'เพิ่มข้อมูลเรียบร้อยแล้ว',
                'redirect' => $isPreApproval ? route('pre-approval.index') : route('purchase-order.index'),
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
            ->select('id', 'name', 'detail', 'per_budget') // per_budget : % หักคอมเกินงบเฉพาะรุ่นย่อย (เช่น Triton AT = 40)
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

        // แคมเปญ CK (type = 4) ต้องได้รับอนุมัติของ "เดือนปัจจุบัน" ถึงจะเลือกได้
        $currentPeriod = $today->format('Y-m');

        // forSubModel = แคมเปญของรุ่นย่อยนี้ + แคมเปญ "ทุกรุ่นย่อย" (subModel_id = NULL) ของรุ่นหลักเดียวกัน
        $campaigns = Campaign::with('appellation', 'type')
            ->forSubModel($subModel_id)
            ->where('startYear', '<=', $year)
            ->where('endYear', '>=', $year)
            ->whereDate('startDate', '<=', $today)
            ->whereDate('endDate', '>=', $today)
            ->where(function ($q) use ($currentPeriod) {
                // ไม่ใช่ CK → เลือกได้ตามปกติ | เป็น CK → ต้องมีอนุมัติ approved ของเดือนนี้
                $q->whereHas('type', fn($t) => $t->where('type', '!=', 4))
                    ->orWhereDoesntHave('type')
                    ->orWhereHas('approvals', fn($a) => $a->where('period_ym', $currentPeriod)->where('status', 'approved'));
            })
            ->get();

        return response()->json($campaigns);
    }

    public function edit($id)
    {
        // ปลด scope preApproval — id-based จึงปลอดภัย และต้องแก้ไข "คำขออนุมัติล่วงหน้า" ได้ด้วย
        $saleCar = Salecar::withoutGlobalScope('preApproval')->with(['customer.prefix', 'customer.currentAddress', 'customer.documentAddress', 'customerReferrer.prefix', 'turnCar', 'accessories', 'model', 'carOrder', 'conStatus', 'provinces', 'remainingPayment.financeInfo', 'campaigns.campaign.type', 'campaigns.campaign.appellation', 'originalCustomer.prefix', 'originalTracking',])->findOrFail($id);
        $model = TbCarmodel::all();
        $finances = Finance::all();
        $subModels = TbSubcarmodel::where('model_id', $saleCar->model_id)->get();
        $conStatus = TbConStatus::all();
        $licensePlateRed = $this->availableRedPlates($saleCar);
        $provinces = TbProvinces::all();
        $insurances = Insurance::orderBy('name')->get();
        $type = TbSalecarType::all();
        $typeSale = TbSalePurchaseType::all();
        $payments = SaleCarPayment::where('SaleID', $id)->get();
        $userRole = Auth::user()->role;
        $gwmColor = $saleCar->subModel
            ? $saleCar->subModel->colors
            : collect();
        $interiorColor = $saleCar->model_id
            ? TbInteriorColor::whereHas('models', fn($q) => $q->where('tb_carmodels.id', $saleCar->model_id))->get()
            : collect();

        //history
        $isHistory = in_array($saleCar->con_status, [5, 9]);

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
                ->forSubModel($subModel_id, $saleCar->model_id)
                ->where('active', 'active')
                ->whereDate('startDate', '<=', $today)
                ->whereDate('endDate', '>=', $today)
                ->get();
        }

        $selected_campaigns = $saleCar->campaigns->pluck('CampaignID')->toArray();

        $pricelistRows = $subModel_id
            ? TbPricelistCar::where('subModel_id', $subModel_id)
            ->select('color', 'year')
            ->distinct()
            ->orderBy('color')
            ->orderBy('year')
            ->get()
            : collect();

        $prefixes = TbPrefixname::all();

        $tracking = $saleCar->tracking_id
            ? CustomerTracking::find($saleCar->tracking_id)
            : null;

        // เก็บงบเพิ่มเติม (running deduction): ยอดที่คันนี้โดนหัก + หนี้คงเหลือก่อนถึงคันนี้ (ให้ JS คำนวณสด)
        $extraAbsorbed   = ExtraBudgetLedger::absorbedFor($saleCar);
        $extraDebtBefore = ExtraBudgetLedger::debtBeforeFor($saleCar);

        // budget ยกมา (brand 2) — งบเดือนก่อน × 1,000 ; availableBefore = คงเหลือก่อนหักคันนี้ (ตอนนั้นมี budget เท่าไหร่)
        $budgetWallet = null;
        if ((int) $saleCar->brand === 2 && $saleCar->DeliveryInCKDate) {
            $ck = Carbon::parse($saleCar->DeliveryInCKDate);
            $budgetWallet = [
                'carried'         => BudgetWallet::carried((int) $saleCar->SaleID, $ck->year, $ck->month),
                'availableBefore' => BudgetWallet::remaining((int) $saleCar->SaleID, $ck->year, $ck->month, $saleCar->id),
            ];
        }

        // ระบุราคาประดับยนต์เองได้ไหม (sale/lead_sale ห้าม) — คุมทั้งการแสดงคอลัมน์และตอนบันทึก
        $canCustomAccPrice = self::canSetCustomAccessoryPrice();

        // id ประดับยนต์ที่เป็น "ป้ายแดง" (ตั้งค่าหลังบ้าน) — JS ใช้เช็คว่าต้องบังคับเลือกป้ายแดงก่อนส่งมอบไหม
        $redPlateAccIds = self::redPlateAccessoryIds();

        // ย้ายลูกค้าไปให้เซลล์คนอื่นได้ไหม — โหลดรายชื่อเฉพาะ role ที่มีสิทธิ์ ไม่งั้นเปลือง query
        $canReassignSale = Auth::user()->canReassignSale();
        $saleUser = $canReassignSale
            // SaleID เป็น null ได้ (ใบขาย Dealer) — อย่า cast เป็น int ไม่งั้นกลายเป็น 0
            ? User::salePoolForBrand((int) $saleCar->brand, $saleCar->SaleID !== null ? (int) $saleCar->SaleID : null)
            : collect();

        // แก้ราคารถได้ไหม
        $canEditCarPrice = Auth::user()->canEditCarPrice();

        // ผูก/ปลดรถได้ไหม — อิง brand ของใบจอง ไม่ใช่ brand ที่ user กำลังสลับอยู่
        $canBindCarOrder = Auth::user()->canBindCarOrder((int) $saleCar->brand);

        return view('purchase-order.edit', compact('saleCar', 'model', 'subModels', 'campaigns', 'selected_campaigns', 'reservationPayment', 'remainingPayment', 'deliveryPayment', 'finances', 'conStatus', 'licensePlateRed', 'provinces', 'insurances', 'type', 'typeSale', 'payments', 'userRole', 'isHistory', 'gwmColor', 'interiorColor', 'pricelistRows', 'prefixes', 'tracking', 'extraAbsorbed', 'extraDebtBefore', 'budgetWallet', 'canCustomAccPrice', 'redPlateAccIds', 'canReassignSale', 'saleUser', 'canEditCarPrice', 'canBindCarOrder'));
    }

    /** id ประดับยนต์ที่ถูก mark ว่าเป็น "ป้ายแดง" (ตั้งค่าหลังบ้าน — ข้ามทุก scope เพราะใช้เทียบ id ข้ามแบรนด์) */
    private static function redPlateAccessoryIds(): array
    {
        return AccessoryPrice::withoutGlobalScope('brandAccess')
            ->where('is_red_plate', 1)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all();
    }

    /**
     * ใบขายนี้มีประดับยนต์ "ป้ายแดง" อยู่ไหม
     * ถ้าฟอร์มส่งรายการประดับยนต์มา ให้ยึดตามที่ส่งมา (ค่าที่กำลังจะบันทึก)
     * ถ้าไม่ได้ส่ง (role ที่ไม่มีสิทธิ์แก้ประดับยนต์) ให้ยึดรายการที่บันทึกไว้เดิม
     */
    private function hasRedPlateAccessory(Request $request, Salecar $saleCar): bool
    {
        $redPlateIds = self::redPlateAccessoryIds();
        if (empty($redPlateIds)) {
            return false;
        }

        if ($request->filled('accessories')) {
            $submitted = json_decode($request->input('accessories'), true);
            if (is_array($submitted)) {
                $ids = array_map('intval', array_column($submitted, 'id'));
                return count(array_intersect($ids, $redPlateIds)) > 0;
            }
        }

        return $saleCar->accessories()->whereIn('accessory_price.id', $redPlateIds)->exists();
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


            $saleCar = Salecar::withoutGlobalScope('preApproval')->with('accessories')->findOrFail($id);

            // pre-approval (ขออนุมัติเกินงบล่วงหน้า) ยังไม่ใช่การจอง → ห้ามตั้งสถานะ "ระหว่างแต่งรถ" (4) / "ส่งมอบ" (5)
            // กัน side effect ส่งมอบ (CarOrder=Delivered / ปิด tracking / ส่งอีเมล) หลุดกับรายการที่ยังไม่เป็นการจอง
            if ($saleCar->is_pre_approval && in_array((int) $request->con_status, [4, 5], true)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'คำขออนุมัติล่วงหน้ายังไม่ใช่การจอง — ตั้งสถานะ "ระหว่างแต่งรถ/ส่งมอบ" ไม่ได้',
                ], 422);
            }

            // บังคับกรอกวันส่งมอบให้ครบ เมื่อ:
            //  - ผูกรถแล้ว (CarOrderID) + มีวันที่ PO (remaining_po_date) หรือ
            //  - เปลี่ยนสถานะเป็น "ส่งมอบ" (con_status = 5)
            // (role ที่ผูกรถไม่ได้จะไม่ส่ง CarOrderID มา — ต้อง fallback เป็นค่าเดิมของใบจอง ไม่งั้นเงื่อนไขนี้หลุด)
            if (
                ($request->input('CarOrderID', $saleCar->CarOrderID) && $request->filled('remaining_po_date'))
                || (int) $request->con_status === 5
            ) {
                $requiredDeliveryDates = [
                    'DeliveryDate'         => 'วันส่งมอบจริง (แจ้งประกัน)',
                    'DeliveryInDMSDate'    => 'วันที่ส่งมอบของบริษัท',
                    'DeliveryInCKDate'     => 'วันที่ส่งมอบของฝ่ายขาย',
                    'DeliveryEstimateDate' => 'ประมาณการส่งมอบ',
                ];

                // ประเภทการขาย = Test Drive / Dealer → ไม่บังคับ "วันที่ส่งมอบของบริษัท" (DMS)
                $typeSaleNow = (int) $request->input('type_sale', $saleCar->type_sale);
                if (in_array($typeSaleNow, [Salecar::TYPE_SALE_TEST_DRIVE, Salecar::TYPE_SALE_DEALER], true)) {
                    unset($requiredDeliveryDates['DeliveryInDMSDate']);
                }

                $missingDates = [];
                foreach ($requiredDeliveryDates as $field => $label) {
                    if (!$request->filled($field)) {
                        $missingDates[] = $label;
                    }
                }
                if (!empty($missingDates)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'ผูกรถแล้วและมีวันที่ PO แล้ว กรุณากรอกให้ครบ: ' . implode(', ', $missingDates),
                    ], 422);
                }
            }

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


            // ประเภทการขาย = Dealer → ไม่ต้องขออนุมัติ + ไม่ผูกฝ่ายขาย
            // (เช็คค่าที่กำลังบันทึก เผื่อเพิ่งเปลี่ยนเป็น Dealer)
            $isDealerSale = (int) $request->input('type_sale', $saleCar->type_sale) === Salecar::TYPE_SALE_DEALER;

            $data = [
                // ผู้ขาย: ย้ายได้เฉพาะ role ที่มีสิทธิ์ — role อื่นบังคับใช้ค่าเดิมเสมอ (กันแก้ผ่าน devtools)
                // ขาย Dealer ไม่นับยอด/ไม่คิดคอม → ล้าง SaleID ทิ้งเสมอ (ฟอร์มปิดช่อง จึงไม่ถูกส่งมา)
                'SaleID' => $isDealerSale
                    ? null
                    : (Auth::user()->canReassignSale()
                        ? ($request->filled('SaleID') ? (int) $request->SaleID : $saleCar->SaleID)
                        : $saleCar->SaleID),
                'type' => $request->type,
                'type_sale' => $request->type_sale,
                'model_id' => $request->model_id,
                'subModel_id' => $request->subModel_id,
                // ราคารถ: แก้ได้เฉพาะ role ใน EDIT_CAR_PRICE_ROLES — role อื่นบังคับใช้ค่าเดิมเสมอ (กันแก้ผ่าน devtools)
                'price_sub' => Auth::user()->canEditCarPrice()
                    ? ($request->filled('price_sub') ? str_replace(',', '', $request->price_sub) : null)
                    : $saleCar->price_sub,
                'Color' => $request->Color ?? null,
                'Year' => $request->Year,
                // ผูกรถ: brand 2 ทำได้เฉพาะ md/gm/admin — role อื่นบังคับใช้ค่าเดิมเสมอ (กันแก้ผ่าน devtools)
                'CarOrderID' => Auth::user()->canBindCarOrder((int) $saleCar->brand)
                    ? $request->CarOrderID
                    : $saleCar->CarOrderID,
                'option' => $request->option ?? null,
                'type_color' => $request->type_color ?? null,
                'payment_mode' => $request->payment_mode,
                'CusID' => $request->CusID,
                'FinanceID' => $request->FinanceID,
                'SaleConsultantID' => $request->SaleConsultantID,
                'CashDeposit' => $request->filled('CashDeposit')
                    ? str_replace(',', '', $request->CashDeposit)
                    : null,
                'TurnCarID' => $turnCarID,
                'BookingDate' => $this->toGregorian($request->BookingDate),
                'KeyInDate' => $this->toGregorian($request->KeyInDate),
                'DeliveryDate' => $this->toGregorian($request->DeliveryDate),
                'DeliveryInDMSDate' => $this->toGregorian($request->DeliveryInDMSDate),
                'DeliveryInCKDate' => $this->toGregorian($request->DeliveryInCKDate),
                'RegistrationProvince' => $request->RegistrationProvince,
                'insurance_id' => $request->insurance_id ?: null,
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
                // กรอกบวกหัว (90%) เอง = ล็อกยอดไว้ เปิดใบมาแก้รอบหน้า JS จะไม่คำนวณ 90% ทับ
                // (บางรุ่นใช้บวกหัวเต็ม เช่น Single Cab 2.4 Pro 4WD - LC2TEJUJCRU)
                'markup90_manual' => $request->filled('Markup90')
                    && $request->boolean('markup90_manual'),
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
                'reason_other_cost' => $request->reason_other_cost,
                'other_cost_fi' => $request->filled('other_cost_fi')
                    ? str_replace(',', '', $request->other_cost_fi)
                    : null,
                'reason_other_cost_fi' => $request->reason_other_cost_fi,
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
                'AccessoryGiftVat' => $request->filled('AccessoryGiftVat')
                    ? str_replace(',', '', $request->AccessoryGiftVat)
                    : null,
                'TotalAccessoryExtra' => $request->filled('TotalAccessoryExtra')
                    ? str_replace(',', '', $request->TotalAccessoryExtra)
                    : null,
                'AccessoryExtraCom' => $request->filled('AccessoryExtraCom')
                    ? str_replace(',', '', $request->AccessoryExtraCom)
                    : null,
                'AccessoryExtraVat' => $request->filled('AccessoryExtraVat')
                    ? str_replace(',', '', $request->AccessoryExtraVat)
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
                'budget_deduct' => $request->filled('budget_deduct')
                    ? str_replace(',', '', $request->budget_deduct)
                    : null,
                'ApprovalSignature' => $request->ApprovalSignature,
                'ApprovalSignatureDate' => $this->toGregorian($request->ApprovalSignatureDate),
                'FinanceAmount' => $request->FinanceAmount,
                'InterestRate' => $request->InterestRate,
                'InterestCampaignID' => $request->InterestCampaignID,
                'InstallmentPeriod' => $request->InstallmentPeriod,
                'EXC_ALP' => $request->EXC_ALP,
                'INC_ALP' => $request->INC_ALP,
                'ALPAmount' => $request->ALPAmount,
                'SMSignature' => $request->SMSignature,
                'SMCheckedDate' => $this->toGregorian($request->SMCheckedDate),
                'AdminSignature' => $request->AdminSignature,
                'AdminCheckedDate' => $this->toGregorian($request->AdminCheckedDate),
                'CheckerID' => $request->CheckerID,
                'CheckerCheckedDate' => $this->toGregorian($request->CheckerCheckedDate),
                'GMApprovalSignature' => $request->GMApprovalSignature,
                'GMApprovalSignatureDate' => $this->toGregorian($request->GMApprovalSignatureDate),
                'DeliveryEstimateDate' => $this->toGregorian($request->DeliveryEstimateDate),
                'Note' => $request->Note,
                // ช่องป้ายแดงมีเฉพาะ role ใน RED_PLATE_ROLES — role อื่นไม่ส่งมา ต้องคงค่าเดิม ไม่งั้นบันทึกทีเดียวป้ายหลุด
                'red_license' => $request->has('red_license') ? $request->red_license : $saleCar->red_license,
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
                'delivery_location' => $request->delivery_location,
                'delivery_province' => $request->delivery_province,
            ];

            // ค่าคอมฝ่ายขายที่ผู้จัดการ/GM กรอกตอนอนุมัติเกินเพดาน — แตะเฉพาะเมื่อฟอร์มส่งฟิลด์นี้มาจริง
            // (ช่องกรอกในหน้า edit ถูกปิดไว้ → ถ้าเขียนทับทุกครั้ง ยอดที่อนุมัติแล้วจะโดนล้างเป็น null
            //  แล้วคอมงบเหลือจะตกไปใช้สูตร auto: balance × 2 × per_budget%)
            if ($request->has('approval_commission_deduct')) {
                $data['approval_commission_deduct'] = $request->filled('approval_commission_deduct')
                    ? str_replace(',', '', $request->approval_commission_deduct)
                    : null;

                // admin กรอกยอดให้ใบที่ยังไม่เคยมียอดมาก่อน → ถือเป็นการกรอกตามกติกาใหม่ (= ยอดหัก)
                // ถ้าใบนั้นมียอดเดิมอยู่แล้ว ไม่แตะธง เพื่อไม่ให้ใบเก่า brand 1/3 กลับเครื่องหมาย
                if ($saleCar->approval_commission_deduct === null && $data['approval_commission_deduct'] !== null) {
                    $data['approval_is_deduct'] = true;
                }
            }

            if (in_array(Auth::user()->brand, [2, 3, 4])) {
                $data['gwm_color'] = $request->gwm_color;
            }

            if (BrandFeature::hasInteriorColor()) {
                $data['interior_color'] = $request->interior_color;
            }

            //ดึง id — ใช้ค่าที่ผ่าน gate สิทธิ์แล้ว ไม่ใช่ค่าดิบจาก request
            // (role ที่ผูกรถไม่ได้ ต้องไม่ไปพลิก car_status ของรถคันไหนทั้งนั้น)
            $oldCarOrderID = $saleCar->CarOrderID;
            $newCarOrderID = $data['CarOrderID'];

            // gate: เปลี่ยนสถานะเป็น "ระหว่างแต่งรถ" (con_status = 4) หรือ "ส่งมอบ" (con_status = 5) ได้ต่อเมื่ออนุมัติแล้ว (ยกเว้น admin)
            //  - ผูกรถได้เลยแม้ยังไม่อนุมัติ — บังคับอนุมัติเฉพาะตอนจะเข้าสองสถานะนี้
            //  - ดักเฉพาะ "การเปลี่ยนเข้าสถานะเป้าหมาย" (ของเดิมไม่ใช่สถานะที่กำลังจะเปลี่ยนไป) เพื่อไม่กวนการแก้ฟิลด์อื่นของรายการที่อยู่สถานะนั้นแล้ว
            // ── เปิดตอนปิดยอด: comment block นี้เพื่อปิด gate บังคับอนุมัติ ──
            // ต้องอนุมัติ/เซ็นให้ครบก่อน จึงจะเข้าสถานะ "ระหว่างแต่งรถ" (4) หรือ "ส่งมอบ" (5) ได้
            $enteringApprovalStage = in_array((int) $request->con_status, [4, 5], true)
                && (int) $saleCar->con_status !== (int) $request->con_status;
            // $isDealerSale ประกาศไว้ก่อนสร้าง $data (ใช้ล้าง SaleID ด้วย)
            $prevApprovalType = $saleCar->approval_type;

            $oldPlate = $saleCar->red_license;

            // เปลี่ยนสถานะเป็น "ส่งมอบ" (con_status = 5) ต้องมีป้ายแดง
            // — เฉพาะเมื่อใบขายนี้มีประดับยนต์ "ป้ายแดง" อยู่ (ลูกค้าที่ไม่เอาป้ายแดงส่งมอบได้เลย)
            // ใช้ค่าที่จะบันทึกจริง (ถ้า role ไม่มีช่องป้ายแดงในฟอร์ม → ใช้ค่าเดิมของรายการ)
            $effectiveRedLicense = $request->has('red_license') ? $request->red_license : $saleCar->red_license;
            if ((int) $request->con_status === 5 && empty($effectiveRedLicense) && $this->hasRedPlateAccessory($request, $saleCar)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'ต้องระบุป้ายแดงก่อนเปลี่ยนสถานะเป็น "ส่งมอบ"',
                ], 422);
            }

            // ── ราคารถเปลี่ยน → รีเซ็ตการอนุมัติ ต้องขอใหม่ ──
            // ฐานที่ผู้อนุมัติเห็นตอนเซ็นไม่ใช่ตัวเดิมแล้ว และการแก้ราคาจะล้าง "บวกหัว" ไปด้วย
            // → Markup90 หลุดจาก balanceCampaign → เคสอนุมัติพลิกจากงบปกติเป็นเกินงบได้
            // ไม่มี threshold: ยอดที่คร่อมเส้นแบ่งเคสอยู่ ต่างกันไม่กี่บาทก็พลิกได้
            $priceChanged = (float) ($saleCar->price_sub ?? 0) !== (float) ($data['price_sub'] ?? 0);
            $hasApprovalState = $saleCar->SMSignature
                || $saleCar->ApprovalSignature
                || $saleCar->GMApprovalSignature
                || $saleCar->approval_requested_at;

            if ($priceChanged && $hasApprovalState) {
                $data = array_merge($data, [
                    'SMSignature'                => 0,
                    'SMCheckedDate'              => null,
                    'ApprovalSignature'          => 0,
                    'ApprovalSignatureDate'      => null,
                    'GMApprovalSignature'        => 0,
                    'GMApprovalSignatureDate'    => null,
                    'approval_requested_at'      => null,
                    'approval_token'             => null,   // ลิงก์อนุมัติในเมลเดิมใช้ไม่ได้
                    ...(Salecar::hasFinalTokenColumn() ? ['approval_final_token' => null] : []),
                    'approval_case'              => null,
                    'approval_type'              => null,
                    'approval_remaining'         => null,
                    // ยอดที่ผู้จัดการ/GM กรอกไว้ ตัดสินจากฐานเก่า → ต้องกรอกใหม่ตอนอนุมัติรอบใหม่
                    // extra_budget ด้วย: เป็นหนี้ที่วิ่งไปหักคอมคันอื่นของเซลล์คนเดียวกัน
                    // (ExtraBudgetLedger) ถ้าค้างไว้ = หนี้จากรอบอนุมัติที่ยกเลิกแล้วยังกัดคันอื่นอยู่
                    'approval_commission_deduct' => null,
                    'approval_extra_budget'      => null,
                    'approval_is_vip'            => 0,
                    'approval_is_deduct'         => 0,
                ]);
            }

            $saleCar->update($data);

            // เช็คสิทธิ์ "หลัง" อัปเดตข้อมูล → ใช้ balanceCampaign/รุ่นรถ ค่าล่าสุดเสมอ
            // ดักเคส: อนุมัติงบปกติผ่านแล้ว แต่แก้ข้อมูลจนเกินงบ → ลายเซ็นเดิมใช้ไม่ได้ ต้องขออนุมัติเกินงบก่อน
            if ($enteringApprovalStage && !$isDealerSale && Auth::user()->role !== 'admin') {
                $saleCar->unsetRelation('model'); // model_id อาจเปลี่ยน → อ่าน over_budget ของรุ่นใหม่
                if (!$this->isApproved($saleCar)) {
                    $becameOverBudget = $prevApprovalType === 'normal'
                        && $this->approvalCase($saleCar) !== 'normal';
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => $becameOverBudget
                            ? 'ข้อมูลเปลี่ยนแปลงจนเกินงบ — คำขออนุมัติงบปกติเดิมใช้ไม่ได้แล้ว กรุณาขออนุมัติเกินงบก่อนเปลี่ยนสถานะ'
                            : 'กรุณาขออนุมัติให้ผ่านก่อน จึงจะเปลี่ยนสถานะนี้ได้',
                    ], 422);
                }
            }

            // ค่างวดล่วงหน้า — เก็บลง finances_confirm.advance_installment (ค่าเดียวกัน)
            $advanceInstallment = $request->filled('advance_installment')
                ? str_replace(',', '', $request->advance_installment)
                : null;
            $financeConfirm = FinancesConfirm::withoutGlobalScopes()
                ->firstOrNew(['SaleID' => $saleCar->id]);
            if (!$financeConfirm->exists) {
                $financeConfirm->brand    = $saleCar->brand;
                $financeConfirm->branch   = $saleCar->branch;
                $financeConfirm->userZone = $saleCar->userZone;
            }
            $financeConfirm->advance_installment = $advanceInstallment;
            $financeConfirm->save();

            if ($request->hasFile('attachments')) {
                $customer = Customer::find($saleCar->CusID);
                $customerFolder = $customer->id . '-' . ($customer->FirstName ?? 'unknown');
                $brandName = Auth::user()->brandInfo->name ?? 'Other';
                $folder = "New Car/{$brandName}/หลักฐานการจอง/{$customerFolder}";

                $oneDrive = new OneDriveService();
                $existing = is_array($saleCar->attachment_url) ? $saleCar->attachment_url : [];

                foreach ($request->file('attachments') as $index => $file) {
                    $fileName = 'booking_' . $saleCar->id . '_edit_' . ($index + 1) . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $existing[] = [
                        'url'  => $oneDrive->upload($file->getRealPath(), $fileName, $folder),
                        'name' => $file->getClientOriginalName(),
                    ];
                }

                $saleCar->update(['attachment_url' => $existing]);
            }

            //ยกเลิกการจอง
            if (in_array($request->con_status, [7, 8, 9])) {
                if ($saleCar->CarOrderID) {
                    $carOrderId = $saleCar->CarOrderID;
                    $saleCar->CarOrderID = null; // ปลดใบที่ถอนออกจากรถ กัน CarOrderID ค้าง
                    $saleCar->save();

                    // คืนรถเป็น Available เฉพาะเมื่อไม่มีใบจอง active อื่นผูกรถคันนี้อยู่
                    $stillActive = Salecar::where('CarOrderID', $carOrderId)
                        ->where('id', '!=', $saleCar->id)
                        ->whereNotIn('con_status', [7, 8, 9])
                        ->exists();
                    if (!$stillActive) {
                        CarOrder::updateLogged(
                            fn($q) => $q->whereKey($carOrderId),
                            ['car_status' => 'Available']
                        );
                    }
                }

                $saleCar->carOrderHistories()->delete();
            }

            //เก็บข้อมูลการผูกรถ
            if ($oldCarOrderID != $newCarOrderID && $newCarOrderID) {
                $saleCar->carOrderHistories()->delete();
                CarOrderHistory::create([
                    'SaleID' => $saleCar->id,
                    'CarOrderID' => $newCarOrderID,
                    'BookingDate' => $this->toGregorian($request->BookingDate),
                    'changed_at' => now(),
                    'userZone' => Auth::user()->userZone ?? null,
                    'brand' => Auth::user()->brand ?? null,
                    'branch' => Auth::user()->branch ?? null,
                ]);

                if ($oldCarOrderID) {
                    // คืนรถเก่าเป็น Available เฉพาะเมื่อไม่มีใบจอง active อื่นผูกอยู่
                    $oldStillActive = Salecar::where('CarOrderID', $oldCarOrderID)
                        ->where('id', '!=', $saleCar->id)
                        ->whereNotIn('con_status', [7, 8, 9])
                        ->exists();
                    if (!$oldStillActive) {
                        CarOrder::updateLogged(
                            fn($q) => $q->whereKey($oldCarOrderID),
                            ['car_status' => 'Available']
                        );
                    }
                }
                CarOrder::updateLogged(
                    fn($q) => $q->whereKey($newCarOrderID),
                    ['car_status' => 'Booked']
                );
            }

            //ส่งมอบรถ
            if ($request->con_status == 5) {

                $carOrderToDeliver = $newCarOrderID ?: $oldCarOrderID;
                if ($carOrderToDeliver) {
                    CarOrder::updateLogged(
                        fn($q) => $q->whereKey($carOrderToDeliver),
                        ['car_status' => 'Delivered']
                    );
                }

                // ปิด customer tracking เมื่อส่งมอบรถแล้ว loop เสร็จสมบูรณ์
                if ($saleCar->CusID) {
                    CustomerTracking::where('customer_id', $saleCar->CusID)
                        ->where('brand', $saleCar->brand)
                        ->whereNull('cancelled_at')
                        ->update([
                            'cancelled_at' => now(),
                            'CancelledBy'  => Auth::id(),
                        ]);
                }
            }

            //ป้ายแดง — ใช้ค่าที่จะบันทึกจริง (role ที่ไม่มีช่องนี้ = คงป้ายเดิม ไม่ต้องสลับอะไร)
            $this->syncRedPlate($saleCar, $oldPlate, $data['red_license']);

            // เก็บ snapshot เดิมไว้ก่อน detach — แถวที่ไม่ได้แก้ต้องคงทุนอะไหล่เดิม ไม่ดึงราคาปัจจุบันจาก master มาทับ
            $prevAcc = Saleaccessory::where('salecar_id', $saleCar->id)
                ->get()
                ->keyBy(fn($r) => $r->accessory_id . '|' . $r->type);

            $saleCar->accessories()->detach();

            if ($request->has('accessories')) {
                $accessories = $request->input('accessories');
                if (is_string($accessories)) {
                    $accessories = json_decode($accessories, true);
                }

                if (is_array($accessories)) {
                    $canCustomAcc = self::canSetCustomAccessoryPrice();
                    // ปลด brand scope — อ่านแค่ทุนอะไหล่ของรายการที่ถูกเลือกไว้แล้ว และ id เป็นตัวชี้อยู่แล้ว
                    $accMasters = AccessoryPrice::withoutGlobalScope('brandAccess')
                        ->whereIn('id', array_column($accessories, 'id'))
                        ->get()
                        ->keyBy('id');

                    foreach ($accessories as $a) {
                        $price = isset($a['price']) ? floatval(str_replace(',', '', $a['price'])) : 0;
                        $commission = isset($a['commission']) ? floatval(str_replace(',', '', $a['commission'])) : 0;

                        $isCustom = trim((string) ($a['price_type'] ?? '')) === self::ACC_PRICE_TYPE_CUSTOM;
                        $prevRow = $prevAcc[$a['id'] . '|' . $a['type']] ?? null;
                        $wasCustom = $prevRow && $prevRow->price_type === self::ACC_PRICE_TYPE_CUSTOM;

                        $sentSpare = isset($a['cost_spare']) && $a['cost_spare'] !== '' && $a['cost_spare'] !== null
                            ? floatval(str_replace(',', '', (string) $a['cost_spare']))
                            : null;

                        // ระบุราคาเอง: ต้องมีสิทธิ์ + รายการนั้นต้องติดธง "ราคาไม่คงที่" ไว้ใน master
                        // เช็คเฉพาะตอน "สร้างใหม่หรือแก้ตัวเลข" — แถวเดิมที่ค่าไม่เปลี่ยนต้องบันทึกผ่านได้เสมอ
                        // ไม่งั้นเซลล์จะบันทึกใบที่หัวหน้าตั้งราคาไว้ไม่ได้ และปิดธงทีหลังจะทำให้ใบเก่าเซฟไม่ผ่าน
                        if ($isCustom) {
                            $unchanged = $wasCustom
                                && abs((float) $prevRow->price - $price) < 0.005
                                && abs((float) $prevRow->cost_spare - (float) $sentSpare) < 0.005;

                            $error = null;
                            if (!$unchanged && !self::ACC_CUSTOM_PRICE_ENABLED) {
                                // ฟีเจอร์ถูกปิดแล้ว — ใบเก่าที่ค่าไม่เปลี่ยนยังเซฟผ่าน ($unchanged) แต่ตั้งราคาใหม่ไม่ได้
                                $error = 'ระบบปิดการระบุราคาประดับยนต์เองแล้ว กรุณาเลือกราคาจากตาราง'
                                    . ' (ถ้าราคาต่างจากเดิมให้เพิ่มรายการใหม่ในหน้าประดับยนต์)';
                            } elseif (!$unchanged && !$canCustomAcc) {
                                $error = 'สิทธิ์ของคุณไม่สามารถระบุราคาประดับยนต์เองได้';
                            } elseif (!$unchanged && !($accMasters[$a['id']]->allow_custom_price ?? false)) {
                                $error = 'รายการนี้ไม่ได้เปิดให้ระบุราคาเอง กรุณาเลือกราคาจากตาราง';
                            }

                            if ($error) {
                                DB::rollBack();
                                return response()->json(['success' => false, 'message' => $error], 422);
                            }
                        }

                        // ทุนอะไหล่: snapshot ลงใบขายเสมอ เพื่อให้ GP/ใบขออนุมัติย้อนหลังไม่ขยับตาม master
                        //  - ระบุเอง → ใช้ค่าที่กรอก
                        //  - แถวเดิมที่ไม่ใช่ระบุเอง → คงค่าเดิมของใบนี้ไว้
                        //  - แถวใหม่ / เพิ่งเปลี่ยนจากระบุเองมาเป็นราคาตาราง → ดึงจาก master
                        if ($isCustom) {
                            $costSpare = $sentSpare;
                        } elseif (!$wasCustom && $prevRow && $prevRow->cost_spare !== null) {
                            $costSpare = (float) $prevRow->cost_spare;
                        } else {
                            $costSpare = $accMasters[$a['id']]->cost_spare ?? null;
                        }

                        // หมายเหตุประดับยนต์ (ฟิล์ม = ความเข้ม/ตำแหน่งที่ติด)
                        // บังคับเฉพาะแถวที่เพิ่งเพิ่มใหม่ — แถวเดิมที่บันทึกไว้ก่อนมีฟีเจอร์นี้ต้องแก้ใบต่อได้
                        $note = isset($a['note']) ? trim((string) $a['note']) : '';

                        if ($note === '' && !$prevRow && ($accMasters[$a['id']] ?? null)?->requiresNote()) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => 'กรุณากรอกหมายเหตุของรายการฟิล์ม (เช่น ความเข้ม/ตำแหน่งที่ติด)',
                            ], 422);
                        }

                        $saleCar->accessories()->attach($a['id'], [
                            'price_type' => $a['price_type'],
                            'price' => $price,
                            'commission' => $commission,
                            'cost_spare' => $costSpare,
                            'type' => $a['type'],
                            'note' => $note !== '' ? mb_substr($note, 0, 255) : null,
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
                        'CampaignType' => $campaign->campaign_type,
                        'CashSupport' => $campaign->cashSupport ?? 0,
                        'CashSupportDeduct' => $campaign->cashSupport_deduct ?? 0,
                        'CashSupportFinal' => $campaign->cashSupport_final ?? 0,
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
                    'date' => $this->toGregorian($request->reservation_date),
                    'userZone' => $request->userZone  ?? null,
                    'brand' => Auth::user()->brand ?? null,
                    'branch' => Auth::user()->branch ?? null,
                ];

                $isBrand2 = Auth::user()->brand == 2;

                switch ($request->reservationCondition) {
                    case 'transfer':
                        $data['transfer_bank'] = $request->reservation_transfer_bank;
                        $data['transfer_branch'] = $request->reservation_transfer_branch;
                        $data['transfer_no'] = $request->reservation_transfer_no;
                        $data['danu_date'] = $isBrand2 ? $this->toGregorian($request->danu_date) : null;

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
                        $data['danu_date'] = $isBrand2 ? $this->toGregorian($request->danu_date) : null;

                        $data['transfer_bank'] = null;
                        $data['transfer_branch'] = null;
                        $data['transfer_no'] = null;
                        $data['credit'] = null;
                        $data['tax_credit'] = null;
                        break;

                    case 'credit':
                        $data['credit'] = $request->reservation_credit;
                        $data['tax_credit'] = $request->reservation_tax_credit ? str_replace(',', '', $request->reservation_tax_credit) : null;
                        $data['danu_date'] = null;

                        $data['transfer_bank'] = null;
                        $data['transfer_branch'] = null;
                        $data['transfer_no'] = null;
                        $data['check_bank'] = null;
                        $data['check_branch'] = null;
                        $data['check_no'] = null;
                        break;

                    case 'cash':
                    default:
                        $data['danu_date'] = $isBrand2 ? $this->toGregorian($request->danu_date) : null;

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
                    'date' => $this->toGregorian($request->remaining_date),
                    'userZone' => $request->userZone ?? null,
                    'brand' => Auth::user()->brand ?? null,
                    'branch' => Auth::user()->branch ?? null,
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
                        $data['po_date'] = $this->toGregorian($request->remaining_po_date ?? null);
                        $data['contract_date'] = $this->toGregorian($request->remaining_contract_date ?? null);
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
                    'date' => $this->toGregorian($request->delivery_date),
                    'userZone' => $request->userZone  ?? null,
                    'brand' => Auth::user()->brand ?? null,
                    'branch' => Auth::user()->branch ?? null,
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
                $dates   = array_map(fn($d) => $this->toGregorian($d), $request->payment_date ?? []);

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
            $user = Auth::user();

            // ขออนุมัติ — ส่งหา "ด่านแรก" ตามเคส/brand (manager/gm/md)
            // ประเภทการขาย = Dealer → ข้ามการขออนุมัติทั้งหมด (ไม่ส่งเมล/ไม่ออก token)
            if (!$saleCar->isDealerSale() && in_array($action, ['request_normal', 'request_over', 'request_gm'])) {
                // เก็บไฟล์ที่ผู้ขอแนบลง storage (ไว้ส่งต่อขั้นถัดไป)
                if ($request->hasFile('approval_files')) {
                    $stored = [];
                    foreach ($request->file('approval_files') as $f) {
                        if ($f->isValid()) {
                            $path = $f->store('approval-files/' . $saleCar->id);
                            $stored[] = [
                                'path' => $path,
                                'name' => $f->getClientOriginalName(),
                                'mime' => $f->getMimeType(),
                            ];
                        }
                    }
                    if ($stored) {
                        $saleCar->update(['approval_files' => $stored]);
                    }
                }

                $case      = $this->approvalCase($saleCar);

                // โมดูล "ขออนุมัติเกินงบล่วงหน้า" รับเฉพาะเคสทะลุเพดาน (b1_md) และ brand 2 เกินงบ (b2_gm)
                if ($saleCar->is_pre_approval && !in_array($case, Salecar::PRE_APPROVAL_CASES, true)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'คำขออนุมัติล่วงหน้ารับเฉพาะกรณีเกินงบทะลุเพดาน — รายการนี้ไม่เข้าเงื่อนไข กรุณาใช้หน้าจองปกติ',
                    ], 422);
                }

                // ด่านแรก = ผู้จัดการเสมอ (ทุกแบรนด์/ทุกเคส) — ดู firstApproverRole()
                $stageRole = $this->firstApproverRole($case);

                $mailTo = $this->approverEmails($saleCar->brand, $saleCar->branch, $stageRole);
                if (empty($mailTo)) {
                    $mailTo = $saleCar->brand == 2
                        ? ['SasithornK@chookiat.org']
                        : ['Phung.mitsuchookiatkrabi@gmail.com'];
                }

                $approvalData  = $this->buildApprovalData($saleCar);
                $token         = $saleCar->approval_token ?: Str::random(48);
                $approvalFiles = $this->buildApprovalAttachments($saleCar);

                $update = [
                    'approval_type'         => $case === 'normal' ? 'normal' : 'overbudget',
                    // เคสจริง ณ ตอนยื่นคำขอ — ใช้ตรวจว่า "ข้อมูลเปลี่ยนจนเคสไม่ตรงเดิม" แล้วต้องขอใหม่
                    'approval_case'         => $case,
                    'approval_requested_at' => now(),
                    'approval_remaining'    => $approvalData['remaining'],
                    'approval_token'        => $token,
                ];
                if ($case !== 'normal') {
                    $update['reason_campaign'] = $request->reason_campaign;
                }
                $saleCar->update($update);

                $mailType = $case === 'normal' ? 'normal' : 'manager';
                // CC ประจำแบรนด์ทุกคำขออนุมัติ (brand 1/3/4 → daw)
                $mailCc = $this->requestCc($saleCar->brand, (array) $mailTo);
                Mail::to($mailTo)->cc($mailCc)->send(new SaleRequestMail($saleCar, $mailType, $approvalData, $approvalFiles));
            }

            DB::commit();

            // แจ้งอีเมล "ส่งมอบ" — ยิง "ครั้งเดียว" เมื่อมีข้อมูลส่งมอบตัวใดตัวหนึ่ง
            //  trigger: DeliveryDate / DeliveryInDMSDate / DeliveryInCKDate / con_status=5
            //  ถ้าตัวถัดมามีข้อมูลตามมาทีหลังจะไม่ยิงซ้ำ (กันด้วย delivered_notified_at)
            if (!$saleCar->delivered_notified_at) {
                $deliveryTriggers = [];
                if ((int) $saleCar->con_status === 5) $deliveryTriggers[] = 'สถานะ = ส่งมอบ';
                if ($saleCar->DeliveryDate)           $deliveryTriggers[] = 'วันส่งมอบจริง (แจ้งประกัน)';
                if ($saleCar->DeliveryInDMSDate)      $deliveryTriggers[] = 'วันส่งมอบของบริษัท (DMS)';
                if ($saleCar->DeliveryInCKDate)       $deliveryTriggers[] = 'วันส่งมอบของฝ่ายขาย (CK)';

                if (!empty($deliveryTriggers)) {
                    try {
                        $saleCar->load([
                            'customer.prefix', 'model', 'subModel', 'carOrder',
                            'saleUser.branchInfo', 'gwmColor', 'interiorColor', 'conStatus',
                            'remainingPayment.financeInfo', // ชื่อไฟแนนซ์ในเมล
                        ]);
                        Mail::to('waliwan.mitsuchookiatkrabi@gmail.com')->send(new CarDeliveredMail($saleCar, $deliveryTriggers));
                        $saleCar->update(['delivered_notified_at' => now()]); // มาร์คว่าแจ้งแล้ว (ยิงครั้งเดียว)
                    } catch (\Throwable $mailEx) {
                        report($mailEx); // ส่งเมลล้มเหลวไม่ควรทำให้การบันทึกล้มเหลว (จะลองใหม่รอบหน้า)
                    }
                }
            }

            // คำขออนุมัติล่วงหน้า (ยังไม่เป็นการจอง) → กลับหน้าโมดูลของมัน ไม่ใช่รายการจอง
            return response()->json([
                'success'  => true,
                'message'  => 'บันทึกข้อมูลเรียบร้อยแล้ว',
                'redirect' => $saleCar->is_pre_approval
                    ? route('pre-approval.index')
                    : route('purchase-order.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // report($e); // เปิดบรรทัดนี้เพื่อเขียน exception จริง + stack trace ลง storage/logs/laravel.log เวลาต้องวินิจฉัย
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

    /**
     * ป้ายแดงที่เลือกได้สำหรับใบจองนี้
     * = ป้ายของแบรนด์ที่ขาย (ไม่ติดให้แบรนด์อื่นยืม) + ป้ายที่ยืมมา (loan ค้าง)
     * และต้องว่าง (is_used = 0) ไม่ใช่ป้ายสูญหาย/ชำรุด/ระหว่างติดตาม
     * — ป้ายที่ใบนี้เลือกไว้แล้วต้องติดมาเสมอ ไม่งั้นหลุดจาก dropdown
     */
    private function availableRedPlates(Salecar $saleCar)
    {
        $plateBrand = Auth::user()->brand ?: $saleCar->brand;

        return TbLicensePlate::withoutGlobalScope('brandAccess')
            ->where(function ($q) use ($saleCar, $plateBrand) {
                $q->where(function ($qq) use ($plateBrand) {
                    $qq->where('brand', $plateBrand)
                        ->whereDoesntHave('loans', fn($l) => $l->whereNull('return_date'));
                })
                    ->orWhereHas('loans', fn($l) => $l->whereNull('return_date')->where('borrower_brand', $plateBrand))
                    ->orWhere('id', $saleCar->red_license);
            })
            ->where(function ($q) use ($saleCar) {
                $q->where('is_used', 0)
                    ->orWhere('id', $saleCar->red_license);
            })
            ->where(function ($q) use ($saleCar) {
                $q->whereNull('plate_status')
                    ->orWhereNotIn('plate_status', TbLicensePlate::BLOCKED_STATUSES)
                    ->orWhere('id', $saleCar->red_license);
            })
            ->orderBy('number')
            ->get();
    }

    /**
     * สลับป้ายแดงของใบจอง — ปลด is_used ป้ายเดิม, จอง is_used ป้ายใหม่ + ลง history
     * ไม่แตะ salecars.red_license (คนเรียกเป็นคนเซ็ต) ; ต้องเรียกภายใน transaction
     */
    private function syncRedPlate(Salecar $saleCar, $oldPlate, $newPlate): void
    {
        if ($oldPlate == $newPlate) {
            return;
        }

        // ปลด/ผูก is_used ด้วย id ตรง ๆ — ข้าม brand scope กันเคสป้ายหลุดจากการมองเห็น
        if ($oldPlate) {
            TbLicensePlate::withoutGlobalScope('brandAccess')
                ->where('id', $oldPlate)
                ->update(['is_used' => 0]);
        }

        if ($newPlate) {
            TbLicensePlate::withoutGlobalScope('brandAccess')
                ->where('id', $newPlate)
                ->update(['is_used' => 1]);

            LicensePlateHistory::create([
                'saleID' => $saleCar->id,
                'licenseID' => $newPlate,
                'date' => now(),
                'UserInsert' => Auth::id(),
                'userZone' => Auth::user()->userZone ?? null,
                'brand' => Auth::user()->brand ?? null,
                'branch' => Auth::user()->branch ?? null,
            ]);
        }
    }

    /**
     * ฟอร์มใส่ป้ายแดงจากหน้าประวัติ (ส่งมอบแล้ว)
     * มีเคสได้ป้ายมาทีหลังวันส่งมอบ — เลยต้องแก้ได้โดยไม่ต้องดึงใบกลับมาแก้ทั้งใบ
     */
    public function redPlateForm($id)
    {
        if (!Auth::user()->canManageRedPlate()) {
            abort(403);
        }

        $saleCar = Salecar::with(['customer.prefix', 'carOrder', 'licensePlateRed'])->findOrFail($id);
        $licensePlateRed = $this->availableRedPlates($saleCar);

        return view('purchase-order.history.red-plate', compact('saleCar', 'licensePlateRed'));
    }

    /** บันทึกป้ายแดงจากหน้าประวัติ — แตะเฉพาะ red_license ไม่ยุ่งกับฟิลด์อื่นของใบจอง */
    public function updateRedPlate(Request $request, $id)
    {
        if (!Auth::user()->canManageRedPlate()) {
            return response()->json(['success' => false, 'message' => 'คุณไม่มีสิทธิ์แก้ไขป้ายแดง'], 403);
        }

        $request->validate([
            'red_license' => ['nullable', Rule::exists('tb_license_plate', 'id')],
        ], [
            'red_license.exists' => 'ไม่พบป้ายแดงที่เลือก',
        ]);

        try {
            $saleCar = Salecar::findOrFail($id);
            $newPlate = $request->red_license ?: null;
            $oldPlate = $saleCar->red_license;

            // ป้ายที่ถูกใบอื่นจองไปแล้ว / สถานะใช้ไม่ได้ — กันเคสสองคนเปิดหน้าค้างไว้แล้วกดพร้อมกัน
            if ($newPlate && $newPlate != $oldPlate) {
                $plate = TbLicensePlate::withoutGlobalScope('brandAccess')->find($newPlate);
                if ($plate->is_used || in_array($plate->plate_status, TbLicensePlate::BLOCKED_STATUSES, true)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'ป้ายแดงนี้ถูกใช้งานอยู่แล้ว หรือมีสถานะที่ใช้ไม่ได้ กรุณาเลือกป้ายอื่น',
                    ], 422);
                }
            }

            DB::transaction(function () use ($saleCar, $oldPlate, $newPlate) {
                $this->syncRedPlate($saleCar, $oldPlate, $newPlate);
                $saleCar->red_license = $newPlate;
                $saleCar->save();
            });

            return response()->json([
                'success' => true,
                'message' => $newPlate ? 'บันทึกป้ายแดงเรียบร้อยแล้ว' : 'นำป้ายแดงออกเรียบร้อยแล้ว',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    //ยกเลิกการผูกรถ
    public function cancelCarOrder(Request $request, $id)
    {
        // brand 2 ปลดรถได้เฉพาะ md/gm/admin (ปุ่มถูกซ่อนอยู่แล้ว ตรงนี้กันยิง endpoint ตรง)
        $sale = Salecar::findOrFail($id);
        if (!Auth::user()->canBindCarOrder((int) $sale->brand)) {
            return response()->json([
                'success' => false,
                'message' => 'คุณไม่มีสิทธิ์ยกเลิกการผูกรถของแบรนด์นี้',
            ], 403);
        }

        DB::transaction(function () use ($id, $request) {

            $sale = Salecar::findOrFail($id);

            // ป้องกันกรณีไม่มี car order
            if (!$sale->CarOrderID) {
                throw new \Exception('ไม่พบข้อมูลการผูกรถ');
            }

            $carOrder = CarOrder::findOrFail($sale->CarOrderID);

            $sale->carOrderHistories()->delete();

            $carOrderId = $sale->CarOrderID;
            $sale->CarOrderID = null;
            $sale->save();

            // คืนรถเป็น Available เฉพาะเมื่อไม่มีใบจอง active อื่นผูกรถคันนี้อยู่
            $stillActive = Salecar::where('CarOrderID', $carOrderId)
                ->where('id', '!=', $sale->id)
                ->whereNotIn('con_status', [7, 8, 9])
                ->exists();
            if (!$stillActive) {
                $carOrder->car_status = 'Available';
                $carOrder->save();
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'ยกเลิกการผูกรถเรียบร้อยแล้ว'
        ]);
    }

    function destroy(Request $request, $id)
    {
        // ซ่อนปุ่มอย่างเดียวไม่พอ — endpoint นี้ยิงตรงได้ ต้องกันที่ server ด้วย
        if (!self::canWithdrawBooking()) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่มีสิทธิ์ถอนจอง',
            ], 403);
        }

        try {
            $saleCar = Salecar::findOrFail($id);

            if ($saleCar->CarOrderID) {
                $carOrderId = $saleCar->CarOrderID;
                $saleCar->CarOrderID = null; // ปลดใบที่ถอนออกจากรถ กัน CarOrderID ค้าง

                // คืนรถเป็น Available เฉพาะเมื่อไม่มีใบจอง active อื่นผูกรถคันนี้อยู่
                $stillActive = Salecar::where('CarOrderID', $carOrderId)
                    ->where('id', '!=', $saleCar->id)
                    ->whereNotIn('con_status', [7, 8, 9])
                    ->exists();
                if (!$stillActive) {
                    CarOrder::updateLogged(
                        fn($q) => $q->whereKey($carOrderId),
                        ['car_status' => 'Available']
                    );
                }

                $saleCar->carOrderHistories()->delete();
            }

            $saleCar->CancelGCIPDate = $this->toGregorian($request->cancel_gcip_date);
            $saleCar->con_status = 9;
            $saleCar->save();

            return response()->json([
                'success' => true,
                'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
        }
    }

    // ดึงคำขออนุมัติกลับ (เฉพาะ admin) — ใช้ตอนส่งคำขอไปแล้วแต่ข้อมูลผิด อยากแก้ก่อนถูกอนุมัติ
    //  - กันเคสที่อนุมัติไปแล้ว (มีลายเซ็นตามเคส) → ดึงกลับไม่ได้
    //  - เคลียร์สถานะคำขอ + ล้าง token เพื่อให้ลิงก์อนุมัติในอีเมลเดิมใช้ไม่ได้
    public function withdrawApproval($id)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $saleCar = Salecar::withoutGlobalScope('preApproval')->findOrFail($id);

        if (!$saleCar->approval_requested_at) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่มีคำขอที่รออนุมัติอยู่',
            ], 422);
        }

        // 2026-08-31 : เดิมบล็อกใบที่อนุมัติแล้ว — เปิดให้ดึงกลับได้ทุกสถานะตามที่ขอ
        // เพราะบางเคส GM/MD อนุมัติไปแล้วค่อยเจอว่าข้อมูลผิด ต้องถอยมาขออนุมัติใหม่
        $wasApproved = $this->isApproved($saleCar);

        // ล้างลายเซ็นทุกขั้นด้วย ไม่งั้นใบจะค้างสถานะ "อนุมัติแล้ว" ทั้งที่ไม่มีคำขอ
        // (ชุดเดียวกับตอน "ตีกลับ" — เริ่มรอบใหม่ ผู้จัดการเลือก VIP/กรอกยอดใหม่ได้)
        $saleCar->update([
            'approval_requested_at'      => null,
            'approval_token'             => null,
            ...(Salecar::hasFinalTokenColumn() ? ['approval_final_token' => null] : []),
            'approval_case'              => null,
            'approval_type'              => null,
            'approval_remaining'         => null,
            'SMSignature'                => 0,
            'SMCheckedDate'              => null,
            'ApprovalSignature'          => 0,
            'ApprovalSignatureDate'      => null,
            'GMApprovalSignature'        => 0,
            'GMApprovalSignatureDate'    => null,
            'approval_commission_deduct' => null,
            'approval_is_vip'            => 0,
            'approval_is_deduct'         => 0,
            'approval_md_note'           => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => $wasApproved
                ? 'ดึงคำขอกลับแล้ว — ลายเซ็นอนุมัติถูกล้าง ต้องยื่นขออนุมัติใหม่'
                : 'ดึงคำขอกลับเรียบร้อยแล้ว',
        ]);
    }

    public function summaryPurchase($id)
    {
        $saleCar = Salecar::withoutGlobalScope('preApproval')->with(['customer.prefix', 'model', 'carOrder', 'campaigns.campaign.type', 'campaigns.campaign.appellation', 'reservationPayment', 'remainingPayment.financeInfo', 'deliveryPayment', 'turnCar', 'provinces'])->findOrFail($id);
        $model = TbCarmodel::all();

        $pdf = Pdf::loadView('purchase-order.report.summary', compact('saleCar', 'model'))
            ->setPaper('A4', 'portrait');

        $filename = 'purchase-order_' . $saleCar->id . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->stream($filename);
    }

    // ดูรายละเอียด (PDF สรุปการขาย) จากลิงก์ในเมล — ไม่ต้อง login, unscoped (เปิดข้าม brand ได้), read-only
    public function emailSummary($token)
    {
        // เปิดผ่านลิงก์ในเมล — ผู้กดอาจล็อกอินคนละ brand → ปิด BrandScope ทั้ง request
        ScopeBypass::$brand = true;

        $saleCar = Salecar::withoutGlobalScopes()
            ->with(['customer.prefix', 'model', 'carOrder', 'campaigns.campaign.type', 'campaigns.campaign.appellation', 'reservationPayment', 'remainingPayment.financeInfo', 'deliveryPayment', 'turnCar', 'provinces'])
            ->where('approval_token', $token)
            ->firstOrFail();

        $model = TbCarmodel::withoutGlobalScopes()->get();

        $pdf = Pdf::loadView('purchase-order.report.summary', compact('saleCar', 'model'))
            ->setPaper('A4', 'portrait');

        $filename = 'purchase-order_' . $saleCar->id . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->stream($filename);
    }

    public function bookingPdf($id)
    {
        // ใบจองสำหรับลูกค้า — ใช้ข้อมูลตอนทำการจอง
        $saleCar = Salecar::with([
            'customer.prefix',
            'customer.currentAddress',
            'customer.documentAddress',
            'model',
            'subModel',
            'carOrder',
            'reservationPayment',
            'saleUser',
            'interiorColor',
        ])->findOrFail($id);

        // หัวบริษัท + โลโก้ตาม brand (default + override รายแบรนด์)
        $company = array_merge(
            config('company.default', []),
            config('company.brands.' . $saleCar->brand, [])
        );

        $pdf = Pdf::loadView('purchase-order.report.booking', compact('saleCar', 'company'))
            ->setPaper('A4', 'portrait')
            ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        $filename = 'booking_' . $saleCar->id . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->stream($filename);
    }

    public function preview($id)
    {
        $saleCar = Salecar::withoutGlobalScope('preApproval')->with(['customer.prefix', 'turnCar', 'accessories', 'model', 'carOrder', 'campaigns', 'remainingPayment.financeInfo'])->findOrFail($id);
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
                // '.' มี precedence สูงกว่า '??' โค้ดเดิมจึงคืนแค่คำนำหน้า ทำให้ค้นหาชื่อในตารางไม่เจอ
                'FullName' => trim(implode(' ', array_filter([
                    $c?->prefix?->Name_TH,
                    $c?->FirstName,
                    $c?->LastName,
                ]))),
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
                // '.' มี precedence สูงกว่า '??' โค้ดเดิมจึงคืนแค่คำนำหน้า ทำให้ค้นหาชื่อในตารางไม่เจอ
                'FullName' => trim(implode(' ', array_filter([
                    $c?->prefix?->Name_TH,
                    $c?->FirstName,
                    $c?->LastName,
                ]))),
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

    public function changeBuyer(Request $request, $id)
    {
        $salecar = Salecar::findOrFail($id);

        if (!$salecar->original_customer_id) {
            $salecar->original_customer_id = $salecar->CusID;
            $salecar->original_tracking_id = $salecar->tracking_id;
        }

        $request->validate([
            'new_customer_id' => 'required|integer',
            'new_tracking_id' => 'required|integer',
        ]);

        $salecar->CusID = $request->new_customer_id;
        $salecar->tracking_id = $request->new_tracking_id;
        $salecar->save();

        return response()->json(['success' => true]);
    }

    public function getCustomerTrackings(Request $request)
    {
        $customerId = $request->customer_id;
        $trackings = CustomerTracking::where('customer_id', $customerId)
            ->whereNull('cancelled_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($t) => [
                'id'    => $t->id,
                'label' => 'Tracking #' . $t->id . ($t->customer_date ? ' (' . $t->customer_date . ')' : ''),
            ]);

        return response()->json($trackings);
    }

    /**
     * เช็คสถานะการติดตามของลูกค้า ก่อนอนุญาตให้เพิ่มการจองจากหน้านี้ (scope: brand เดียวกับผู้ใช้)
     *  - has_active_booking : มีใบจอง active (con_status 1-4,6) → เพิ่มซ้ำได้แต่ต้องยืนยันก่อน
     *  - open_tracking      : ยังอยู่ในลิสต์ติดตาม (cancelled_at IS NULL) → ให้ไปจองผ่านหน้าการติดตาม
     *  - ok                 : เคยมีการติดตามแต่ปิดแล้ว (ส่งมอบ/ยกเลิก/ถอนจอง) → จองใหม่ได้เลย
     *  - no_tracking        : ไม่เคยมีการติดตาม (ลูกค้าใหม่) → ต้องเพิ่มการติดตามก่อน
     */
    public function checkCustomerTracking(Request $request)
    {
        $customerId = $request->customer_id;
        $brand      = Auth::user()->brand;

        if (!$customerId) {
            return response()->json(['status' => 'no_tracking']);
        }

        // 1) มีใบจองที่ยังดำเนินการอยู่ (con_status ไม่ใช่ 5,7,8,9 = ยังไม่จบ)
        $hasActiveBooking = Salecar::withoutGlobalScopes(['userAccess', 'saleTeam'])
            ->where('CusID', $customerId)
            ->where('brand', $brand)
            ->whereNotIn('con_status', [5, 7, 8, 9])
            ->exists();

        if ($hasActiveBooking) {
            return response()->json(['status' => 'has_active_booking']);
        }

        // 2) ยังอยู่ในลิสต์ติดตาม (ยังไม่ปิด) → ต้องจองผ่านหน้าการติดตาม
        $openTracking = CustomerTracking::withoutGlobalScopes(['userAccess', 'saleTeam'])
            ->where('customer_id', $customerId)
            ->where('brand', $brand)
            ->whereNull('cancelled_at')
            ->orderByDesc('created_at')
            ->first();

        if ($openTracking) {
            return response()->json([
                'status'      => 'open_tracking',
                'tracking_id' => $openTracking->id,
            ]);
        }

        // 3) เคยมีการติดตามแต่ปิดแล้ว → จองใหม่ได้เลย
        $hasAnyTracking = CustomerTracking::withoutGlobalScopes(['userAccess', 'saleTeam'])
            ->where('customer_id', $customerId)
            ->where('brand', $brand)
            ->exists();

        if ($hasAnyTracking) {
            return response()->json(['status' => 'ok']);
        }

        // 4) ไม่เคยมีการติดตาม → ลูกค้าใหม่ ต้องเพิ่มการติดตามก่อน
        return response()->json(['status' => 'no_tracking']);
    }

    // คืนรายการข้อมูลที่ลูกค้ายังขาดสำหรับทำการจอง (ว่าง = ครบ) — ใช้ร่วมกันทั้ง gate หน้าจอและ store()
    private function customerProfileMissing(?Customer $customer): array
    {
        if (!$customer) {
            return ['ไม่พบข้อมูลลูกค้า'];
        }

        $addr = Address::where('customer_id', $customer->id)
            ->where('type', 'current')
            ->orderByDesc('id')
            ->first();

        $missing = [];
        if (empty($customer->IDNumber))      $missing[] = 'เลขบัตรประชาชน';
        if (empty($customer->Mobilephone1))  $missing[] = 'เบอร์โทรศัพท์';
        if (!($addr && !empty($addr->province) && !empty($addr->district) && !empty($addr->subdistrict))) {
            $missing[] = 'ที่อยู่ปัจจุบัน';
        }

        return $missing;
    }

    /**
     * ข้อมูลย่อของ "ลูกค้าที่ถือค่าซ้ำอยู่" (เลขบัตร/เบอร์) — ส่งกลับไปให้หน้าจอชี้ตัวได้ว่าชนกับใคร
     * customers เป็นข้อมูลกลาง ไม่มี BrandScope เจ้าของอาจอยู่คนละแบรนด์/สาขา จึงบอกแบรนด์ไปด้วย
     */
    private function duplicateOwnerPayload(Customer $c): array
    {
        $authBrand = (int) Auth::user()->brand;

        return [
            'id'         => $c->id,
            'name'       => trim(($c->prefix->Name_TH ?? '') . ' ' . $c->FirstName . ' ' . ($c->LastName ?? '')),
            'id_number'  => $c->formatted_id_number,
            'mobile'     => $c->formatted_mobile,
            'mobile2'    => $c->Mobilephone2,
            'brand'      => (int) $c->brand,
            'brand_name' => config('brand.names')[$c->brand] ?? ('Brand ' . $c->brand),
            'same_brand' => (int) $c->brand === $authBrand,
            'branch'     => $c->branch,
            'deleted'    => $c->trashed(),
            'created_at' => optional($c->created_at)->format('d/m/Y'),
            // การติดตาม/ใบจองที่ยังเปิดอยู่ (ทุกแบรนด์ — เพื่อบอกภาพรวมว่าลูกค้ารายนี้ยัง active ที่ไหน)
            'has_tracking' => CustomerTracking::withoutGlobalScopes()
                ->whereNull('deleted_at')->whereNull('cancelled_at')
                ->where('customer_id', $c->id)->exists(),
            'has_booking'  => Salecar::withoutGlobalScopes()
                ->whereNull('deleted_at')->whereNull('CancelDate')
                ->where('CusID', $c->id)->exists(),
        ];
    }

    /**
     * รวมลูกค้าซ้ำ: ย้ายทุกอย่างของ $from (แถวที่เพิ่งสร้างจากการติดตาม) ไปหา $target (แถวเดิมที่ถือเลขบัตร)
     * แล้ว soft-delete $from — ใช้ตอนกรอกเลขบัตรในหน้าจองแล้วชนกับลูกค้าเดิมที่เป็นคนเดียวกัน
     *
     * เงื่อนไขกันพลาด: $target ต้องถือเลขบัตรที่ส่งมาจริง ๆ (ยืนยันว่าเป็นคนเดียวกัน) และต้องไม่ถูกลบ
     * เบอร์ของ $from ย้ายไปช่อง Mobilephone2 เพราะ Mobilephone1 มี unique index (soft delete ไม่ปลดล็อกดัชนี)
     */
    public function mergeCustomer(Request $request)
    {
        $request->validate([
            'customer_id'        => 'required|integer|exists:customers,id',
            'target_customer_id' => 'required|integer|exists:customers,id',
            'IDNumber'           => 'required|string',
        ]);

        $idNumber = Customer::normalizeIdNumber($request->IDNumber);

        if ((int) $request->customer_id === (int) $request->target_customer_id) {
            return response()->json(['success' => false, 'message' => 'เป็นลูกค้ารายเดียวกันอยู่แล้ว'], 422);
        }

        $from   = Customer::find($request->customer_id);
        $target = Customer::find($request->target_customer_id);

        if (!$from || !$target) {
            return response()->json(['success' => false, 'message' => 'ไม่พบข้อมูลลูกค้า (อาจถูกลบไปแล้ว)'], 422);
        }

        if (Customer::normalizeIdNumber($target->IDNumber) !== $idNumber) {
            return response()->json([
                'success' => false,
                'message' => 'เลขบัตรของลูกค้าปลายทางไม่ตรงกับที่กรอก — ยกเลิกการรวมเพื่อความปลอดภัย',
            ], 422);
        }

        $notes = [];

        DB::beginTransaction();
        try {
            $fromPhone = $from->Mobilephone1;

            // เบอร์เดิมของแถวที่จะถูกลบ — เก็บไว้เป็นเบอร์สำรองของลูกค้าปลายทาง ถ้ายังไม่ซ้ำกับที่มีอยู่
            if ($fromPhone && $fromPhone !== $target->Mobilephone1 && $fromPhone !== $target->Mobilephone2) {
                if (empty($target->Mobilephone2)) {
                    $target->Mobilephone2 = $fromPhone;
                    $notes[] = 'เก็บเบอร์ ' . $fromPhone . ' เป็นเบอร์สำรอง';
                } else {
                    $notes[] = 'เบอร์ ' . $fromPhone . ' ไม่ได้เก็บ (ช่องเบอร์สำรองมีข้อมูลอยู่แล้ว)';
                }
            }

            // เติมเฉพาะช่องที่ปลายทางว่าง — ไม่ทับข้อมูลเดิมที่ยืนยันแล้ว
            foreach (['PrefixName' => $from->PrefixName, 'FirstName' => $from->FirstName, 'LastName' => $from->LastName] as $col => $val) {
                if (empty($target->$col) && !empty($val)) {
                    $target->$col = $val;
                }
            }
            $target->save();

            // ย้ายสิ่งที่ผูกกับลูกค้าเก่า (ตารางที่อ้าง customer: address / customer_trackings / salecars / service_check_trackings)
            $movedTracking = CustomerTracking::withoutGlobalScopes()
                ->where('customer_id', $from->id)->update(['customer_id' => $target->id]);

            $movedBooking = Salecar::withoutGlobalScopes()
                ->where('CusID', $from->id)->update(['CusID' => $target->id]);

            // อ้างอิงอื่นบนใบจองที่ชี้มาที่แถวเก่า — ย้ายด้วยไม่งั้นจะชี้ไปแถวที่ถูกลบ
            Salecar::withoutGlobalScopes()
                ->where('original_customer_id', $from->id)->update(['original_customer_id' => $target->id]);
            Salecar::withoutGlobalScopes()
                ->where('ReferrerID', $from->id)->update(['ReferrerID' => $target->id]);

            DB::table('service_check_trackings')->where('customer_id', $from->id)->update(['customer_id' => $target->id]);

            // ที่อยู่: ย้ายเฉพาะประเภทที่ปลายทางยังไม่มี — กันที่อยู่ซ้ำซ้อนบนลูกค้าคนเดียว
            foreach (['current', 'document'] as $type) {
                $targetHas = Address::where('customer_id', $target->id)->where('type', $type)->exists();
                if ($targetHas) continue;

                $addr = Address::where('customer_id', $from->id)->where('type', $type)->orderByDesc('id')->first();
                if ($addr) {
                    $addr->customer_id = $target->id;
                    $addr->save();
                    $notes[] = 'ย้ายที่อยู่ (' . $type . ')';
                }
            }

            // ที่อยู่ปัจจุบันยังขาด แต่ผู้ใช้เพิ่งกรอกมาในฟอร์ม → สร้างให้เลย
            $needAddress = !Address::where('customer_id', $target->id)->where('type', 'current')->exists();
            if ($needAddress && $request->filled(['house_number', 'province', 'district', 'subdistrict'])) {
                $authUser = Auth::user();
                $addrData = [
                    'house_number' => $request->house_number,
                    'group'        => $request->group,
                    'village'      => $request->village,
                    'alley'        => $request->alley,
                    'road'         => $request->road,
                    'subdistrict'  => $request->subdistrict,
                    'district'     => $request->district,
                    'province'     => $request->province,
                    'postal_code'  => $request->postal_code,
                    'post_id'      => $request->post_id ?: null,
                    'userZone'     => $target->userZone ?? $authUser->userZone,
                    'brand'        => $target->brand ?? $authUser->brand,
                    'branch'       => $target->branch ?? $authUser->branch,
                ];
                Address::create(['customer_id' => $target->id, 'type' => 'current'] + $addrData);
                if (!Address::where('customer_id', $target->id)->where('type', 'document')->exists()) {
                    Address::create(['customer_id' => $target->id, 'type' => 'document'] + $addrData);
                }
                $notes[] = 'บันทึกที่อยู่ที่กรอกใหม่';
            }

            $from->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'รวมข้อมูลไม่สำเร็จ กรุณาลองใหม่'], 500);
        }

        $target->refresh()->load('prefix');
        $missing = $this->customerProfileMissing($target);

        return response()->json([
            'success'     => true,
            'customer_id' => $target->id,
            'name'        => trim(($target->prefix->Name_TH ?? '') . ' ' . $target->FirstName . ' ' . ($target->LastName ?? '')),
            'id_number'   => $target->formatted_id_number,
            'mobile'      => $target->formatted_mobile,
            'complete'    => empty($missing),
            'missing'     => $missing,
            'moved'       => ['tracking' => $movedTracking, 'booking' => $movedBooking],
            'notes'       => $notes,
        ]);
    }

    // ตรวจว่าลูกค้ามีข้อมูลครบก่อนทำการจอง: เลขบัตร + เบอร์โทร + ที่อยู่ปัจจุบัน (จังหวัด/อำเภอ/ตำบล)
    public function customerProfile(Request $request)
    {
        $customer = Customer::find($request->customer_id);
        if (!$customer) {
            return response()->json(['found' => false], 404);
        }

        $addr = Address::where('customer_id', $customer->id)
            ->where('type', 'current')
            ->orderByDesc('id')
            ->first();

        $missing = $this->customerProfileMissing($customer);

        return response()->json([
            'found'         => true,
            'complete'      => empty($missing),
            'missing'       => $missing,
            'prefix_id'     => $customer->PrefixName,
            'first_name'    => $customer->FirstName,
            'last_name'     => $customer->LastName,
            'original_name' => $customer->OriginalName,
            'id_number'     => $customer->IDNumber,
            'mobile'        => $customer->Mobilephone1,
            'address'   => $addr ? [
                'house_number' => $addr->house_number,
                'group'        => $addr->group,
                'village'      => $addr->village,
                'alley'        => $addr->alley,
                'road'         => $addr->road,
                'province'     => $addr->province,
                'district'     => $addr->district,
                'subdistrict'  => $addr->subdistrict,
                'postal_code'  => $addr->postal_code,
                'post_id'      => $addr->post_id,
            ] : null,
        ]);
    }

    // บันทึกข้อมูลที่ขาด (เลขบัตร/เบอร์โทร/ที่อยู่ปัจจุบัน) จาก modal หน้าจอง
    public function saveCustomerProfile(Request $request)
    {
        $request->validate([
            'customer_id'  => 'required|integer|exists:customers,id',
            'PrefixName'   => 'nullable|integer|exists:tb_prefixname,id',
            'FirstName'    => 'required|string|max:100',
            'LastName'     => 'nullable|string|max:100',
            'IDNumber'     => 'required|string',
            'Mobilephone1' => 'required|string',
            'house_number' => 'required|string|max:100',
            'province'     => 'required|string|max:100',
            'district'     => 'required|string|max:100',
            'subdistrict'  => 'required|string|max:100',
        ]);

        $authUser = Auth::user();
        $idNumber = Customer::normalizeIdNumber($request->IDNumber);
        $mobile   = preg_replace('/\D/', '', $request->Mobilephone1);

        // รับได้ 2 แบบ: บัตรประชาชนไทย 13 หลัก หรือพาสปอร์ตต่างชาติที่มีตัวอักษรปน
        // (เดิมบังคับ 13 หลักอย่างเดียว ลูกค้าต่างชาติเลยออกใบจองไม่ได้)
        $isThaiId   = (bool) preg_match('/^\d{13}$/', (string) $idNumber);
        $isPassport = (bool) preg_match('/^(?=.*[A-Z])[A-Z0-9]{6,17}$/', (string) $idNumber);

        if (!$isThaiId && !$isPassport) {
            return response()->json([
                'success' => false,
                'message' => 'เลขบัตรประชาชนต้องมี 13 หลัก หรือกรอกเลขพาสปอร์ต (ตัวอักษรผสมตัวเลข 6-17 ตัว)'
            ], 422);
        }

        // ชนเลขบัตร = เป็นคนเดียวกัน → ต้องบอกว่าชนกับใคร ไม่ใช่ปิดประตูเฉย ๆ
        // (ลูกค้าส่วนใหญ่เข้าระบบทางการติดตามซึ่งยังไม่มีเลขบัตร คนเดิมที่มาด้วยเบอร์ใหม่จึงกลายเป็นอีกแถวได้ง่าย)
        // withTrashed: ของเดิมไม่ได้เช็คคนที่ถูกลบ ทำให้บันทึกทับจนเกิดเลขบัตรซ้ำเงียบ ๆ
        $idOwner = Customer::withTrashed()->with('prefix')
            ->where('IDNumber', $idNumber)
            ->where('id', '!=', $request->customer_id)
            ->orderBy('id')
            ->first();

        if ($idOwner) {
            return response()->json([
                'success' => false,
                'code'    => 'id_taken',
                'message' => 'เลขบัตรประชาชนนี้เป็นของลูกค้ารายอื่นในระบบแล้ว',
                'owner'   => $this->duplicateOwnerPayload($idOwner),
            ], 422);
        }

        $phoneOwner = Customer::withTrashed()->with('prefix')
            ->where('Mobilephone1', $mobile)
            ->where('id', '!=', $request->customer_id)
            ->orderBy('id')
            ->first();

        if ($phoneOwner) {
            return response()->json([
                'success' => false,
                'code'    => 'phone_taken',
                'message' => 'เบอร์โทรศัพท์นี้เป็นของลูกค้ารายอื่นในระบบแล้ว',
                'owner'   => $this->duplicateOwnerPayload($phoneOwner),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $customer = Customer::findOrFail($request->customer_id);
            $customer->update([
                'PrefixName'   => $request->PrefixName ?: null,
                'FirstName'    => $request->FirstName,
                'LastName'     => $request->LastName ?: null,
                'IDNumber'     => $idNumber,
                'Mobilephone1' => $mobile,
            ]);

            $addrData = [
                'house_number' => $request->house_number,
                'group'        => $request->group,
                'village'      => $request->village,
                'alley'        => $request->alley,
                'road'         => $request->road,
                'subdistrict'  => $request->subdistrict,
                'district'     => $request->district,
                'province'     => $request->province,
                'postal_code'  => $request->postal_code,
                'post_id'      => $request->post_id ?: null,
                'userZone'     => $customer->userZone ?? $authUser->userZone,
                'brand'        => $customer->brand ?? $authUser->brand,
                'branch'       => $customer->branch ?? $authUser->branch,
            ];

            // ไม่ใช้ updateOrCreate เพราะถ้ามีที่อยู่ซ้ำหลายแถวมันจะหยิบแถวไหนก็ได้ ต้องล็อกแถวล่าสุด
            $currentAddress = Address::where('customer_id', $customer->id)
                ->where('type', 'current')
                ->orderByDesc('id')
                ->first();

            if ($currentAddress) {
                $currentAddress->update($addrData);
            } else {
                Address::create(['customer_id' => $customer->id, 'type' => 'current'] + $addrData);
            }

            // ถ้ายังไม่มีที่อยู่เอกสาร ให้สร้างตามที่อยู่ปัจจุบัน (รายงาน เช่น ประกันภัย อ่านจาก document)
            // ไม่ทับของเดิมถ้ามีอยู่แล้ว เพราะที่อยู่เอกสารอาจตั้งใจให้ต่างจากปัจจุบัน
            $hasDocAddress = Address::where('customer_id', $customer->id)
                ->where('type', 'document')
                ->exists();

            if (!$hasDocAddress) {
                Address::create(['customer_id' => $customer->id, 'type' => 'document'] + $addrData);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'], 500);
        }

        $customer->refresh()->load('prefix');
        $fullName = trim(($customer->prefix->Name_TH ?? '') . ' ' . $customer->FirstName . ' ' . ($customer->LastName ?? ''));

        return response()->json([
            'success'   => true,
            'name'      => $fullName,
            'id_number' => $customer->formatted_id_number,
            'mobile'    => $customer->formatted_mobile,
        ]);
    }

    public function listHistory(Request $request)
    {
        $user = Auth::user();

        $query = Salecar::with(['customer.prefix', 'originalCustomer.prefix', 'carOrder', 'licensePlateRed', 'saleTeam'])
            ->where('con_status', '5');

        if (in_array($user->role, ['sale', 'lead_sale'])) {
            $visibleSaleIds = [$user->id];
            if ($user->role === 'lead_sale') {
                $visibleSaleIds = array_merge($visibleSaleIds, [9, 10, 11]);
            }
            $query->whereIn('SaleID', $visibleSaleIds);
        }

        $totalRecords = (clone $query)->count();

        $searchValue = $request->input('search.value');
        if ($searchValue) {
            $query->where(function ($q) use ($searchValue) {
                $q->whereHas('customer', function ($cq) use ($searchValue) {
                    $cq->searchFullName($searchValue);
                })->orWhereHas('originalCustomer', function ($cq) use ($searchValue) {
                    // ค้นชื่อ "ผู้จองเดิม" ด้วย (กรณีเปลี่ยนผู้ซื้อ) — ตารางแสดง 2 ชื่อ ต้องค้นเจอทั้งคู่
                    $cq->searchFullName($searchValue);
                })->orWhereHas('carOrder', function ($cq) use ($searchValue) {
                    $cq->where('order_code', 'like', "%{$searchValue}%");
                });
            });
        }

        $filteredRecords = (clone $query)->count();

        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $saleCar = $query->orderBy('DeliveryDate', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        $data = $saleCar->map(function ($s, $index) use ($start) {
            return [
                'No'       => $start + $index + 1,
                'FullName' => $this->customerNameWithOriginal($s),
                // 'code'   => $s->carOrder->order_code ?? '-',
                'vin_number' => $s->carOrder->vin_number ?? '-',
                // ทีมขาย (snapshot ตอนออกใบจอง) — โชว์เฉพาะ brand ที่ถูกขายโดยหลายทีม
                'team'       => $s->saleTeam?->name ?? '-',
                // ป้ายแดง — คันที่ยังไม่มีจะขึ้นป้ายจาง ๆ ให้เห็นว่าต้องตามใส่
                'red_plate' => $s->licensePlateRed
                    ? '<span class="badge bg-label-danger">' . e($s->licensePlateRed->number) . '</span>'
                    : '<span class="text-muted small">-</span>',
                'Action' => view('purchase-order.history.button', compact('s'))->render(),
            ];
        });

        return response()->json([
            'draw'            => (int) $request->input('draw', 1),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $data,
        ]);
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

    /**
     * ดึงคำสั่งซื้อที่ส่งมอบแล้วกลับมา / เปลี่ยนสถานะ — เฉพาะ role = admin
     * เปลี่ยนแค่ con_status เท่านั้น ไม่ยุ่งกับ CarOrder / tracking
     */
    public function changeStatus(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'con_status' => 'required|integer|exists:tb_constatus,id',
        ]);

        $saleCar = Salecar::findOrFail($id);
        $saleCar->update(['con_status' => $request->con_status]);

        return response()->json([
            'success' => true,
            'message' => 'เปลี่ยนสถานะเรียบร้อยแล้ว',
        ]);
    }

    public function exportBooking(Request $request)
    {
        return Excel::download(new BookingExport($request), ExportFilename::withBrand('booking.xlsx'));
    }

    //search puschase
    public function search(Request $request)
    {
        $keyword = $request->input('keyword');

        $saleCars = Salecar::with([
            'customer.prefix',
            'model',
            'subModel',
            'interiorColor'
        ])
            ->whereNull('CarOrderID')
            ->whereHas('customer', function ($q) use ($keyword) {
                $q->searchFullName($keyword);
            })
            ->limit(10)
            ->get();

        // แยกคีย์ชื่อสีภายในไว้ กัน relation interiorColor ทับ attribute interior_color (ที่เก็บเป็น id)
        $saleCars->each(function ($s) {
            $s->setAttribute('interior_color_name', $s->interiorColor->name ?? null);
        });

        return response()->json($saleCars);
    }

    //commission
    public function viewCommission()
    {
        if (!in_array(Auth::user()->role, ['admin', 'manager', 'gm', 'md', 'audit_dp', 'audit_lead'])) {
            abort(403);
        }

        return view('purchase-order.commission.view');
    }

    public function listCommission(Request $request)
    {
        // month มาเป็นรูปแบบ "YYYY-MM" จาก input type=month (default เดือนปัจจุบัน)
        [$year, $month] = $this->resolveCommissionMonth($request->input('month'));

        $user = Auth::user();

        $fromDate = Carbon::create($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $toDate   = Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');

        // ดึงรายคัน (พร้อม relation ที่ต้องใช้คิดค่าคอมสด) แล้วค่อยรวมต่อเซลล์ใน PHP
        // — ใช้ effectiveCommissionSale() เพื่อรองรับเคสเกิน over_budget ที่ใช้ยอดหักของ manager
        $rows = SaleCommissionQuery::base($user, false, $fromDate, $toDate)
            ->with(['model', 'saleUser.branchInfo'])
            ->when(in_array($user->role, ['sale', 'lead_sale']), function ($q) use ($user) {
                $visibleSaleIds = [$user->id];
                if ($user->role === 'lead_sale') {
                    $visibleSaleIds = array_merge($visibleSaleIds, [9, 10, 11]);
                }
                $q->whereIn('SaleID', $visibleSaleIds);
            })
            ->get();

        $saleCar = $rows->groupBy('SaleID')->map(function ($group, $saleId) {
            $first = $group->first();
            return (object) [
                'SaleID'           => $saleId,
                'saleUser'         => $first->saleUser,
                'total_cars'       => $group->count(),
                'total_commission' => (float) $group->sum(fn($r) => $r->effectiveCommissionSale()),
            ];
        })->values();

        // คอม SSI (brand 1, เฉพาะเดือน 3/10) — คิดจากยอดส่งมอบย้อนหลัง 6 เดือน
        $ssi = SsiCommissionQuery::forPeriod($year, $month);
        $ssiPerSale = $ssi['perSale'];

        // จำกัดสิทธิ์การมองเห็นให้ตรงกับ base query (sale/lead_sale)
        if (in_array($user->role, ['sale', 'lead_sale'])) {
            $visibleSaleIds = $user->role === 'lead_sale'
                ? array_merge([$user->id], [9, 10, 11])
                : [$user->id];
            $ssiPerSale = $ssiPerSale->only($visibleSaleIds);
        }

        $saleCar = $saleCar->keyBy('SaleID');

        // เฉพาะ viewer brand 1: เพิ่มเซลล์ที่ได้ SSI แต่ไม่มีรถส่งมอบในเดือนที่เลือก (SSI เป็นของ brand 1)
        if ((int) $user->brand === 1) {
            $missingIds = $ssiPerSale->filter(fn($v) => ($v['amount'] ?? 0) > 0)->keys()->diff($saleCar->keys());
            if ($missingIds->isNotEmpty()) {
                $extraUsers = User::with('branchInfo')->whereIn('id', $missingIds)->get()->keyBy('id');
                foreach ($missingIds as $sid) {
                    $saleCar->put($sid, (object) [
                        'SaleID'           => $sid,
                        'saleUser'         => $extraUsers->get($sid),
                        'total_cars'       => 0,
                        'total_commission' => 0.0,
                    ]);
                }
            }
        }

        // คอมตัวรถรายคัน (รายเดือน) → รวมเข้ายอดสุทธิ
        $carCommission = CarCommissionQuery::forMonth($year, $month)['perSale'];

        // ค่าปรับต่อเซลล์ต่อเดือน (วินัย / ขาด-ลา-สาย / lead / clip) → รวมเข้ายอดสุทธิ
        $adjustments = SaleCommissionMonthly::where('year', $year)
            ->where('month', $month)
            ->whereIn('SaleID', $saleCar->keys())
            ->get()
            ->keyBy('SaleID');

        // ยอดสุทธิ = ยอดที่ได้ "ทั้งเดือน" (base + คอมตัวรถ + SSI) — คอมกั๊กเป็นเรื่องเวลาจ่าย ไม่ลดยอดรวม
        // SSI เป็นของ brand 1 → คิดเฉพาะตอนดูหน้า brand 1 (brand 3 ใช้เซลล์ร่วมกับ brand 1 จึง gate ด้วย viewer brand)
        $viewerBrand = (int) $user->brand;
        $saleCar = $saleCar->map(function ($s) use ($adjustments, $ssiPerSale, $carCommission, $viewerBrand) {
            $adj = $adjustments->get($s->SaleID);
            $brand = (int) ($s->saleUser->brand ?? 0);
            $net = $adj
                ? $adj->computeNet($s->total_commission, $brand)
                : $s->total_commission;
            $carEntry = CarCommissionQuery::entry($carCommission, (int) $s->SaleID, $viewerBrand);
            $net += (float) ($carEntry['amount'] ?? 0);
            if ($viewerBrand === 1) {
                $net += (float) ($ssiPerSale[$s->SaleID]['amount'] ?? 0);
            }
            $s->net_commission = $net;
            return $s;
        })->values()->sortByDesc('net_commission')->values();

        $showEmoji = !in_array($user->role, ['sale', 'lead_sale']) && $saleCar->count() > 1;
        $lastIndex = $saleCar->count() - 1;

        $data = $saleCar->map(function ($s, $index) use ($showEmoji, $lastIndex) {
            $nameSale = $s->saleUser->name ?? '-';
            $branchSale = $s->saleUser->branchInfo->name ?? '-';

            $emoji = '';
            if ($showEmoji) {
                if ($index === 0) {
                    $emoji = ' 😊';
                } elseif ($index === $lastIndex) {
                    $emoji = ' 😢';
                }
            }

            $sale = "{$nameSale}{$emoji}<br>(สาขา : {$branchSale})";

            return [
                'No' => $index + 1,
                'name' => $sale,
                'total_car' => $s->total_cars . ' คัน',
                'com' => number_format($s->net_commission ?? 0, 2),
                'DT_RowData' => ['saleid' => $s->SaleID],
            ];
        });

        return response()->json(['data' => $data]);
    }

    /** แปลงค่า month ("YYYY-MM") เป็น [year, month]; ถ้าไม่ส่งมาใช้เดือนปัจจุบัน */
    private function resolveCommissionMonth($monthInput): array
    {
        if ($monthInput && preg_match('/^(\d{4})-(\d{2})$/', $monthInput, $m)) {
            return [(int) $m[1], (int) $m[2]];
        }

        return [(int) Carbon::now()->year, (int) Carbon::now()->month];
    }

    /**
     * รายชื่อลูกค้าทั้งหมดของเซลล์คนนั้นในเดือนที่เลือก + ฟอร์มกรอกค่าคอมเพิ่มเติมต่อเดือน
     * (ค่าคอมวินัย, ค่าขาด/ลา/มาสาย, คอม lead, คอม clip) — แสดงใน modal
     */
    public function commissionSaleDetail(Request $request, $saleId)
    {
        // audit_lead / audit_dp เปิดดูได้อย่างเดียว (ห้ามแก้ — endpoint บันทึกยังล็อกไว้ตามเดิม)
        $role = Auth::user()->role;
        abort_unless(in_array($role, ['admin', 'manager', 'gm', 'md', 'audit_lead', 'audit_dp']), 403);
        $canEditCommission = in_array($role, ['admin', 'manager', 'gm', 'md']);

        // ช่องเดือน = เดือน CK (เดือนที่ตัดยอด/ขาย)
        [$year, $month] = $this->resolveCommissionMonth($request->input('month'));

        $fromDate = Carbon::create($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $toDate   = Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');

        $rows = SaleCommissionQuery::base(Auth::user(), false, $fromDate, $toDate)
            ->with('model')
            ->without('saleUser') // modal ไม่ได้ใช้ saleUser ต่อคัน (โหลด $saleUser แยกด้านล่าง) → ตัด eager-load 2 query
            ->where('SaleID', $saleId)
            ->get();

        $saleUser = User::with('branchInfo')->find($saleId);
        // brand ที่กำลังดู (brand 3 ใช้เซลล์ร่วมกับ brand 1 → SSI/กั๊กต้องผูกกับหน้าที่ดู ไม่ใช่ brand ของตัวเซลล์)
        $viewerBrand = (int) Auth::user()->brand;

        // เรตคอมรายคันของเซลล์นี้ (ใช้คิดค่าคอมรายคัน C ต่อคัน + คอมกั๊ก) — ตาม brand ที่กำลังดู
        $car = CarCommissionQuery::forMonth($year, $month);
        $carEntry = CarCommissionQuery::entry($car['perSale'], (int) $saleId, $viewerBrand);
        $carMode  = $carEntry['mode'] ?? 'volume';
        $carRate  = (float) ($carEntry['rate'] ?? 0);

        $cars = $rows->map(function ($r) use ($viewerBrand, $carEntry, $carMode, $carRate) {
            $customerName = trim(
                ($r->customer->prefix->Name_TH ?? '') . ' ' .
                    ($r->customer->FirstName ?? '') . ' ' .
                    ($r->customer->LastName ?? '')
            );

            $sub = $r->carOrder->subModel->name ?? '-';
            $detailModel = $r->carOrder->subModel->detail ?? null;

            // คอมงบเหลือคิดสด (สูตรอัตโนมัติล้วน ๆ) + ยอดที่ผู้จัดการกรอกเคสเกินเพดาน แยกกันคนละช่อง
            $balanceCampaign = $r->autoBalanceCommission();
            $approvedCom     = $r->approvedCommission();
            // เกินงบ → ไม่คิดคอมประดับยนต์
            $accessoryCom = $r->effectiveAccessoryCommission();
            // คอมอื่นๆ — ใช้ค่า default ตามรุ่นถ้ายังไม่กรอก
            $specialCom   = $r->effectiveSpecialCommission();
            $interestCom  = $r->remainingPayment->total_com ?? 0;
            $turnCarCom   = $r->turnCar->com_turn ?? 0;

            // ค่าคอมรายคัน C ของคันนี้ (สำหรับคิดคอมกั๊ก brand 1) — เกินงบทะลุเพดานก่อนวันตัด = ไม่ได้คอมตัวรถ
            $C = 0.0;
            if ($carEntry && $r->earnsCarCommission()) {
                $C = $carMode === 'model'
                    ? CarCommissionQuery::modelRate((int) $r->brand, $r->model_id !== null ? (int) $r->model_id : null)
                    : $carRate;
            }

            // คอมกั๊ก (โมเดลใหม่): DD > รอบหลักของ CK หรือ DD ว่าง → กั๊ก H=min(2000,C) ; โชว์เฉพาะ brand 1
            $ck = $r->DeliveryInCKDate ? Carbon::parse($r->DeliveryInCKDate) : null;
            $dd = $r->DeliveryDate ? Carbon::parse($r->DeliveryDate) : null;
            $p = ($viewerBrand === 1 && $ck)
                ? HeldCommissionQuery::paymentFor($ck, $dd, $C)
                : ['held' => false, 'held_amount' => 0.0, 'main_amount' => $C, 'main_payday' => null, 'held_payday' => null];

            return [
                'id'              => $r->id,
                'customer'        => $customerName ?: '-',
                'model'           => $r->carOrder->model->Name_TH ?? '-',
                'subModel'        => $detailModel ? "{$detailModel} - {$sub}" : $sub,
                'ckDate'          => $r->DeliveryInCKDate,
                'ddDate'          => $r->DeliveryDate,
                'ddDay'           => $dd ? (int) $dd->day : null,
                'isHeld'          => $p['held'],
                'carCommission'   => $C,
                'heldAmount'      => $p['held_amount'],
                'heldPayday'      => $p['held_payday']?->format('Y-m-d'),
                'mainPayDate'     => $p['main_payday']?->format('Y-m-d'),
                'balanceCampaign' => $balanceCampaign,
                'approvedCom'     => $approvedCom,   // ยอดที่ผู้จัดการ/GM กรอก (เกินเพดาน) — brand2/4 ติดลบ
                'overCeiling'     => $r->isOverBudgetCeiling(), // เกินงบทะลุเพดาน (ใช้กับป้าย "รอยอดอนุมัติ")
                'noCarCom'        => !$r->earnsCarCommission(), // คันเก่าก่อนวันตัด → ไม่ได้คอมตัวรถ
                'extraDeduct'     => ExtraBudgetLedger::absorbedFor($r),
                'accessoryCom'    => $accessoryCom,
                'specialCom'      => $specialCom,
                'interestCom'     => $interestCom,
                'turnCarCom'      => $turnCarCom,
                'budgetDeduct'    => $r->effectiveBudgetDeduct(),   // budget หัก (brand 2)
                'commissionSale'  => $r->effectiveCommissionSale(), // รวมค่าคอมรถ (รวม budget หักแล้ว)
            ];
        });

        $baseCommission = (float) $rows->sum(fn($r) => $r->effectiveCommissionSale());

        $adjustment = SaleCommissionMonthly::firstOrNew([
            'SaleID' => $saleId,
            'year'   => $year,
            'month'  => $month,
        ]);

        $brand = (int) ($saleUser->brand ?? 0);

        // คอม SSI (brand 1, เฉพาะเดือน 3/10) — เฉลี่ยแยกสาขา + เกณฑ์ ≥18 คัน/≥1 ทุกเดือน
        $ssi = SsiCommissionQuery::forPeriod($year, $month);
        $ssiEntry  = $ssi['perSale'][$saleId] ?? null;
        $ssiActive = $ssi['active'] && $brand === 1 && $viewerBrand === 1;
        $ssiData = [
            'active'      => $ssiActive,
            'branch'      => $ssiEntry['branch'] ?? SsiCommissionQuery::branchOf((int) $saleId),
            'rate'        => $ssiEntry['rate'] ?? 0,
            'average'     => $ssiEntry['average'] ?? null,
            'count'       => $ssiEntry['count'] ?? 0,
            'eligible'    => $ssiEntry['eligible'] ?? false,
            'every_month' => $ssiEntry['every_month'] ?? false,
            'min_cars'    => SsiCommissionQuery::MIN_CARS,
            'amount'      => $ssiActive ? (float) ($ssiEntry['amount'] ?? 0) : 0.0,
        ];

        // คอมตัวรถรายคัน (รายเดือน) — ใช้ $carEntry ที่คิดไว้ด้านบน
        $carData = [
            'active'    => $car['active'] && $carEntry !== null,
            'mode'      => $carEntry['mode'] ?? 'volume',
            'count'     => $carEntry['count'] ?? 0,
            // จำนวนคันที่ได้คอมจริง (ตัดคันที่เกินงบทะลุเพดานออก)
            'paidCount' => $carEntry['paidCount'] ?? ($carEntry['count'] ?? 0),
            'rate'      => $carEntry['rate'] ?? 0,
            'achieved'  => $carEntry['achieved'] ?? false,
            'amount'    => (float) ($carEntry['amount'] ?? 0),
        ];

        // ── ยอดสุทธิ = คอมเต็มของ CK เดือนนี้ (กั๊กเป็นแค่ "เวลาจ่าย" ไม่กระทบยอดรวม) ──
        $carAmount = (float) $carData['amount'];
        $nonCarNet = $adjustment->computeNet($baseCommission, (int) $brand) + (float) $ssiData['amount'];
        $net = $nonCarNet + $carAmount;

        // ── แตกรอบจ่ายเงิน (brand 1) — รอบหลัก 10 ของเดือนถัดจาก CK ; กั๊ก 2000 ยกไป 10 เดือนถัดจากรับรถ ──
        $rounds = ['active' => false];
        if ($viewerBrand === 1) {
            $mainCK  = Carbon::create($year, $month, 1)->addMonthNoOverflow()->day(10);
            $carMain = 0.0;
            $gakItems = [];
            $pendingTotal = 0.0;
            foreach ($cars as $c) {
                if ($c['mainPayDate'] === null) {        // DD ว่าง → พักทั้งก้อน
                    $pendingTotal += (float) $c['carCommission'];
                    continue;
                }
                $carMain += (float) $c['carCommission'] - (float) $c['heldAmount'];
                if ($c['isHeld'] && $c['heldAmount'] > 0) {
                    $gakItems[] = [
                        'customer' => $c['customer'],
                        'amount'   => (float) $c['heldAmount'],
                        'date'     => Carbon::parse($c['heldPayday'])->format('d/m/Y'),
                    ];
                }
            }
            // ยกมา: กั๊กของรถ CK เดือนก่อน ที่มาถึงกำหนดจ่ายในรอบหลักเดือนนี้ (10 ของ M+1)
            $ymM = Carbon::create($year, $month, 1)->format('Y-m');
            $carriedIn = HeldCommissionQuery::paymentsInMonth((int) $mainCK->year, (int) $mainCK->month)
                ->where('SaleID', (int) $saleId)
                ->where('kind', 'held')
                ->filter(fn($p) => Carbon::parse($p['ck'])->format('Y-m') < $ymM)
                ->sum('amount');

            $rounds = [
                'active'     => true,
                'main_date'  => $mainCK->format('d/m/Y'),
                'main_own'   => $nonCarNet + $carMain,   // รอบหลักของเดือนนี้ (base + SSI + คอมรถส่วนหลัก)
                'carried_in' => (float) $carriedIn,        // + กั๊กยกมาจากเดือนก่อน
                'gak_items'  => $gakItems,                 // กั๊กของเดือนนี้ที่ยกไป (พร้อมวันจ่าย)
                'gak_total'  => (float) array_sum(array_column($gakItems, 'amount')),
                'pending'    => $pendingTotal,
            ];
        }

        $months = [1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'];

        // budget ยกมา (brand 2) — กระเป๋าตังค์จากรถส่งมอบเดือนก่อน × 1,000 ; หักผ่าน budget_deduct ต่อคัน
        $carried = $brand === 2 ? BudgetWallet::carried((int) $saleId, $year, $month) : 0.0;
        $budget = [
            'active'    => $brand === 2,
            'carried'   => $carried,
            'used'      => $brand === 2 ? BudgetWallet::used((int) $saleId, $year, $month) : 0.0,
            'remaining' => $brand === 2 ? BudgetWallet::remaining((int) $saleId, $year, $month) : 0.0,
        ];

        return view('purchase-order.commission.sale-detail', [
            'canEdit'        => $canEditCommission,
            'saleUser'       => $saleUser,
            'cars'           => $cars,
            'baseCommission' => $baseCommission,
            'adjustment'     => $adjustment,
            'brand'          => $brand,
            'ssi'            => $ssiData,
            'car'            => $carData,
            'rounds'         => $rounds,
            'net'            => $net,
            'budget'         => $budget,
            'monthLabel'     => ($months[$month] ?? $month) . ' ' . ($year + 543),
            'year'           => $year,
            'month'          => $month,
        ]);
    }

    /** ดึงเป้ายอดขายของเดือน (ตาม brand ผู้ใช้) + สถานะบรรลุเป้า */
    public function getMonthlyTarget(Request $request)
    {
        abort_unless(in_array(Auth::user()->role, ['admin', 'manager', 'gm', 'md']), 403);

        [$year, $month] = $this->resolveCommissionMonth($request->input('month'));
        $brand = (int) Auth::user()->brand;

        $target = MonthlySaleTarget::where('brand', $brand)
            ->where('year', $year)->where('month', $month)->value('target');

        $car  = CarCommissionQuery::forMonth($year, $month);
        $mult = (float) config('car_commission.target_multiplier', 1.2);

        return response()->json([
            'target'      => $target,
            'brand_count' => (int) ($car['brandCount'][$brand] ?? 0),
            'achieved'    => (bool) ($car['achievedByBrand'][$brand] ?? false),
            'threshold'   => $target ? (int) ceil($target * $mult) : null,
        ]);
    }

    /** บันทึกเป้ายอดขายของเดือน (ตาม brand ผู้ใช้) */
    public function saveMonthlyTarget(Request $request)
    {
        abort_unless(in_array(Auth::user()->role, ['admin', 'manager', 'gm', 'md']), 403);

        $data = $request->validate([
            'target' => 'required|integer|min:0',
        ]);

        [$year, $month] = $this->resolveCommissionMonth($request->input('month'));
        $brand = (int) Auth::user()->brand;

        MonthlySaleTarget::updateOrCreate(
            ['brand' => $brand, 'year' => $year, 'month' => $month],
            ['target' => $data['target']]
        );

        return response()->json(['status' => 'success']);
    }

    /** บันทึกค่าคอมเพิ่มเติมต่อเซลล์ต่อเดือน */
    public function saveCommissionMonthly(Request $request)
    {
        abort_unless(in_array(Auth::user()->role, ['admin', 'manager', 'gm', 'md']), 403);

        $data = $request->validate([
            'SaleID'            => 'required|integer',
            'year'              => 'required|integer',
            'month'             => 'required|integer|min:1|max:12',
            'com_discipline'    => 'nullable|numeric',
            'deduct_absence'    => 'nullable|numeric',
            'com_lead'          => 'nullable|numeric',
            'com_clip'          => 'nullable|numeric',
            'discipline_failed' => 'nullable|boolean',
        ]);

        SaleCommissionMonthly::updateOrCreate(
            [
                'SaleID' => $data['SaleID'],
                'year'   => $data['year'],
                'month'  => $data['month'],
            ],
            [
                'com_discipline'    => $data['com_discipline'] ?? 0,
                'deduct_absence'    => $data['deduct_absence'] ?? 0,
                'com_lead'          => $data['com_lead'] ?? 0,
                'com_clip'          => $data['com_clip'] ?? 0,
                'discipline_failed' => (bool) ($data['discipline_failed'] ?? false),
            ]
        );

        // "คอมอื่นๆ" (CommissionSpecial) ต่อคัน — แก้ได้จากตารางในหน้ารายละเอียด
        if (is_array($request->input('car_special'))) {
            foreach ($request->input('car_special') as $salecarId => $value) {
                Salecar::withoutGlobalScopes()
                    ->where('id', (int) $salecarId)
                    ->update(['CommissionSpecial' => is_numeric($value) ? (float) $value : 0]);
            }
        }

        // "budget หัก" ต่อคัน (brand 2) — งบเดือนก่อนที่เอามากลบคันติดลบ
        if (is_array($request->input('car_budget_deduct'))) {
            foreach ($request->input('car_budget_deduct') as $salecarId => $value) {
                Salecar::withoutGlobalScopes()
                    ->where('id', (int) $salecarId)
                    ->where('brand', 2)
                    ->update(['budget_deduct' => is_numeric($value) ? (float) $value : 0]);
            }
        }

        return response()->json(['status' => 'success']);
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

        return Excel::download(new SaleCommissionExport(Auth::user(), $fromDate, $toDate), ExportFilename::withBrand('sale-commission.xlsx'));
    }

    // report gp
    public function viewExportGP()
    {
        // รายงาน GP ปิดจาก role manager
        abort_if(Auth::user()->role === 'manager', 403);

        return view('purchase-order.report.gp.view');
    }

    /**
     * หน้า "ตั้งค่า GP" — กรอกราคาทุน / ค่าอุปกรณ์ตกแต่ง / คอมขาย รายคัน (ใช้ในรายงาน GP รายคัน)
     * เห็นได้เฉพาะ role admin, audit ดึงรายการตามเดือนจาก DeliveryInDMSDate (default เดือนปัจจุบัน)
     */
    public function gpSetting(Request $request)
    {
        abort_unless(in_array(Auth::user()->role, ['admin', 'audit', 'audit_lead', 'audit_dp', 'gm', 'account']), 403);

        $month = $request->input('month') ?: now()->format('Y-m');

        $rows = GPQuery::base($month)
            ->orderBy('DeliveryInDMSDate')
            ->get();

        return view('purchase-order.gp-setting.view', compact('rows', 'month'));
    }

    public function updateGpSetting(Request $request, $id)
    {
        $role = Auth::user()->role;
        // admin, audit และ account แก้ไขได้ (audit/account แก้ได้ทุกอย่าง ยกเว้นราคาทุน/ราคาขาย ซึ่ง readonly)
        abort_unless(in_array($role, ['admin', 'audit', 'audit_lead', 'audit_dp', 'gm', 'account']), 403);

        $validated = $request->validate([
            'gp_cost_price_override' => 'nullable|numeric|min:0',
            'gp_accessory_cost'      => 'nullable|numeric|min:0',
            'gp_commission_sale'     => 'nullable|numeric|min:0',
            'car_DNP'                => 'nullable|numeric|min:0',
            'car_MSRP'               => 'nullable|numeric|min:0',
            'RI'                     => 'nullable|numeric',
            'WS'                     => 'nullable|numeric',
        ]);

        $salecar = Salecar::findOrFail($id);
        $salecar->gp_cost_price_override = $validated['gp_cost_price_override'] ?? null;
        $salecar->gp_accessory_cost      = $validated['gp_accessory_cost'] ?? null;
        $salecar->gp_commission_sale     = $validated['gp_commission_sale'] ?? null;
        $salecar->save();

        // RI / WS / ราคาทุน(DNP) / ราคาขาย(MSRP) เก็บที่ car_order
        if ($salecar->carOrder) {
            $salecar->carOrder->RI = $validated['RI'] ?? null;
            $salecar->carOrder->WS = $validated['WS'] ?? null;
            // ราคาทุน(DNP)/ราคาขาย(MSRP) แก้ได้เฉพาะ admin (audit เป็น readonly)
            if ($role === 'admin') {
                $salecar->carOrder->car_DNP  = $validated['car_DNP'] ?? null;
                $salecar->carOrder->car_MSRP = $validated['car_MSRP'] ?? null;
            }
            $salecar->carOrder->save();
        }

        return response()->json(['success' => true, 'message' => 'บันทึกเรียบร้อยแล้ว']);
    }

    public function exportGP(Request $request)
    {
        // รายงาน GP ปิดจาก role manager
        abort_if(Auth::user()->role === 'manager', 403);

        $fromDate = $request->from_date ?? now()->startOfMonth()->format('Y-m');

        return Excel::download(new GPExport($fromDate), ExportFilename::withBrand('gp-report.xlsx'));
    }

    // report sale Estimated
    public function viewExportSaleCar()
    {
        return view('purchase-order.report.saleCar.estimated.view');
    }

    public function exportSaleCar(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->format('Y-m');

        return Excel::download(new EstimatedExport($fromDate), ExportFilename::withBrand('ข้อมูลประมาณการ.xlsx'));
    }

    // report ประมาณการเซลล์ — ข้อมูลเหมือนประมาณการ แต่กรองเดือนตาม DeliveryInCKDate
    // และนับประเภทการขาย Normal + Test Drive (ไม่นับ Dealer) ; ประเภทการซื้อรถนับทั้งหมด
    public function viewExportSaleEstimate()
    {
        return view('purchase-order.report.saleCar.saleEstimate.view');
    }

    public function exportSaleEstimate(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->format('Y-m');

        return Excel::download(new EstimatedExport($fromDate, 'sale'), ExportFilename::withBrand('ประมาณการเซลล์.xlsx'));
    }

    //report gwm
    public function viewExportGwmStock()
    {
        return view('purchase-order.report.gwm.view');
    }

    public function gwmStockExport(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->format('Y-m');

        return Excel::download(new GwmExport($fromDate), ExportFilename::withBrand('ข้อมูลรถ GWM.xlsx'));
    }

    // report sale Booking
    public function viewExportSaleBooking()
    {
        $conStatus = TbConStatus::all();
        return view('purchase-order.report.saleCar.booking.view', compact('conStatus'));
    }

    public function exportSaleBooking(Request $request)
    {
        $fromDate = $request->from_date ?: null;
        $toDate   = $request->to_date   ?: null;
        $status   = $request->con_status ?: null;

        return Excel::download(new SaleCarBookingExport($fromDate, $toDate, $status), ExportFilename::withBrand('ข้อมูลการจอง.xlsx'));
    }

    // report ข้อมูลประกันภัย (เฉพาะ admin) — ดึงตามเดือน DeliveryDate ทุก brand แยก sheet
    public function viewExportInsurance()
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        return view('purchase-order.report.insurance.view');
    }

    public function exportInsurance(Request $request)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $fromDate = $request->from_date ?: now()->startOfMonth()->format('Y-m');

        return Excel::download(new InsuranceExport($fromDate), ExportFilename::withBrand('ข้อมูลประกันภัย.xlsx'));
    }

    //lead online allocation report (จัดสรร Lead Online) — แยก sheet ตาม brand + sheet Master_Settings
    public function viewExportLeadOnline()
    {
        abort_unless(in_array(Auth::user()->role, ['admin', 'gm', 'md', 'audit', 'manager']), 403);

        return view('purchase-order.report.lead-online.view');
    }

    public function exportLeadOnline(Request $request)
    {
        $user = Auth::user();
        abort_unless(in_array($user->role, ['admin', 'gm', 'md', 'audit', 'manager']), 403);

        $fromDate = $request->from_date ?: now()->startOfMonth()->format('Y-m');

        // admin/gm/md เห็นทุก brand → ทุกสาขา (branchFilter = null)
        // audit/manager เห็นตาม brand ของตน: 1→[1,3], 2→[2], 3→[3], 4→[4] → เฉพาะสาขาของตนเอง
        if (in_array($user->role, ['admin', 'gm', 'md'])) {
            $brands       = [1, 2, 3, 4];
            $branchFilter = null;
        } else {
            $homeBrand    = (int) $user->getOriginal('brand');
            $scope        = [1 => [1, 3], 2 => [2], 3 => [3], 4 => [4]];
            $brands       = $scope[$homeBrand] ?? [$homeBrand];
            $branchFilter = (int) $user->branch;   // เห็นแค่สาขาตัวเอง
        }

        // แตกเป็น unit = (brand × สาขาที่มีเซลล์จริง)
        // "เซลล์ที่ขาย brand นี้ได้" อ่านจาก config/brand.php sale_pool + สิทธิ์ขายราย user
        // เดิม hardcode [3,4] ทำให้เซลล์ Mitsu (brand 1) ที่ขาย Wuling หลุดจากรายงานทั้งหมด
        // whereNull('deleted_at') ต้องใส่เอง เพราะ withoutGlobalScopes() ปิด SoftDeletingScope ด้วย
        // (ถ้าไม่ใส่ สาขาที่เซลล์ถูกลบหมดแล้วจะยังสร้าง sheet เปล่าขึ้นมา)
        $branchNames = TbBranch::pluck('name', 'id')->all();
        $units = [];
        foreach ($brands as $brand) {
            $saleBrands = array_map('intval', (array) config("brand.sale_pool.$brand", [$brand]));
            $extraIds   = User::extraSaleUserIdsForBrand($brand);

            $q = User::withoutGlobalScopes()
                ->whereNull('deleted_at')
                ->whereIn('role', ['sale', 'lead_sale'])
                ->where(fn($w) => $w->whereIn('brand', $saleBrands)->orWhereIn('id', $extraIds))
                ->whereNotNull('branch');
            if ($branchFilter !== null) {
                $q->where('branch', $branchFilter);
            }
            $branchIds = $q->distinct()->pluck('branch')->map(fn($b) => (int) $b)->sort()->values();
            foreach ($branchIds as $br) {
                $units[] = [
                    'brand'      => $brand,
                    'branch'     => $br,
                    'branchName' => $branchNames[$br] ?? ('สาขา ' . $br),
                ];
            }
        }

        abort_if(empty($units), 404, 'ไม่มีข้อมูลเซลล์สำหรับรายงานนี้');

        return Excel::download(new LeadOnlineAllocationExport($fromDate, $units), ExportFilename::withBrand('จัดสรร Lead Online.xlsx'));
    }

    // report เกินงบ (รายงานเกินงบ) — กรองตามเดือนที่ขอเกินงบ (approval_requested_at)
    //  admin/md/account/gm เห็นทุก brand แยก sheet ; manager/audit เห็น brand ตัวเอง (1 → 1,3)
    public function viewExportOverBudget()
    {
        abort_unless(in_array(Auth::user()->role, ['admin', 'md', 'account', 'gm', 'manager', 'audit', 'audit_lead', 'audit_dp']), 403);

        return view('purchase-order.report.over-budget.view');
    }

    public function exportOverBudget(Request $request)
    {
        $user = Auth::user();
        abort_unless(in_array($user->role, ['admin', 'md', 'account', 'gm', 'manager', 'audit', 'audit_lead', 'audit_dp']), 403);

        $fromDate = $request->from_date ?: now()->format('Y-m');

        // admin/md/account/gm เห็นทุก brand รวมกัน
        // manager/audit เห็นตาม brand ประจำตัว: 1→[1,3], 2→[2], 3→[3], 4→[4]
        if (in_array($user->role, ['admin', 'md', 'account', 'gm'])) {
            $brands = [1, 2, 3, 4];
        } else {
            $homeBrand = (int) $user->getOriginal('brand');
            $scope = [1 => [1, 3], 2 => [2], 3 => [3], 4 => [4]];
            $brands = $scope[$homeBrand] ?? [$homeBrand];
        }

        return Excel::download(new OverBudgetExport($fromDate, $brands), 'รายงานเกินงบ.xlsx');
    }

    //delivery report
    public function viewExportMonthlyDelivery()
    {
        return view('purchase-order.report.saleCar.monthlyDelivery.view');
    }

    public function exportMonthlyDelivery(Request $request)
    {
        $fromDate   = $request->from_date ?? now()->startOfMonth()->format('Y-m');
        $toDate     = $request->to_date ?? now()->startOfMonth()->format('Y-m');
        $dateType   = $request->date_type ?? 'dms';

        return Excel::download(new MonthlyDeliveryExport($fromDate, $toDate, $dateType), ExportFilename::withBrand('ส่งมอบประจำเดือน.xlsx'));
    }

    public function proxyAttachment(Request $request, $id, $filename = null)
    {
        $saleCar  = Salecar::findOrFail($id);
        $shareUrl = $request->input('url');

        $allowed = collect($saleCar->attachment_url ?? [])->contains(function ($item) use ($shareUrl) {
            return is_array($item) ? ($item['url'] ?? '') === $shareUrl : $item === $shareUrl;
        });

        if (!$allowed) {
            abort(403);
        }

        try {
            $oneDrive                  = new OneDriveService();
            ['url' => $downloadUrl, 'name' => $filename] = $oneDrive->getDownloadInfo($shareUrl);

            $guzzle   = new Client(['allow_redirects' => true]);
            $response = $guzzle->get($downloadUrl);

            $contentType = $response->getHeader('Content-Type')[0] ?? 'application/octet-stream';
            $body        = $response->getBody()->getContents();

            return response($body, 200, [
                'Content-Type'        => $contentType,
                'Content-Disposition' => "inline; filename=\"{$filename}\"",
                'Cache-Control'       => 'private, max-age=3600',
            ]);
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function deleteAttachment(Request $request, $id)
    {
        $saleCar = Salecar::findOrFail($id);

        $index = $request->input('index');
        $urls  = is_array($saleCar->attachment_url) ? $saleCar->attachment_url : [];

        if (!isset($urls[$index])) {
            return response()->json(['success' => false, 'message' => 'ไม่พบไฟล์'], 404);
        }

        array_splice($urls, $index, 1);
        $saleCar->update(['attachment_url' => $urls]);

        return response()->json(['success' => true]);
    }
}
