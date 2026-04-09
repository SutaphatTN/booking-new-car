<?php

namespace App\Models;

use App\Models\Traits\UserAccessScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceAccessory extends Model
{
    use SoftDeletes;
	use UserAccessScope;

	protected $table = 'invoice_accessory';

    protected $fillable = [
		'inv_cust_id',
        'acc_partner',
        'detail',
        'cost_price',
        'sale_price',
        'brand',
        'userZone'
        ];

	protected $dates = ['deleted_at'];

	public function partner()
	{
		return $this->belongsTo(AccessoryPartner::class, 'acc_partner', 'id');
	}
}
