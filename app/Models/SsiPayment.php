<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SsiPayment extends Model
{
    protected $table = 'ssi_payments';

    protected $casts = [
        'amount_admin'    => 'float',
        'amount_customer' => 'float',
        'transfer_correct' => 'boolean',
    ];

    protected $fillable = [
        'ssi_record_id',
        'amount_admin',
        'amount_customer',
        'payment_channel',
        'transfer_correct',
        'remark',
    ];

    public function ssiRecord()
    {
        return $this->belongsTo(SsiRecord::class, 'ssi_record_id');
    }
}
