<?php

namespace App\Models;

use App\Models\Traits\UserAccessScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\TbPurchaseType;
use App\Models\TbColor;
use App\Models\TbInteriorColor;

class CarOrderWaiting extends Model
{
    use SoftDeletes;
    use UserAccessScope;

    protected $table = 'car_order_waiting';

    protected $fillable = [
        'model_id',
        'subModel_id',
        'type',
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
        'car_DNP',
        'car_MSRP',
        'RI',
        'WS',
        'count_order',
        'received_order',
        'approver',
        'approved_by',
        'approved_at',
        'note',
        'reason',
        'system_date',
        'status',
        'userZone',
        'brand',
        'UserInsert',
        'branch',
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

    public function approvers()
    {
        return $this->belongsTo(User::class, 'approver', 'id');
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
        if ($this->brand == 2 || $this->brand == 3) {
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

    public function getFormatApprovedAtAttribute()
    {
        return $this->approved_at ? Carbon::parse($this->approved_at)->format('d-m-Y') : null;
    }
}
