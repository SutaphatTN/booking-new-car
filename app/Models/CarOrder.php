<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class CarOrder extends Model
{
    use SoftDeletes;

    protected $table = 'car_order';

    protected $casts = [
        'order_date' => 'date:Y-m-d',
        'order_invoice_date'   => 'date:Y-m-d',
    ];

    protected $fillable = [
        'model_id',
        'subModel_id',
        'vinNo',
        'purchase_source',
        'order_code',
        'order_date',
        'color',
        'year',
        'purchase_type',
        'order_status',
        'order_invoice_date',
        'order_stock_date',
        'cancel_date',
        'car_DNP',
        'car_MSRP',
        'estimated_stock_date',
        'stock_id',
        'car_status',
        'userZone',
    ];

    protected $dates = ['deleted_at'];

    public function model()
    {
        return $this->belongsTo(TbCarmodel::class, 'model_id', 'id');
    }

    public function subModel()
    {
        return $this->belongsTo(TbSubcarmodel::class, 'subModel_id', 'id');
    }

    public function getFormatOrderDateAttribute()
	{
		return $this->order_date ? Carbon::parse($this->order_date)->format('d-m-Y') : null;
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
}
