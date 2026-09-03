<?php

namespace App\Models;

use App\Models\Traits\LogsActivity;
use App\Models\Traits\TracksUserActions;
use App\Models\Traits\UserAccessScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CarOrder extends Model
{
    use SoftDeletes;
    use UserAccessScope;
    use TracksUserActions;

    // ประวัติการแก้ไขรายคัน ลง activity_logs (subject_type = 'CarOrder')
    // UserUpdate เก็บได้แค่ "คนแก้ล่าสุด" ซึ่งทับของเก่าทุกครั้ง — สืบย้อนว่าใครแก้ฟิลด์ไหนไม่ได้
    //
    // ⚠️ trait ดักได้เฉพาะการแก้ผ่าน model instance — ห้ามใช้ CarOrder::where(...)->update([...])
    //    กับตารางนี้ เพราะ query builder update ไม่ยิง model event ทำให้ประวัติหาย
    //    ถ้าต้องอัปเดตหลายแถวให้ใช้ CarOrder::updateLogged(fn($q) => ..., [...]) แทน
    use LogsActivity;

    protected $table = 'car_order';

    protected $casts = [
        'order_date' => 'date:Y-m-d',
        'order_invoice_date'   => 'date:Y-m-d',
        'gp_support_date'      => 'date:Y-m-d',
    ];

    protected $fillable = [
        'model_id',
        'subModel_id',
        'salecar_id',
        'vin_number',
        'j_number',
        'type',
        'system_date',
        'engine_number',
        'option',
        'purchase_source',
        // ดีลเลอร์ต้นทาง (เฉพาะ purchase_source = OTHDealer) — จังหวัดเดียวกันมีได้หลายดีลเลอร์ จึงเก็บชื่อคู่กัน
        'dealer_province_id',
        'dealer_name',
        'order_code',
        'order_date',
        'color',
        'type_color',
        'gwm_color',
        'interior_color',
        'year',
        'purchase_type',
        'payment_type',
        'order_status',
        'order_invoice_date',
        'fp_date',
        'fp_close_date',
        // ยอดที่ใช้คิดดอกเบี้ย FP — ตั้งต้นจาก car_DNP แต่แก้ได้ (บางคันมีค่าประดับยนต์ทำให้ยอดไม่ตรง)
        'fp_net_amount',
        // แจ้งจำหน่าย — ผูกกับ "คัน" ไม่ใช่ใบจอง (ใบจองสลับรถได้ เอกสารต้องอยู่กับรถ)
        'dispose_set',
        'dispose_received_date',
        'dispose_reg_withdraw_date',
        'dispose_note',
        'order_stock_date',
        'cancel_date',
        'car_DNP',
        'car_MSRP',
        'RI',
        'WS',
        // เงินสนับสนุน (เฉพาะ brand 4) — กรอกจากหน้า "ตั้งค่า GP" เก็บที่คันรถเหมือน RI/WS
        'gp_support_amount',
        'gp_support_date',
        'estimated_stock_date',
        'stock_id',
        'car_status',
        'approver',
        'approved_by',
        'approver_date',
        'approval_requested_at', // ส่งเมลขออนุมัติไปแล้วเมื่อไหร่ — มีค่า = ขอซ้ำไม่ได้
        'note_accessory',
        'note',
        'cam_testdrive',
        'mileage_test',
        'license_plate_id', // ป้ายแดงของรถทดลองขับ (เฉพาะ purchase_type = TestDrive)
        'status',
        'reason',
        'userZone',
        'brand',
        'UserInsert',
        'UserUpdate',
        'UserDelete',
        'branch',
        'waiting_id',
    ];

    protected $dates = ['deleted_at'];

    /** แหล่งที่มา = ซื้อจากดีลเลอร์อื่น — เคสเดียวที่ต้องระบุจังหวัดของดีลเลอร์ */
    const SOURCE_DEALER = 'OTHDealer';

    /** ประเภทการซื้อรถ = TestDrive / Retail (tb_purchase_type.id) */
    const PURCHASE_TYPE_TEST_DRIVE = 1;
    const PURCHASE_TYPE_RETAIL = 2;

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_FINISHED = 'finished';

    public function model()
    {
        return $this->belongsTo(TbCarmodel::class, 'model_id', 'id');
    }

    public function subModel()
    {
        return $this->belongsTo(TbSubcarmodel::class, 'subModel_id', 'id');
    }

    public function saleCus()
    {
        return $this->belongsTo(Salecar::class, 'salecar_id', 'id');
    }

    public function approvers()
    {
        return $this->belongsTo(User::class, 'approver', 'id');
    }

    public function userInsert()
    {
        return $this->belongsTo(User::class, 'UserInsert', 'id');
    }

    public function userUpdate()
    {
        return $this->belongsTo(User::class, 'UserUpdate', 'id');
    }

    public function userDelete()
    {
        return $this->belongsTo(User::class, 'UserDelete', 'id');
    }

    public function salecars()
    {
        return $this->hasMany(Salecar::class, 'CarOrderID', 'id');
    }

    public function historyCar()
    {
        return $this->hasMany(CarOrderHistory::class, 'CarOrderID', 'id');
    }

    public function orderStatus()
    {
        return $this->belongsTo(TbOrderStatus::class, 'order_status', 'id');
    }

    public function purchaseType()
    {
        return $this->belongsTo(TbPurchaseType::class, 'purchase_type', 'id');
    }

    public function gwmColor()
    {
        return $this->belongsTo(TbColor::class, 'gwm_color');
    }

    // จังหวัดของดีลเลอร์ที่ซื้อรถมา (เฉพาะ purchase_source = OTHDealer)
    public function dealerProvince()
    {
        return $this->belongsTo(TbProvinces::class, 'dealer_province_id', 'id');
    }

    // ป้ายแดงของรถทดลองขับ — ข้าม brand scope ของป้าย (ใช้แสดงผลเท่านั้น)
    public function licensePlate()
    {
        return $this->belongsTo(TbLicensePlate::class, 'license_plate_id', 'id')
            ->withoutGlobalScope('brandAccess');
    }

    // รายละเอียดดีลเลอร์ต้นทาง "จังหวัด · ชื่อร้าน" — null เมื่อไม่ใช่เคส OTHDealer หรือยังไม่ได้กรอก
    public function getDealerDetailAttribute(): ?string
    {
        if ($this->purchase_source !== self::SOURCE_DEALER) {
            return null;
        }

        $parts = array_filter([$this->dealerProvince?->name, $this->dealer_name]);

        return $parts ? implode(' · ', $parts) : null;
    }

    // แหล่งที่มาแบบเต็ม — เคส OTHDealer พ่วงจังหวัด/ชื่อดีลเลอร์ต่อท้าย (ใช้ในรายงาน)
    public function getPurchaseSourceLabelAttribute()
    {
        if (!$this->purchase_source) {
            return '-';
        }

        return $this->dealer_detail
            ? $this->purchase_source . ' (' . $this->dealer_detail . ')'
            : $this->purchase_source;
    }

    public function getDisplayColorAttribute()
    {
        if (in_array($this->brand, [2, 3, 4])) {
            return $this->gwmColor?->name ?? '-';
        }

        return $this->color ?? '-';
    }

    public function interiorColor()
    {
        return $this->belongsTo(TbInteriorColor::class, 'interior_color', 'id');
    }

    public function branchInfo()
    {
        return $this->belongsTo(TbBranch::class, 'branch', 'id');
    }

    /**
     * เห็นรถ "ข้ามสาขา" ทั้ง brand — ตัดตัวกรอง สาขา/zone ของ UserAccessScope ออก
     * เหลือกรองแค่ brand (= สิทธิ์เท่ากับ role admin)
     *
     * ใช้เฉพาะหน้าที่จำกัด role แน่นอนอยู่แล้วและงานเป็นภาพรวมทั้ง brand
     * ตอนนี้คือหน้า "แจ้งจำหน่าย" (Floor Plan — admin / audit_internal / md เท่านั้น)
     * เพราะเอกสารแจ้งจำหน่ายคนทำมีไม่กี่คน ไล่ดูทีเดียวทั้ง brand สะดวกกว่าสลับสาขา
     *
     * ⚠️ ห้ามเอาไปใช้กับหน้าที่ role ทั่วไปเข้าถึงได้ — จะทะลุการแบ่งสาขาทั้งระบบ
     */
    public function scopeAcrossBranches($query)
    {
        $query->withoutGlobalScope('userAccess');

        $brand = Auth::user()->brand ?? null;
        if ($brand) {
            $query->where($this->getTable() . '.brand', $brand);
        }

        return $query;
    }

    public function getFormatOrderDateAttribute()
    {
        return $this->order_date ? Carbon::parse($this->order_date)->format('d-m-Y') : null;
    }

    public function getFormatSystemDateAttribute()
    {
        return $this->system_date ? Carbon::parse($this->system_date)->format('d-m-Y') : null;
    }

    public function getFormatOrderInvoiceDateAttribute()
    {
        return $this->order_invoice_date ? Carbon::parse($this->order_invoice_date)->format('d-m-Y') : null;
    }

    public function getFormatOrderStockDateAttribute()
    {
        return $this->order_stock_date ? Carbon::parse($this->order_stock_date)->format('d-m-Y') : null;
    }

    public function getFormatCancelDateAttribute()
    {
        return $this->cancel_date ? Carbon::parse($this->cancel_date)->format('d-m-Y') : '-';
    }

    public function getFormatEstimatedStockDateAttribute()
    {
        return $this->estimated_stock_date ? Carbon::parse($this->estimated_stock_date)->format('d-m-Y') : null;
    }

    public function getOrderInvoiceDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getCancelDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getOrderStockDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getEstimatedStockDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getFormatApproverDateAttribute()
    {
        return $this->approver_date ? Carbon::parse($this->approver_date)->format('d-m-Y') : null;
    }

    public function getOrderDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getApproverDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getFpDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getFormatFpDateAttribute()
    {
        return $this->fp_date ? Carbon::parse($this->fp_date)->format('d-m-Y') : null;
    }

    public function getFpCloseDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getFormatFpCloseDateAttribute()
    {
        return $this->fp_close_date ? Carbon::parse($this->fp_close_date)->format('d-m-Y') : null;
    }

    public function getDisposeReceivedDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getFormatDisposeReceivedDateAttribute()
    {
        return $this->dispose_received_date ? Carbon::parse($this->dispose_received_date)->format('d-m-Y') : null;
    }

    public function getDisposeRegWithdrawDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getFormatDisposeRegWithdrawDateAttribute()
    {
        return $this->dispose_reg_withdraw_date ? Carbon::parse($this->dispose_reg_withdraw_date)->format('d-m-Y') : null;
    }

    const PAYMENT_TYPES = [
        'cash'     => 'เงินสด',
        'fp_tisco' => 'FP Tisco',
    ];

    public function getPaymentTypeLabelAttribute()
    {
        return self::PAYMENT_TYPES[$this->payment_type] ?? '-';
    }
}
