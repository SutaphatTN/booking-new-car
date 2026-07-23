<?php

namespace App\Models;

use App\Models\Traits\TracksUserActions;
use App\Models\Traits\UserAccessScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class CarOrder extends Model
{
    use SoftDeletes;
    use UserAccessScope;
    use TracksUserActions;

    protected $table = 'car_order';

    protected $casts = [
        'order_date' => 'date:Y-m-d',
        'order_invoice_date'   => 'date:Y-m-d',
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

    // ป้ายแดงของรถทดลองขับ — ข้าม brand scope ของป้าย (ใช้แสดงผลเท่านั้น)
    public function licensePlate()
    {
        return $this->belongsTo(TbLicensePlate::class, 'license_plate_id', 'id')
            ->withoutGlobalScope('brandAccess');
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
