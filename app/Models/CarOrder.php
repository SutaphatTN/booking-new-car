<?php

namespace App\Models;

use App\Models\Traits\UserAccessScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class CarOrder extends Model
{
    use SoftDeletes;
    use UserAccessScope;

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
        'gwm_color',
        'interior_color',
        'year',
        'purchase_type',
        'order_status',
        'order_invoice_date',
        'order_stock_date',
        'cancel_date',
        'car_DNP',
        'car_MSRP',
        'RI',
        'estimated_stock_date',
        'stock_id',
        'car_status',
        'approver',
        'approved_by',
        'approver_date',
        'note_accessory',
        'note',
        'cam_testdrive',
        'mileage_test',
        'status',
        'reason',
        'userZone',
        'brand',
        'UserInsert'
    ];

    protected $dates = ['deleted_at'];

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

    public function getDisplayColorAttribute()
    {
        if ($this->brand == 2) {
            return $this->gwmColor?->name ?? '-';
        }

        return $this->color ?? '-';
    }

    public function interiorColor()
    {
        return $this->belongsTo(TbInteriorColor::class, 'interior_color', 'id');
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
}
