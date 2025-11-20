<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentType extends Model
{
    use SoftDeletes;

    protected $table = 'payment_type';

    protected $fillable = [
        'saleCar_id',
        'category',
        'type',
        'cost',
        'date',
        'credit',
        'tax_credit',
        'transfer_bank',
        'transfer_branch',
        'transfer_no',
        'check_bank',
        'check_branch',
        'check_no',
        'finance',
        'interest',
        'period',
        'alp',
        'including_alp',
        'total_alp',
        'type_com',
        'total_com',
        'userZone',
    ];

    protected $dates = ['deleted_at'];

    public function salecar()
    {
        return $this->belongsTo(Salecar::class, 'saleCar_id', 'id');
    }

    public function financeInfo()
    {
        return $this->belongsTo(Finance::class, 'finance', 'id');
    }
}
